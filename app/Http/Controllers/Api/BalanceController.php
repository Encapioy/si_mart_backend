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
            'target_identity_code' => 'required|string',
            'amount' => 'required|integer|min:1000',
            'pin' => 'required|string', // Validasi size bisa dihandle di logic bawah
        ]);

        $sender = $request->user();

        // 1. Cek PIN Pengirim (Pakai trim & string casting biar aman)
        if (trim((string) $sender->pin) !== trim((string) $request->pin)) {
            return response()->json(['message' => 'PIN Salah'], 401);
        }

        // 2. Cek Saldo
        if ($sender->saldo < $request->amount) {
            return response()->json(['message' => 'Saldo tidak cukup'], 400);
        }

        // 3. Cari Penerima (Update: Tambah pencarian via No HP)
        $kode = $request->target_identity_code;
        $receiver = User::where('member_id', $kode)
            ->orWhere('username', $kode)
            ->orWhere('nfc_id', $kode)
            ->orWhere('no_hp', $kode) // <--- TAMBAHAN: Bisa transfer via No HP
            ->first();

        // Validasi Penerima
        if (!$receiver)
            return response()->json(['message' => 'Penerima tidak ditemukan'], 404);
        if ($receiver->id == $sender->id)
            return response()->json(['message' => 'Tidak bisa transfer ke diri sendiri'], 400);

        // 4. EKSEKUSI TRANSFER
        return DB::transaction(function () use ($sender, $receiver, $request) {

            // A. PENGIRIM (Debit)
            $sender->saldo -= $request->amount;
            $sender->save();

            // PERBAIKAN DI SINI: Category harus 'transfer_out' (bukan 'transfer')
            $this->recordMutation(
                $sender,
                $request->amount,
                'debit',
                'transfer_out', // <--- PENTING!
                'Transfer ke ' . $receiver->nama_lengkap,
                $receiver->id
            );

            // B. PENERIMA (Kredit)
            $receiver->saldo += $request->amount;
            $receiver->save();

            // PERBAIKAN DI SINI: Category harus 'transfer_in' (bukan 'transfer')
            $this->recordMutation(
                $receiver,
                $request->amount,
                'credit',
                'transfer_in', // <--- PENTING!
                'Terima saldo dari ' . $sender->nama_lengkap,
                $sender->id
            );

            return response()->json([
                'message' => 'Transfer Berhasil!',
                'amount' => $request->amount,
                'sisa_saldo_anda' => $sender->saldo,
                'penerima' => $receiver->nama_lengkap
            ]);
        });
    }

    // 4. GET RECENT TRANSFER USERS (5 Terakhir)
    public function getRecentTransfers(Request $request)
    {
        $myId = $request->user()->id;

        $recentIds = \App\Models\BalanceMutation::where('user_id', $myId)
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