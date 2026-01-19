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
    // Cek Permission: Hanya Developer/Pusat/Keuangan yang boleh hapus
    private function checkPermission($user)
    {
        if (!in_array($user->role, ['developer', 'pusat', 'keuangan'])) {
            abort(403, 'Unauthorized. Anda tidak punya akses hapus data.');
        }
    }

    /**
     * BATALKAN TOPUP (Uang ditarik kembali dari User)
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

        DB::beginTransaction();
        try {
            // 1. Tarik Saldo User (Debit)
            $user->saldo -= $topup->amount;
            $user->save();

            // 2. Catat Mutasi "KOREKSI" (Biar user gak bingung kenapa saldo hilang)
            BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'debit', // Uang Keluar
                'amount' => $topup->amount,
                'current_balance' => $user->saldo,
                'category' => 'correction',
                'description' => 'KOREKSI ADMIN: Pembatalan TopUp #' . $topup->id,
                'related_user_id' => $request->user()->id
            ]);

            // 3. Notifikasi ke User
            NotificationService::send(
                $user->id,
                'Top Up Dibatalkan',
                "Top Up senilai Rp " . number_format($topup->amount) . " telah dibatalkan oleh Admin. Saldo Anda disesuaikan.",
                'system'
            );

            // 4. Hapus Data Topup (Sesuai request kamu)
            $topup->delete();

            DB::commit();

            return response()->json(['message' => 'TopUp berhasil dibatalkan & saldo ditarik kembali.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * BATALKAN TRANSAKSI (Uang dikembalikan ke User)
     */
    public function rollbackTransaction(Request $request)
    {
        $this->checkPermission($request->user());

        $request->validate(['transaction_code' => 'required|exists:transactions,transaction_code']);

        $trx = Transaction::where('transaction_code', $request->transaction_code)->first();
        $user = User::find($trx->user_id);

        if ($trx->status !== 'paid') {
            return response()->json(['message' => 'Hanya transaksi lunas yang bisa dibatalkan.'], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Kembalikan Saldo User (Credit)
            $user->saldo += $trx->total_bayar;
            $user->save();

            // 2. Catat Mutasi "KOREKSI" (Refund)
            BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'credit', // Uang Masuk
                'amount' => $trx->total_bayar,
                'current_balance' => $user->saldo,
                'category' => 'correction',
                'description' => 'REFUND: Pembatalan Transaksi ' . $trx->transaction_code,
                'related_user_id' => $request->user()->id
            ]);

            // 3. Notifikasi
            NotificationService::send(
                $user->id,
                'Pengembalian Dana',
                "Transaksi " . $trx->transaction_code . " dibatalkan. Saldo Rp " . number_format($trx->total_bayar) . " dikembalikan.",
                'system'
            );

            // 4. Hapus Transaksi
            // Hapus juga detail item transaksi jika ada tabel transaction_items
            // $trx->items()->delete();
            $trx->delete();

            DB::commit();

            return response()->json(['message' => 'Transaksi dibatalkan & dana dikembalikan.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}