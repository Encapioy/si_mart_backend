<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use Livewire\Attributes\Layout;

class AdminTransactionHistory extends Component
{
    use WithPagination;

    public $search = ''; // Fitur Pencarian

    // Reset pagination saat search berubah
    public function updatedSearch()
    {
        $this->resetPage();
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        $transactions = Transaction::with(['user', 'store.owner'])
            ->whereIn('type', ['payment', 'transfer'])
            ->when($this->search, function ($query) {
                // Logic Pencarian Pintar
                $query->where(function ($q) {
                    $q->where('transaction_code', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($u) {
                            $u->where('nama_lengkap', 'like', '%' . $this->search . '%')
                                ->orWhere('username', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('store', function ($s) {
                            $s->where('nama_toko', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin-transaction-history', [
            'transactions' => $transactions
        ]);
    }
}