<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

class Dashboard extends Component
{
    #[Layout('components.layouts.userbar')]
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