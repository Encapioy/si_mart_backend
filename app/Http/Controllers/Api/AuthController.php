<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
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

    // CEK KETERSEDIAAN USERNAME/EMAIL (REAL-TIME)
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // 1. Cek Username
        if ($request->filled('username')) {
            $isTaken = User::where('username', $request->username)->exists();
            if ($isTaken) {
                return response()->json([
                    'status' => 'unavailable',
                    'field' => 'username',
                    'message' => 'Username sudah dipakai orang lain.'
                ], 200); // Return 200 biar frontend gak error, tapi statusnya unavailable
            }
        }

        // 2. Cek Email
        if ($request->filled('email')) {
            $isTaken = User::where('email', $request->email)->exists();
            if ($isTaken) {
                return response()->json([
                    'status' => 'unavailable',
                    'field' => 'email',
                    'message' => 'Email sudah terdaftar.'
                ], 200);
            }
        }

        return response()->json([
            'status' => 'available',
            'message' => 'Data tersedia.'
        ]);
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

    // ==========================================================
    // KHUSUS WEB ADMIN / DASHBOARD (JANGAN HAPUS YANG API)
    // ==========================================================

    // 1. Tampilkan Halaman Login HTML
    public function showLoginForm()
    {
        // Jika sudah login, langsung lempar sesuai role
        if (Auth::guard('web')->check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    // 2. Proses Login Web (Satu Pintu)
    public function loginWeb(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // A. CEK ADMIN (Pakai Guard 'admin')
        $admin = Admin::where('username', $request->username)->first();
        if ($admin && Hash::check($request->password, $admin->password)) {

            // PENTING: Login pakai guard 'admin'
            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();

            // Redirect
            if ($admin->role === 'keuangan') {
                return redirect()->route('admin.topup');
            }
            return redirect()->route('admin.dashboard');
        }

        // B. CEK USER (Pakai Guard default 'web')
        $user = User::where('username', $request->username)
            ->orWhere('no_hp', $request->username)
            ->first();
        if ($user && Hash::check($request->password, $user->password)) {

            // Login pakai guard default
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('user.home');
        }

        return back()->with('error', 'Username atau Password salah!');
    }

    // 3. Helper Redirect (Biar rapi)
    private function redirectBasedOnRole($user)
    {
        // Pastikan cek role hanya jika user itu Admin
        // Kalau model User biasa gak punya role, langsung skip
        if ($user instanceof Admin) {
            if ($user->role === 'keuangan') {
                return redirect()->route('admin.topup');
            } elseif ($user->role === 'pusat' || $user->role === 'developer') {
                return redirect()->route('admin.dashboard');
            }
        }

        // Default User Biasa
        return redirect()->route('user.home');
    }

    // 4. Logout Web
    public function logoutWeb(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}