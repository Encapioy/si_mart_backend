<?php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        // QR User sederhana buat ditunjukin ke kasir (Topup)
        $qrData = 'SIPAY:USER:' . $user->id;

        return view('livewire.dashboard', [
            'user' => $user,
            'qrCode' => $qrData
        ]);
    }
}