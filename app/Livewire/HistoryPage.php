<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination; // Wajib import ini
use App\Models\BalanceMutation;
use Illuminate\Support\Facades\Auth;

class HistoryPage extends Component
{
    use WithPagination; // Aktifkan fitur pagination

    public function render()
    {
        $user = Auth::user();

        // Query sama seperti sebelumnya
        $mutations = BalanceMutation::where('user_id', $user->id)
            ->latest()
            ->paginate(10); // Otomatis handle ?page=2 dst

        return view('livewire.history-page', [
            'mutations' => $mutations
        ])->layout('components.layouts.userbar');
    }
}