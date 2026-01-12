<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

class ProfilePage extends Component
{
    public function logout()
    {
        // 1. Logout dari Guard Web
        Auth::guard('web')->logout();

        // 2. Hapus Sesi (Penting agar tidak bisa di-back)
        session()->invalidate();
        session()->regenerateToken();

        // 3. Redirect ke Halaman Login
        // Kita tidak pakai return redirect() biasa, tapi pakai helper Livewire
        return $this->redirect(route('login'), navigate: true);
    }

    #[Layout('components.layouts.userbar')]
    public function render()
    {
        return view('Livewire.profile-page', [
            'user' => Auth::user()
        ]);
    }
}