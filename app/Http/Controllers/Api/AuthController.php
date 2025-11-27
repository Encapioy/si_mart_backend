<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\UserDevice;

class AuthController extends Controller
{
    // 1. REGISTER (Khusus Pendaftaran Murid/User Baru)
    public function register(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'pin' => 'nullable|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi Gagal',
                'errors' => $validator->errors() // Akan memberitahu detail: "Email has already been taken"
            ], 422); // 422 adalah kode Unprocessable Entity
        }

        // --- LOGIKA GENERATE MEMBER ID UNIK ---
        $tahun = date('Y'); // Ambil tahun sekarang (2025)
        $memberId = null;
        $isUnique = false;

        // Lakukan looping sampai nemu angka yang belum dipakai
        while (!$isUnique) {
            // Rumus: Tahun + 8 Angka Acak
            // Hasil: 202512345678
            $random = mt_rand(10000000, 99999999);
            $candidateId = $tahun . $random;

            // Cek di database
            if (!User::where('member_id', $candidateId)->exists()) {
                $memberId = $candidateId;
                $isUnique = true; // Keluar dari loop
            }
        }

        // Buat User Baru
        $user = User::create([
            'member_id' => $memberId,
            'nama_lengkap' => $request->nama_lengkap,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'pin' => $request->pin,
            'saldo' => 0,
            'status_verifikasi' => 'unverified' // Default belum verified
        ]);

        // Buat Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil',
            'role' => 'user',
            'member_id' => $user->member_id,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    // 2. LOGIN (Bisa Admin, Bisa User)
    public function login(Request $request)
    {
        // Validasi: Wajib kirim device_id (Unique ID dari HP)
        $request->validate([
            'login' => 'required', // Bisa Username ATAU Email
            'password' => 'required',
            'device_id' => 'required|string',
        ]);

        // A. LOGIKA "USERNAME ATAU EMAIL"
        // Kita cari user yang username-nya cocok ATAU email-nya cocok
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Cari di Tabel User (Prioritas Utama)
        $user = User::where($loginType, $request->login)->first();

        // Jika tidak ketemu di User, Cek di Admin (Opsional kalau Admin mau fitur ini juga)
        if (!$user) {
            // Asumsi Admin cuma pake username
            $admin = Admin::where('username', $request->login)->first();
        }

        // --- SKENARIO 1: USER BIASA ---
        if ($user && Hash::check($request->password, $user->password)) {

            // B. CEK DEVICE ID (Apakah HP ini sudah dikenal?)
            $isDeviceTrusted = UserDevice::where('user_id', $user->id)
                ->where('device_id', $request->device_id)
                ->exists();

            if ($isDeviceTrusted) {
                // DEVICE LAMA -> Langsung Login Sukses

                // Update last login
                UserDevice::where('user_id', $user->id)
                    ->where('device_id', $request->device_id)
                    ->update(['last_login_at' => now()]);

                // Hapus token lama & buat baru
                $user->tokens()->delete();
                $token = $user->createToken('user_token')->plainTextToken;

                return response()->json([
                    'message' => 'Login Berhasil',
                    'status' => 'success',
                    'role' => 'user',
                    'access_token' => $token,
                    'user' => $user
                ]);

            } else {
                // DEVICE BARU -> Tahan Dulu! Minta PIN.
                return response()->json([
                    'message' => 'Device baru terdeteksi. Silakan verifikasi PIN.',
                    'status' => 'require_pin', // Sinyal ke Frontend buat buka layar PIN
                    'user_id' => $user->id     // Kirim ID buat tahap selanjutnya
                ], 200);
            }
        }

        // --- SKENARIO 2: CEK LOGIN ADMIN ---
        $admin = Admin::where('username', $request->login)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {
            $admin->tokens()->delete();
            $token = $admin->createToken('admin_token')->plainTextToken;
            return response()->json([
                'message' => 'Login Admin Berhasil',
                'status' => 'success',
                'role' => $admin->role,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $admin
            ]);
        }

        return response()->json(['message' => 'Username/Email atau Password salah'], 401);
    }

    // 3. VERIFIKASI DEVICE BARU (Tahap Kedua)
    public function verifyNewDevice(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'device_id' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        $user = User::find($request->user_id);

        // Cek PIN
        if ($user->pin !== $request->pin) {
            return response()->json(['message' => 'PIN Salah! Verifikasi gagal.'], 401);
        }

        // Jika PIN Benar -> CATAT DEVICE INI SEBAGAI TERPERCAYA
        UserDevice::create([
            'user_id' => $user->id,
            'device_id' => $request->device_id,
            'last_login_at' => now()
        ]);

        // Berikan Token
        $user->tokens()->delete();
        $token = $user->createToken('user_token')->plainTextToken;

        return response()->json([
            'message' => 'Verifikasi Berhasil. Device telah didaftarkan.',
            'status' => 'success',
            'role' => 'user',
            'access_token' => $token,
            'user' => $user
        ]);
    }

    // 4. LOGOUT
    public function logout(Request $request)
    {
        // Hapus token yang sedang dipakai saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    // 4. CEK PROFIL SAYA
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}