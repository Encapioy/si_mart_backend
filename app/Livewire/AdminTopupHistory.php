<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TopUp; // <-- Ganti Transaction jadi TopUp
use Livewire\Attributes\Layout;

class AdminTopupHistory extends Component
{
    use WithPagination;

    #[Layout('components.layouts.admin')]
    public function render()
    {
        // Ambil data dari tabel TopUp
        // Load relasi 'user' (santri) dan 'admin' (kasir) biar query ringan
        $history = TopUp::with(['user', 'admin'])
            ->where('status', 'approved') // Hanya yang sukses
            ->latest()
            ->paginate(10);

        return view('Livewire.admin-topup-history', [
            'history' => $history
        ]);
    }
}