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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

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
            'password' => 'required|min:6',
            'pin' => 'required|digits:6',
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

    // CEK KETERSEDIAAN USERNAME/EMAIL/NO HP (REAL-TIME API)
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|min:3|max:255',
            'email' => 'nullable|string|email|max:255',
            'no_hp' => 'nullable|numeric|digits_between:10,15', // Tambahan validasi No HP
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // 1. Cek Username
        if ($request->filled('username')) {

            // SANITASI INPUT: Ubah spasi jadi underscore
            $cleanUsername = preg_replace('/\s+/', '_', $request->username);

            // Cek format (Opsional, sesuaikan kebutuhan)
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $cleanUsername)) {
                return response()->json([
                    'status' => 'unavailable',
                    'field' => 'username',
                    'message' => 'Format username tidak valid (gunakan huruf, angka, _).'
                ], 200);
            }

            // Cek Database menggunakan username yang sudah bersih
            $isTaken = User::where('username', $cleanUsername)->exists();

            if ($isTaken) {
                return response()->json([
                    'status' => 'unavailable',
                    'field' => 'username',
                    'message' => 'Username sudah dipakai orang lain.'
                ], 200);
            }

            // Jika cleanUsername beda dengan request asli (karena ada spasi),
            // kita bisa kasih info balik agar frontend mengoreksinya (Opsional)
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

        // 3. Cek No HP (BARU)
        if ($request->filled('no_hp')) {
            // Pastikan kolom di database kamu namanya 'no_hp'
            $isTaken = User::where('no_hp', $request->no_hp)->exists();
            if ($isTaken) {
                return response()->json([
                    'status' => 'unavailable',
                    'field' => 'no_hp',
                    'message' => 'Nomor HP sudah terdaftar.'
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
        // A. LOGIC UNTUK API / MOBILE APP (Sanctum)
        if ($request->wantsJson() || $request->is('api/*')) {

            // Pastikan user memang punya token (cegah error)
            if ($request->user()) {
                // Hapus token yang sedang dipakai
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Logout berhasil'
            ]);
        }

        // B. LOGIC UNTUK WEB ADMIN / BROWSER (Session)

        // 1. Logout dari guard web
        Auth::guard('web')->logout();

        // 2. Hapus sesi browser (PENTING)
        $request->session()->invalidate();

        // 3. Regenerate Token CSRF (Untuk keamanan)
        $request->session()->regenerateToken();

        // 4. Lempar ke halaman login
        return redirect()->route('login');
    }

    // 4. CEK PROFIL SAYA
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // 5. KIRIM OTP KE EMAIL
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // 1. Generate OTP 6 Digit
        $otp = rand(100000, 999999);
        $email = $request->email;

        // 2. Simpan di Cache selama 5 menit
        // Key: "otp_reset_email@gmail.com"
        Cache::put('otp_reset_' . $email, $otp, 300);

        // 3. Kirim Email (Pake Raw biar cepet, bisa diganti Mailable view nanti)
        try {
            Mail::raw("Kode OTP Reset Anda adalah: $otp. Jangan berikan ke siapapun. Berlaku 5 menit.", function ($message) use ($email) {
                $message->to($email)
                    ->subject('Kode OTP Reset SI Pay');
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal kirim email. Cek konfigurasi SMTP.'], 500);
        }

        return response()->json(['status' => 'success', 'message' => 'OTP telah dikirim ke email']);
    }

    // 6. RESET PASSWORD (Verifikasi OTP + Ganti Pass)
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
            'new_password' => 'required|min:6|confirmed'
        ]);

        // 1. Cek OTP
        $cachedOtp = Cache::get('otp_reset_' . $request->email);
        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['message' => 'OTP Salah atau Kedaluwarsa'], 400);
        }

        // 2. Update Password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->new_password);
        $user->save();

        // 3. Hapus OTP
        Cache::forget('otp_reset_' . $request->email);

        return response()->json(['status' => 'success', 'message' => 'Password berhasil diubah']);
    }

    // 7. RESET PIN (Verifikasi OTP + Ganti PIN)
    public function resetPin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
            'new_pin' => 'required|digits:6'
        ]);

        // 1. Cek OTP
        $cachedOtp = Cache::get('otp_reset_' . $request->email);
        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['message' => 'OTP Salah atau Kedaluwarsa'], 400);
        }

        // 2. Update PIN
        $user = User::where('email', $request->email)->first();
        // PERHATIAN: Ini Plain Text sesuai request terakhir kamu.
        // Kalau mau hash, ganti jadi Hash::make($request->new_pin)
        $user->pin = $request->new_pin;
        $user->save();

        // 3. Hapus OTP
        Cache::forget('otp_reset_' . $request->email);

        return response()->json(['status' => 'success', 'message' => 'PIN berhasil diubah']);
    }

    // 8. GENERATE QR CODE
    public function getMyQrCode(Request $request)
    {
        $user = $request->user();

        // Pastikan Member ID sudah ada (jaga-jaga)
        if (!$user->member_id) {
            return response()->json(['message' => 'Member ID belum digenerate'], 400);
        }

        // PAYLOAD MURNI MEMBER ID
        // Hasil: "202569833207"
        $payload = $user->member_id;

        return response()->json([
            'status' => 'success',
            'data' => [
                'member_id' => $user->member_id,
                'nama' => $user->nama_lengkap,
                'qr_payload' => $payload, // Cuma angka
                'qr_image_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . $payload
            ]
        ]);
    }
}