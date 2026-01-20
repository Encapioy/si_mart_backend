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

        // Ambil iklan yang aktif saja
        $ads = \App\Models\Advertisement::where('status', 'active')
            ->where('end_time', '>', now())
            ->latest()
            ->get();

        return view('Livewire.dashboard', [
            'memberId' => $memberId,
            'user' => $user,
            'ads' => $ads // <--- Pastikan variable ini dikirim
        ]);
    }
}