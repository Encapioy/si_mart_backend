<?php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $username = '';
    public $password = '';

    public function login()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = ['username' => $this->username, 'password' => $this->password];

        // 1. Cek Admin
        if (Auth::guard('admin')->attempt($credentials, true)) {
            session()->regenerate();

            // AMBIL DATA ADMIN YANG BARU LOGIN
            $admin = Auth::guard('admin')->user();

            // LOGIKA REDIRECT BERDASARKAN ROLE
            if ($admin->role === 'kasir') {
                return redirect()->route('admin.topup'); // Langsung ke halaman topup
            }

            // Default (Admin Pusat / Dreamland)
            return redirect()->route('admin.dashboard');
        }

        // 2. Cek User Web
        if (Auth::guard('web')->attempt($credentials, true)) {
            session()->regenerate();
            return redirect()->route('dashboard');
        }

        $this->addError('username', 'Username atau Password salah.');
    }

    public function render()
    {
        return view('Livewire.login');
    }
}