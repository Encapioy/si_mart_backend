<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Register extends Component
{
    // Properti Data Form (Disesuaikan dengan API)
    public $nama_lengkap = '';
    public $username = '';
    public $email = '';
    public $no_hp = '';
    public $password = '';
    public $password_confirmation = ''; // Tetap butuh ini untuk UX Web
    public $pin = '';

    // Note: $no_hp dihapus karena di API tidak ada input no_hp.
    // Jika di DB wajib, pastikan API juga diupdate atau kolom db nullable.

    public function register()
    {
        // 1. Validasi Input (Disamakan dengan API)
        $this->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'no_hp' => 'required|numeric|digits_between:10,14|unique:users,no_hp',
            'password' => 'required|string|min:6|confirmed', // Web butuh konfirmasi pass
            'pin' => 'nullable|digits:6', // API bilang nullable, kita ikut
        ]);

        // 2. --- LOGIKA GENERATE MEMBER ID UNIK (Copy dari API) ---
        $tahun = date('Y'); // Ambil tahun sekarang
        $memberId = null;
        $isUnique = false;

        // Lakukan looping sampai nemu angka yang belum dipakai
        while (!$isUnique) {
            // Rumus: Tahun + 8 Angka Acak -> Contoh: 202512345678
            $random = mt_rand(10000000, 99999999);
            $candidateId = $tahun . $random;

            // Cek di database
            if (!User::where('member_id', $candidateId)->exists()) {
                $memberId = $candidateId;
                $isUnique = true; // Keluar dari loop
            }
        }

        // 3. Simpan ke Database (Disamakan dengan API)
        $user = User::create([
            'member_id' => $memberId, // Hasil generate di atas
            'nama_lengkap' => $this->nama_lengkap,
            'username' => $this->username,
            'email' => $this->email,
            'no_hp' => $this->no_hp,
            'password' => Hash::make($this->password),
            'pin' => $this->pin,

            // Default Values sesuai API
            'role' => 'user',
            'saldo' => 0,
            'status_verifikasi' => 'unverified' // Livewire lama 'pending', API 'unverified'
        ]);

        // 4. Auto Login & Redirect
        Auth::guard('web')->login($user);

        session()->flash('success', 'Registrasi berhasil! ID Member Anda: ' . $memberId);

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.register');
    }
}