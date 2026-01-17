<?php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $username = '';
    public $password = '';
    public $remember = false;

    public function login()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required',
            'remember' => 'boolean',
        ]);

        $credentials = ['username' => $this->username, 'password' => $this->password];

        // 1. Cek Admin
        if (Auth::guard('admin')->attempt($credentials, $this->remember)) {
            session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        // 2. Cek User Web
        if (Auth::guard('web')->attempt($credentials, $this->remember)) {
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