<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Register extends Component
{
    // Properti Data Form
    public $nama_lengkap = '';
    public $username = '';
    public $email = '';
    public $no_hp = '';
    public $pin = '';
    public $password = '';
    public $password_confirmation = '';

    public function register()
    {
        // 1. Validasi Input (Diperketat)
        $this->validate([
            'nama_lengkap' => 'required|string|min:3',
            'username' => 'required|alpha_dash|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'no_hp' => 'required|numeric|digits_between:10,14|unique:users,no_hp',
            'pin' => 'required|numeric|digits:6', // PIN wajib 6 angka
            'password' => 'required|min:6|confirmed',
        ]);

        // 2. Simpan ke Database
        $user = User::create([
            'nama_lengkap' => $this->nama_lengkap,
            'username' => $this->username,
            'email' => $this->email,
            'no_hp' => $this->no_hp,
            'pin' => $this->pin, // PIN Transaksi (biasanya disimpan plain atau hash tergantung kebijakanmu, default hash is safer but request might differ. Asumsi: plain/string sesuai controller admin sebelumnya)
            'password' => Hash::make($this->password),
            'role' => 'user',
            'saldo' => 0,
            'status_verifikasi' => 'pending'
        ]);

        // 3. Auto Login
        Auth::guard('web')->login($user);

        // 4. Redirect
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.register');
    }
}