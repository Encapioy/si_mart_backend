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
    public $password_confirmation = '';
    public $pin = '';
    public $pin_confirmation = '';

    // Variabel untuk menyimpan status validasi real-time
    public $usernameStatus = '';
    public $emailStatus = '';
    public $noHpStatus = '';

    // Status Kecocokan (Match)
    public $passwordMatchStatus = '';
    public $pinMatchStatus = '';

    public function register()
    {

        // Pastikan sanitasi juga terjadi saat tombol submit ditekan (jaga-jaga)
        $this->username = preg_replace('/\s+/', '_', $this->username);

        // 1. Validasi Input (Disamakan dengan API)
        $this->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email' => 'required|string|email|max:255|unique:users',
            'no_hp' => 'required|numeric|digits_between:10,14|unique:users,no_hp',
            'password' => 'required|string|min:6|confirmed', // Web butuh konfirmasi pass
            'pin' => 'required|digits:6|confirmed', // API bilang nullable, kita ikut
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
            'saldo' => 0,
            'status_verifikasi' => 'unverified' // Livewire lama 'pending', API 'unverified'
        ]);

        // 4. Auto Login & Redirect
        Auth::guard('web')->login($user);

        session()->flash('success', 'Registrasi berhasil! ID Member Anda: ' . $memberId);

        return redirect()->route('dashboard');
    }

    // --- MAGIC METHOD 1: Cek Username ---
    // Function ini otomatis jalan saat properti $username berubah
    public function updatedUsername()
    {
        // 1. SANITASI: Ubah semua whitespace (spasi/tab) menjadi underscore
        // Contoh: "budi santoso" -> "budi_santoso"
        $this->username = preg_replace('/\s+/', '_', $this->username);

        // 2. Reset status
        $this->usernameStatus = '';
        $this->resetErrorBag('username');

        // 3. Cek validasi dasar
        // Regex: /^[a-zA-Z0-9_]+$/ artinya hanya boleh Huruf, Angka, dan Underscore.
        if (strlen($this->username) < 3) {
            return;
        }

        // Validasi format (Opsional: kalau mau menolak simbol aneh selain _)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $this->username)) {
            $this->addError('username', 'Username hanya boleh huruf, angka, dan underscore.');
            return;
        }

        // 4. Cek Database
        $exists = User::where('username', $this->username)->exists();

        if ($exists) {
            $this->addError('username', 'Username ini sudah dipakai orang lain.');
            $this->usernameStatus = 'taken';
        } else {
            $this->usernameStatus = 'available';
        }
    }

    // --- MAGIC METHOD 2: Cek Email ---
    public function updatedEmail()
    {
        $this->emailStatus = '';
        $this->resetErrorBag('email');

        // Validasi format email sederhana
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $exists = User::where('email', $this->email)->exists();

        if ($exists) {
            $this->addError('email', 'Email ini sudah terdaftar.');
            $this->emailStatus = 'taken';
        } else {
            $this->emailStatus = 'available';
        }
    }

    // --- MAGIC METHOD 3: Cek no hp ---
    public function updatedNoHp()
    {
        // Reset status & error
        $this->noHpStatus = '';
        $this->resetErrorBag('no_hp');

        // Validasi format sederhana:
        // 1. Harus numeric (angka saja)
        // 2. Minimal 10 digit (nomor HP indo biasanya 10-13 digit)
        if (!is_numeric($this->no_hp) || strlen($this->no_hp) < 10) {
            return; // Jangan cek DB kalau formatnya belum benar
        }

        // Cek Database
        // PASTIKAN nama kolom di database kamu adalah 'no_hp'.
        // Jika di DB namanya 'phone', ganti 'no_hp' jadi 'phone'.
        $exists = User::where('no_hp', $this->no_hp)->exists();

        if ($exists) {
            $this->addError('no_hp', 'Nomor HP ini sudah terdaftar.');
            $this->noHpStatus = 'taken';
        } else {
            $this->noHpStatus = 'available';
        }
    }

    // --- LOGIKA CEK KECOCOKAN PASSWORD ---
    public function updatedPasswordConfirmation()
    {
        // 1. Hapus dulu error lama agar tidak nyangkut
        $this->resetErrorBag('password_confirmation');
        $this->passwordMatchStatus = '';

        // Kalau kosong gak usah dicek
        if (empty($this->password_confirmation))
            return;

        // Cek logic
        if ($this->password === $this->password_confirmation) {
            $this->passwordMatchStatus = 'match';
        } else {
            $this->passwordMatchStatus = 'mismatch';
            $this->addError('password_confirmation', 'Password tidak sama.');
        }
    }

    // --- LOGIKA CEK KECOCOKAN PIN ---
    public function updatedPinConfirmation()
    {
        // 1. Hapus dulu error lama
        $this->resetErrorBag('pin_confirmation');
        $this->pinMatchStatus = '';

        if (empty($this->pin_confirmation))
            return;

        if ($this->pin === $this->pin_confirmation) {
            $this->pinMatchStatus = 'match';
        } else {
            $this->pinMatchStatus = 'mismatch';
            $this->addError('pin_confirmation', 'PIN tidak sama.');
        }
    }

    // Optional: Update status saat input utama berubah juga
    public function updatedPassword()
    {
        $this->updatedPasswordConfirmation();
    }
    public function updatedPin()
    {
        $this->updatedPinConfirmation();
    }

    public function render()
    {
        return view('Livewire.register');
    }
}