<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

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

    // 6. UPDATE DATA PRIBADI (UMUM & FOTO)
    // Method: POST (karena ada upload file)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // 1. Validasi (Gabungan Text & Foto)
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'sometimes|required|string|max:255',
            // Pastikan unique mengecualikan ID user sendiri
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            'no_hp' => 'sometimes|nullable|string|max:15',
            'profile_photo' => 'sometimes|image|max:2048', // Max 2MB
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // 2. Siapkan data yang akan diupdate dari inputan text
        // Kita tampung dulu di array agar nanti bisa ditambah path foto jika ada
        $dataToUpdate = $request->only(['nama_lengkap', 'username', 'email', 'no_hp']);

        // 3. Cek: Apakah user mengupload foto baru?
        if ($request->hasFile('profile_photo')) {

            // A. Hapus foto lama jika ada (Biar storage gak penuh sampah)
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // B. Simpan foto baru ke folder 'profiles' di public disk
            $path = $request->file('profile_photo')->store('profiles', 'public');

            // C. Masukkan path foto baru ke array data yang akan diupdate
            $dataToUpdate['profile_photo'] = $path;
        }

        // 4. Eksekusi Update Database sekaligus
        // $user->update() akan otomatis hanya mengupdate kolom yang ada di array $dataToUpdate
        $user->update($dataToUpdate);

        // Ambil data user terbaru (fresh) dan lampirkan URL fotonya
        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $user->fresh()->append('profile_photo_url')
        ]);
    }

    // 7. CARI INFO USER LAIN (UNTUK KONFIRMASI TRANSFER)
    // Hanya mengembalikan Nama & Foto (Tanpa Saldo) demi privasi
    public function getUserPublicInfo(Request $request)
    {
        $request->validate([
            'identity_code' => 'required|string', // Bisa Username, NFC, atau Member ID
        ]);

        $kode = $request->identity_code;

        // Cari User
        $user = User::where('member_id', $kode)
            ->orWhere('username', $kode)
            ->orWhere('nfc_id', $kode)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Tujuan tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'User ditemukan',
            'data' => [
                'nama_lengkap' => $user->nama_lengkap,
                'username' => $user->username,
                // URL Foto Profil (Pastikan accessor profile_photo_url sudah ada di Model User)
                'profile_photo_url' => $user->profile_photo_url,
            ]
        ]);
    }

    // 8. CEK VALIDITAS PIN (FIXED TYPE CASTING)
    public function validatePin(Request $request)
    {
        $request->validate(['pin' => 'required']);

        $user = $request->user();

        // Pastikan keduanya string agar perbandingannya aman
        // Trim() untuk menghapus spasi yang mungkin tidak sengaja terketik
        $inputPin = trim((string) $request->pin);
        $dbPin = trim((string) $user->pin);

        if ($inputPin === $dbPin) {
            return response()->json(['message' => 'PIN Benar', 'valid' => true]);
        } else {
            return response()->json(['message' => 'PIN Salah', 'valid' => false], 401);
        }
    }

    // 9. GANTI PASSWORD
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed', // butuh field: new_password_confirmation
        ]);

        $user = $request->user();

        // Cek apakah password lama benar?
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password lama salah!'], 400);
        }

        // Simpan Password Baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password berhasil diganti']);
    }


    // 10. GANTI PIN (FIXED)
    public function changePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required',
            'new_pin' => 'required|string|size:6|confirmed',
        ]);

        $user = $request->user();

        $inputOldPin = trim((string) $request->current_pin);
        $dbPin = trim((string) $user->pin);

        // Cek PIN Lama
        if ($inputOldPin !== $dbPin) {
            return response()->json(['message' => 'PIN lama salah!'], 400);
        }

        // Simpan PIN Baru
        $user->pin = trim((string) $request->new_pin);
        $user->save();

        return response()->json(['message' => 'PIN berhasil diganti']);
    }

    // 11. SYNC KONTAK (Cari Teman sesama Aplikasi)
    public function syncContacts(Request $request)
    {
        // Frontend mengirim array nomor HP: ["08123456", "+628123456", ...]
        $request->validate([
            'contacts' => 'required|array',
            'contacts.*' => 'string',
        ]);

        $inputContacts = $request->contacts;

        // --- TAHAP NORMALISASI (Agar Pencarian Akurat) ---
        // Karena user mungkin simpan nomor formatnya beda-beda (+62, 62, 08, pakai spasi, strip -)
        // Kita bersihkan dulu menjadi format standar (misal: angka saja)

        $cleanContacts = [];
        foreach ($inputContacts as $number) {
            // 1. Hapus karakter selain angka (spasi, strip, plus)
            $clean = preg_replace('/[^0-9]/', '', $number);

            // 2. Handle format +62 menjadi 0 (Opsional, tergantung format di database kamu)
            // Asumsi di database kita simpan format '08...'
            if (str_starts_with($clean, '62')) {
                $clean = '0' . substr($clean, 2);
            }

            $cleanContacts[] = $clean;
        }
        // ------------------------------------------------

        // Cari User yang no_hp-nya ada di daftar bersih tadi
        // Kita juga pastikan tidak mencari diri sendiri
        $matchedUsers = User::whereIn('no_hp', $cleanContacts)
            ->where('id', '!=', $request->user()->id)
            ->get(['id', 'nama_lengkap', 'username', 'no_hp', 'profile_photo']);

        foreach ($matchedUsers as $u) {
            $u->append('profile_photo_url');
        }

        return response()->json([
            'message' => 'Kontak disinkronisasi',
            'total_found' => $matchedUsers->count(),
            'data' => $matchedUsers
        ]);
    }
}