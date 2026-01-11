<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ProfilePage extends Component
{
    public function logout()
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();


        return redirect()->route('login'); // Redirect ke halaman login
    }

    public function render()
    {
        return view('livewire.profile-page', [
            'user' => Auth::user()
        ])->layout('components.layouts.userbar');
    }
}