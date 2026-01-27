<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    // Kita tetap pakai nama variabel $username biar gak perlu ubah view blade
    public $username = '';
    public $password = '';

    public function login()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // --- 1. CEK ADMIN ---
        // Admin biasanya login pakai username spesifik.
        // Kita coba dulu login ke guard admin menggunakan input sebagai 'username'.
        if (Auth::guard('admin')->attempt(['username' => $this->username, 'password' => $this->password], true)) {
            session()->regenerate();

            $admin = Auth::guard('admin')->user();

            if ($admin->role === 'kasir') {
                return redirect()->route('admin.topup');
            }

            return redirect()->route('admin.dashboard');
        }

        // --- 2. CEK USER (SANTRI) - SMART LOGIN ---
        // Disini kita deteksi inputnya jenis apa

        $fieldType = 'username'; // Default asumsi adalah username

        if (filter_var($this->username, FILTER_VALIDATE_EMAIL)) {
            // Jika formatnya email (ada @ dan domain)
            $fieldType = 'email';
        } elseif (is_numeric($this->username)) {
            // Jika isinya angka semua (contoh: 08123456789)
            $fieldType = 'no_hp';
        }

        // Susun credentials berdasarkan hasil deteksi tadi
        // Contoh: ['email' => 'budi@gmail.com', 'password' => '...']
        // Atau:   ['no_hp' => '08123...', 'password' => '...']
        $userCredentials = [
            $fieldType => $this->username,
            'password' => $this->password
        ];

        if (Auth::guard('web')->attempt($userCredentials, true)) {
            session()->regenerate();
            return redirect()->route('dashboard');
        }

        // Jika gagal semua
        $this->addError('username', 'Login gagal. Pastikan Username/Email/No HP dan Password benar.');
    }

    public function render()
    {
        return view('Livewire.login');
    }
}