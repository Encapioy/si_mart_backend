<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TopUp; // <-- Ganti Transaction jadi TopUp

class AdminTopupHistory extends Component
{
    use WithPagination;

    public function render()
    {
        // Ambil data dari tabel TopUp
        // Load relasi 'user' (santri) dan 'admin' (kasir) biar query ringan
        $history = TopUp::with(['user', 'admin'])
            ->where('status', 'approved') // Hanya yang sukses
            ->latest()
            ->paginate(10);

        return view('livewire.admin-topup-history', [
            'history' => $history
        ])->layout('components.layouts.sidebar');
    }
}