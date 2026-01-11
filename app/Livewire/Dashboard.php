<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        // Ambil Member ID. Kalau kosong (user lama), kita kasih peringatan/fallback
        $memberId = $user->member_id;

        return view('livewire.dashboard', [
            'memberId' => $memberId,
            'user' => $user
        ]);
    }
}