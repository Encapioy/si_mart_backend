<?php

namespace App\Http\Controllers\Api;

use App\Services\NotificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\TopUp;
use App\Models\Admin;
use App\Models\Transaction;
use App\Models\BalanceMutation;

class BalanceController extends Controller
{
    // 1. USER REQUEST TOP UP (Upload Bukti)
    public function requestTopUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:10000',
            'bukti_transfer' => 'required|image|max:2048', // Wajib Foto
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // Upload Gambar
        $path = $request->file('bukti_transfer')->store('topups', 'public');

        $topup = TopUp::create([
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'bukti_transfer' => $path,
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Permintaan Top Up dikirim. Tunggu konfirmasi Admin.', 'data' => $topup]);
    }

    // 2. USER REQUEST WITHDRAW (Update Logic: Tunai vs Transfer)
    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:10000',
            'pin' => 'required|string',
            'metode' => 'required|in:tunai,transfer', // Pilihan Metode
            // Jika transfer, wajib isi data bank
            'bank_name' => 'required_if:metode,transfer',
            'account_number' => 'required_if:metode,transfer',
            'account_name' => 'required_if:metode,transfer',
            'source_type' => 'required|in:main,merchant',
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $user = $request->user();

        // Cek pin
        if ($user->pin !== $request->pin)
            return response()->json(['message' => 'PIN Salah'], 401);


        // Tentukan Saldo Mana yang Dipakai
        $currentBalance = 0;

        if ($request->source_type === 'merchant') {
            $currentBalance = $user->merchant_balance;
            $balanceField = 'merchant_balance'; // Nama kolom di DB
        } else {
            $currentBalance = $user->saldo;
            $balanceField = 'saldo'; // Nama kolom di DB
        }

        // Cek Kecukupan Saldo
        if ($currentBalance < $request->amount) {
            return response()->json([
                'message' => 'Saldo ' . ($request->source_type == 'merchant' ? 'Penghasilan' : 'Utama') . ' tidak mencukupi'
            ], 400);
        }

        // Kurangi Saldo
        // Kita pakai increment/decrement biar aman
        if ($request->source_type === 'merchant') {
            $user->decrement('merchant_balance', $request->amount);
        } else {
            $user->decrement('saldo', $request->amount);
        }

        $this->recordMutation($user, $request->amount, 'debit', 'withdraw', 'Request Penarikan Dana');

        // Simpan ke Withdrawals
        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'status' => 'pending',
            'bank_name' => $request->metode == 'transfer' ? $request->bank_name : null,
            'account_number' => $request->metode == 'transfer' ? $request->account_number : null,
            'account_name' => $request->metode == 'transfer' ? $request->account_name : null,
        ]);


        return response()->json(['message' => 'Pengajuan penarikan berhasil.', 'sisa_saldo' => $user->saldo]);
    }

    // 3. TRANSFER SALDO KE TEMAN (Scan QR Teman)
    public function transfer(Request $request)
    {
        $request->validate([
            'target_identity_code' => 'required|string',
            'amount' => 'required|integer|min:1000',
            'pin' => 'required|string',
        ]);

        $sender = $request->user();

        // 1. Cek PIN
        if (trim((string) $sender->pin) !== trim((string) $request->pin)) {
            return response()->json(['message' => 'PIN Salah'], 401);
        }

        // 2. Cek Saldo
        if ($sender->saldo < $request->amount) {
            if ($sender->saldo < 0) {
                return response()->json([
                    'message' => 'Akun Anda memiliki tunggakan (Saldo Minus). Silakan Top Up untuk melunasi.'
                ], 400);
            }
            return response()->json(['message' => 'Saldo tidak cukup!'], 400);
        }

        // 3. Cari Penerima
        $kode = $request->target_identity_code;
        $receiver = User::where('member_id', $kode)
            ->orWhere('username', $kode)
            ->orWhere('nfc_id', $kode)
            ->orWhere('no_hp', $kode)
            ->first();

        if (!$receiver)
            return response()->json(['message' => 'Penerima tidak ditemukan'], 404);
        if ($receiver->id == $sender->id)
            return response()->json(['message' => 'Tidak bisa transfer ke diri sendiri'], 400);

        // 4. EKSEKUSI TRANSFER (Hapus 'return' di sini, tampung ke variabel $trx)
        $trx = DB::transaction(function () use ($sender, $receiver, $request) {

            // A. PENGIRIM (Debit)
            $sender->saldo -= $request->amount;
            $sender->save();

            BalanceMutation::create([
                'user_id' => $sender->id,
                'type' => 'debit',
                'amount' => $request->amount,
                'current_balance' => $sender->saldo,
                'category' => 'transfer_out',
                'description' => 'Transfer ke ' . $receiver->nama_lengkap,
                'related_user_id' => $receiver->id
            ]);

            // B. PENERIMA (Kredit)
            $receiver->saldo += $request->amount;
            $receiver->save();

            BalanceMutation::create([
                'user_id' => $receiver->id,
                'type' => 'credit',
                'amount' => $request->amount,
                'current_balance' => $receiver->saldo,
                'category' => 'transfer_in',
                'description' => 'Terima saldo dari ' . $sender->nama_lengkap,
                'related_user_id' => $sender->id
            ]);

            // C. Simpan Transaksi (Sudah ada 'type' => 'transfer', AMAN)
            return Transaction::create([
                'transaction_code' => 'TRF-' . time() . rand(100, 999),
                'user_id' => $sender->id,
                'total_bayar' => $request->amount,
                'type' => 'transfer',
                'description' => 'Transfer ke ' . $receiver->nama_lengkap,
                'status' => 'paid',
                'tanggal_transaksi' => now(),
            ]);
        });

        // 5. KIRIM NOTIFIKASI
        // Gunakan variabel $trx hasil dari DB::transaction di atas
        if ($trx) {
            $formattedAmount = number_format($request->amount, 0, ',', '.');

            // A. Notifikasi ke PENGIRIM
            NotificationService::send(
                $sender->id,
                'Transfer Berhasil',
                "Anda berhasil mengirim Rp $formattedAmount ke {$receiver->nama_lengkap}.",
                'transaction',
                [
                    'transaction_id' => $trx->id,
                    'amount' => $request->amount,
                    'counterparty' => $receiver->nama_lengkap,
                    'action' => 'transfer_out'
                ]
            );

            // B. Notifikasi ke PENERIMA
            NotificationService::send(
                $receiver->id,
                'Dana Masuk',
                "Anda menerima Rp $formattedAmount dari {$sender->nama_lengkap}.",
                'transaction',
                [
                    'transaction_id' => $trx->id,
                    'amount' => $request->amount,
                    'counterparty' => $sender->nama_lengkap,
                    'action' => 'transfer_in'
                ]
            );
        }

        // 6. RESPONSE JSON (Baru return di sini)
        return response()->json([
            'message' => 'Transfer Berhasil!',
            'amount' => $request->amount,
            'sisa_saldo_anda' => $sender->saldo,
            'penerima' => $receiver->nama_lengkap,
            'transaction_code' => $trx->transaction_code // Opsional: Balikin kode transaksi
        ]);
    }

    // 4. GET RECENT TRANSFER USERS (5 Terakhir)
    public function getRecentTransfers(Request $request)
    {
        $myId = $request->user()->id;

        $recentIds = BalanceMutation::where('user_id', $myId)
            ->whereIn('category', ['transfer_out', 'transfer_in']) // Ini akan cocok dengan kode transfer di atas
            ->whereNotNull('related_user_id')
            ->latest()
            ->get()
            ->pluck('related_user_id')
            ->unique()
            ->take(5);

        // TAMBAHAN: Cek jika kosong, langsung return array kosong
        if ($recentIds->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $users = User::whereIn('id', $recentIds)->get(['id', 'nama_lengkap', 'username', 'profile_photo']);

        foreach ($users as $u) {
            $u->append('profile_photo_url');
        }

        return response()->json(['data' => $users->values()]);
    }


}
