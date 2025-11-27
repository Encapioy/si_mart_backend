<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\TopUp;

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
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $user = $request->user();

        if ($user->pin !== $request->pin)
            return response()->json(['message' => 'PIN Salah'], 401);
        if ($user->saldo < $request->amount)
            return response()->json(['message' => 'Saldo kurang'], 400);

        // Potong Saldo Utama (Sesuai request dulu)
        $user->saldo -= $request->amount;
        $user->save();

        // ... (setelah $user->saldo -= $request->amount; $user->save())
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
            'target_identity_code' => 'required|string', // QR / Member ID Penerima
            'amount' => 'required|integer|min:1000',     // Minimal transfer 1000
            'pin' => 'required|string|size:6',           // PIN Pengirim
        ]);

        $sender = $request->user(); // Pengirim (Saya)

        // 1. Cek PIN Pengirim
        if ($sender->pin !== $request->pin) {
            return response()->json(['message' => 'PIN Salah'], 401);
        }

        // 2. Cek Saldo Pengirim
        if ($sender->saldo < $request->amount) {
            return response()->json(['message' => 'Saldo tidak cukup'], 400);
        }

        // 3. Cari Penerima (Pakai Logic Pencarian Pintar)
        // Bisa scan QR (Member ID), Username, atau NFC
        $receiver = User::where('member_id', $request->target_identity_code)
            ->orWhere('username', $request->target_identity_code)
            ->orWhere('nfc_id', $request->target_identity_code)
            ->first();

        // Validasi Penerima
        if (!$receiver)
            return response()->json(['message' => 'Penerima tidak ditemukan'], 404);
        if ($receiver->id == $sender->id)
            return response()->json(['message' => 'Tidak bisa transfer ke diri sendiri'], 400);


        // 4. EKSEKUSI TRANSFER (Pakai DB Transaction biar aman)
        return DB::transaction(function () use ($sender, $receiver, $request) {
            // Kurangi Saldo Pengirim
            $sender->saldo -= $request->amount;
            $sender->save();

            $this->recordMutation($sender, $request->amount, 'debit', 'transfer', 'Transfer ke ' . $receiver->nama_lengkap);


            // Tambah Saldo Penerima
            $receiver->saldo += $request->amount;
            $receiver->save();

            $this->recordMutation($receiver, $request->amount, 'credit', 'transfer', 'Terima saldo dari ' . $sender->nama_lengkap);

            // Tapi untuk sekarang return sukses aja cukup.

            return response()->json([
                'message' => 'Transfer Berhasil!',
                'amount' => $request->amount,
                'sisa_saldo_anda' => $sender->saldo,
                'penerima' => $receiver->nama_lengkap
            ]);
        });
    }
}