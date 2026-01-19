<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TopUp;
use App\Models\Transaction;
use App\Models\User;
use App\Models\BalanceMutation;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class CorrectionController extends Controller
{
    private function checkPermission($user)
    {
        if (!in_array($user->role, ['developer', 'pusat', 'keuangan'])) {
            abort(403, 'Unauthorized. Anda tidak punya akses hapus data.');
        }
    }

    /**
     * BATALKAN TOPUP
     * Logic: Cek Saldo User -> Tarik Saldo -> Hapus History
     */
    public function rollbackTopUp(Request $request)
    {
        $this->checkPermission($request->user());

        $request->validate(['topup_id' => 'required|exists:top_ups,id']);

        $topup = TopUp::find($request->topup_id);
        $user = User::find($topup->user_id);

        if ($topup->status !== 'approved') {
            return response()->json(['message' => 'Hanya TopUp sukses yang bisa dibatalkan.'], 400);
        }

        // 1. CEK KECUKUPAN SALDO USER
        if ($user->saldo < $topup->amount) {
            return response()->json([
                'message' => 'Gagal! Saldo user tidak cukup (Uang sudah terpakai).',
                'sisa_saldo' => $user->saldo,
                'required' => $topup->amount
            ], 400);
        }

        DB::beginTransaction();
        try {
            // A. Tarik Saldo User (Debit)
            $user->saldo -= $topup->amount;
            $user->save();

            // B. Catat Mutasi
            BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $topup->amount,
                'current_balance' => $user->saldo,
                'category' => 'correction',
                'description' => 'KOREKSI ADMIN: Pembatalan TopUp #' . $topup->id,
                'related_user_id' => $request->user()->id
            ]);

            // C. Notifikasi
            NotificationService::send(
                $user->id,
                'Top Up Dibatalkan',
                "Top Up Rp " . number_format($topup->amount, 0, ',', '.') . " dibatalkan Admin. Saldo disesuaikan.",
                'system'
            );

            // D. Hapus Data
            $topup->delete();

            DB::commit();
            return response()->json(['message' => 'TopUp berhasil dibatalkan.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * BATALKAN TRANSAKSI (REFUND & ROLLBACK)
     * Logic: Cek Tipe (P2P/Merchant) -> Cek Saldo Penerima -> Refund Pengirim -> Tarik Penerima -> Hapus History
     */
    public function rollbackTransaction(Request $request)
    {
        $this->checkPermission($request->user());

        $request->validate(['transaction_code' => 'required|exists:transactions,transaction_code']);

        $trx = Transaction::where('transaction_code', $request->transaction_code)->first();

        if ($trx->status !== 'paid') {
            // Jika belum lunas, hapus saja tanpa mutasi saldo
            $trx->delete();
            return response()->json(['message' => 'Riwayat transaksi belum lunas berhasil dihapus.']);
        }

        // 1. IDENTIFIKASI PIHAK TERKAIT
        $sender = User::find($trx->user_id);          // Pengirim / Pembeli
        $receiver = User::find($trx->target_user_id);   // Penerima / Penjual

        if (!$sender || !$receiver) {
            return response()->json(['message' => 'Data Pengirim atau Penerima tidak ditemukan.'], 404);
        }

        // 2. CEK KECUKUPAN SALDO PIHAK PENERIMA
        $isP2P = ($trx->type === 'transfer');

        if ($isP2P) {
            // Kasus P2P: Cek Saldo Utama Penerima
            if ($receiver->saldo < $trx->total_bayar) {
                return response()->json([
                    'message' => 'Gagal! Saldo Penerima tidak cukup untuk ditarik kembali.'
                ], 400);
            }
        } else {
            // Kasus Merchant: Cek Merchant Balance Toko
            if ($receiver->merchant_balance < $trx->total_bayar) {
                return response()->json([
                    'message' => 'Gagal! Merchant Balance Toko tidak cukup (Dana sudah ditarik).'
                ], 400);
            }
        }

        // 3. EKSEKUSI
        DB::beginTransaction();
        try {
            // A. KEMBALIKAN UANG KE PENGIRIM (REFUND)
            $sender->saldo += $trx->total_bayar;
            $sender->save();

            BalanceMutation::create([
                'user_id' => $sender->id,
                'type' => 'credit',
                'amount' => $trx->total_bayar,
                'current_balance' => $sender->saldo,
                'category' => 'correction',
                'description' => 'REFUND: Batal ' . ($isP2P ? 'Transfer' : 'Transaksi') . ' ' . $trx->transaction_code,
                'related_user_id' => $request->user()->id
            ]);

            // B. TARIK UANG DARI PENERIMA (ROLLBACK)
            $currentBalanceReceiver = 0;
            $descReceiver = '';

            if ($isP2P) {
                // Tarik dari Saldo Utama
                $receiver->saldo -= $trx->total_bayar;
                $receiver->save();
                $currentBalanceReceiver = $receiver->saldo;
                $descReceiver = 'KOREKSI: Batal Terima Transfer ' . $trx->transaction_code;
            } else {
                // Tarik dari Merchant Balance
                $receiver->merchant_balance -= $trx->total_bayar;
                $receiver->save();
                $currentBalanceReceiver = $receiver->merchant_balance;
                $descReceiver = 'KOREKSI: Batal Penjualan ' . $trx->transaction_code;
            }

            BalanceMutation::create([
                'user_id' => $receiver->id,
                'type' => 'debit',
                'amount' => $trx->total_bayar,
                'current_balance' => $currentBalanceReceiver,
                'category' => 'correction',
                'description' => $descReceiver,
                'related_user_id' => $request->user()->id
            ]);

            // C. NOTIFIKASI KE DUA PIHAK
            $judul = $isP2P ? 'Transfer Dibatalkan' : 'Transaksi Dibatalkan';

            // Ke Pengirim
            NotificationService::send(
                $sender->id,
                'Dana Dikembalikan',
                "$judul. Saldo Rp " . number_format($trx->total_bayar) . " telah dikembalikan.",
                'system'
            );

            // Ke Penerima
            NotificationService::send(
                $receiver->id,
                'Saldo Disesuaikan',
                "$judul. Dana senilai Rp " . number_format($trx->total_bayar) . " ditarik kembali.",
                'system'
            );

            // D. Hapus Transaksi
            $trx->delete();

            DB::commit();

            return response()->json([
                'message' => 'Transaksi dibatalkan. Saldo Pengirim dikembalikan & Saldo Penerima ditarik.',
                'type' => $isP2P ? 'P2P Transfer' : 'Merchant Purchase'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}