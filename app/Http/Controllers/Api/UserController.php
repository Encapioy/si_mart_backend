<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // 1. MENAUTKAN AKUN ANAK (Pairing)
    // Diakses oleh: Orang Tua
    public function pairChild(Request $request)
    {
        $request->validate([
            'child_username' => 'required|string',
            'child_pin' => 'required|string|size:6', // PIN harus 6 digit
        ]);

        // Cari akun anak berdasarkan username
        $child = User::where('username', $request->child_username)->first();

        // Cek 1: Apakah anaknya ada?
        if (!$child) {
            return response()->json(['message' => 'Username anak tidak ditemukan'], 404);
        }

        // Cek 2: Apakah PIN-nya benar?
        // (Asumsi PIN disimpan plain text sesuai skema database tadi.
        // Jika di-hash, pakai Hash::check)
        if ($child->pin !== $request->child_pin) {
            return response()->json(['message' => 'PIN Anak salah'], 401);
        }

        // Cek 3: Jangan sampai menautkan diri sendiri
        if ($child->id == Auth::id()) {
            return response()->json(['message' => 'Tidak bisa menautkan diri sendiri'], 400);
        }

        // Cek 4: Apakah anak ini sudah punya orang tua?
        if ($child->parent_id != null) {
            return response()->json(['message' => 'Akun ini sudah tertaut dengan orang tua lain'], 400);
        }

        // EKSEKUSI: Simpan ID Orang Tua (User yg login) ke kolom parent_id si Anak
        $child->parent_id = Auth::id();
        $child->save();

        return response()->json([
            'message' => 'Berhasil menautkan akun anak',
            'data' => $child
        ]);
    }

    // 2. LIHAT DAFTAR ANAK SAYA
    // Diakses oleh: Orang Tua
    public function getMyChildren()
    {
        // Ambil user yang parent_id-nya adalah ID saya
        $children = User::where('parent_id', Auth::id())->get();

        return response()->json([
            'message' => 'Daftar anak anda',
            'data' => $children
        ]);
    }

    // 3. AJUKAN VERIFIKASI (UPLOAD KTP)
    public function uploadVerification(Request $request)
    {
        // Validasi
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|size:16', // NIK biasanya 16 digit
            'alamat_ktp' => 'required|string',
            'foto_ktp' => 'required|image|max:2048', // Max 2MB
            'foto_selfie_ktp' => 'required|image|max:2048',
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $user = $request->user();

        // Cek: Kalau sudah verified, jangan upload lagi
        if ($user->status_verifikasi == 'verified') {
            return response()->json(['message' => 'Akun anda sudah terverifikasi!'], 400);
        }

        // Proses Upload Gambar
        // Disimpan di folder: storage/app/public/verifikasi
        if ($request->hasFile('foto_ktp')) {
            $pathKtp = $request->file('foto_ktp')->store('verifikasi', 'public');
            $user->foto_ktp = $pathKtp;
        }

        if ($request->hasFile('foto_selfie_ktp')) {
            $pathSelfie = $request->file('foto_selfie_ktp')->store('verifikasi', 'public');
            $user->foto_selfie_ktp = $pathSelfie;
        }

        // Update Data User
        $user->nik = $request->nik;
        $user->alamat_ktp = $request->alamat_ktp;
        $user->status_verifikasi = 'pending'; // Ubah status jadi Pending (Menunggu persetujuan admin)
        // Kalau mau otomatis verified tanpa admin, ubah jadi 'verified' di sini.

        $user->save();

        return response()->json([
            'message' => 'Data verifikasi berhasil dikirim. Tunggu persetujuan Admin.',
            'status' => $user->status_verifikasi
        ]);
    }

    // 4. (KHUSUS ADMIN) SETUJUI VERIFIKASI USER
    public function verifyUser(Request $request, $user_id)
    {
        // Nanti logic ini dipasang middleware khusus admin
        $user = User::find($user_id);
        if (!$user)
            return response()->json(['message' => 'User not found'], 404);

        $user->status_verifikasi = 'verified';
        $user->save();

        return response()->json(['message' => 'User berhasil diverifikasi menjadi Customer']);
    }

    // 5. CEK SALDO (Support NFC & QR Code)
    public function checkBalance(Request $request)
    {
        // Validasi: Kita namakan inputnya 'kode_identitas' biar umum
        // Isinya bisa kode NFC (misal: 839201) atau Username dari QR (misal: udin_petot)
        $validator = Validator::make($request->all(), [
            'kode_identitas' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $kode = $request->kode_identitas;

        // LOGIKA PENCARIAN PINTAR (OR WHERE)
        // Cari user yang nfc_id-nya = $kode
        // ATAU
        // Cari user yang username-nya = $kode
        $user = User::where('nfc_id', $kode)
            ->orWhere('username', $kode)
            ->orWhere('member_id', $kode)
            ->first();

        // Jika tidak ketemu di kedua kolom tersebut
        if (!$user) {
            return response()->json([
                'message' => 'Identitas tidak ditemukan. Coba scan ulang.',
            ], 404);
        }

        // Kembalikan Data
        return response()->json([
            'message' => 'Data User Ditemukan',
            'data' => [
                'nama_lengkap' => $user->nama_lengkap,
                'username' => $user->username,
                'member_id' => $user->member_id,
                'saldo' => $user->saldo,
                'status' => $user->status_verifikasi,
                'foto' => $user->foto_selfie_ktp
            ]
        ]);
    }
}