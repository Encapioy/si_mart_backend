<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use App\Models\Advertisement;

class Dashboard extends Component
{
    #[Layout('components.layouts.userbar')]
    public function render()
    {
        $user = Auth::user();

        // Ambil Member ID
        $memberId = $user->member_id;

        // Ambil iklan aktif dengan Eager Loading (with store)
        // Kita perlu relasi 'store' untuk menampilkan nama toko dan foto pemilik/toko
        $ads = Advertisement::with('store')
            ->where('status', 'active')
            ->where('end_time', '>', now())
            ->latest()
            ->get();

        return view('livewire.dashboard', [
            'memberId' => $memberId,
            'user' => $user,
            'ads' => $ads
        ]);
    }
}