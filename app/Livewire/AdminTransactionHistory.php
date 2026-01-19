<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\BalanceMutation;
use Illuminate\Support\Facades\DB;

class AdminTransactionHistory extends Component
{
    use WithPagination;

    public $search = ''; // Fitur Pencarian

    // Reset pagination saat search berubah
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function deleteTransaction($id) // $id bisa ID atau Transaction Code
    {
        $trx = Transaction::find($id);

        if (!$trx)
            return;

        if ($trx->status != 'paid') {
            $trx->delete();
            $this->dispatch('show-success', message: 'Data dihapus.');
            return;
        }

        DB::transaction(function () use ($trx) {
            $user = User::find($trx->user_id);

            // 1. Refund Saldo
            $user->saldo += $trx->total_bayar;
            $user->save();

            // 2. Catat Mutasi
            BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $trx->total_bayar,
                'current_balance' => $user->saldo,
                'category' => 'correction',
                'description' => 'REFUND: Batal Transaksi ' . $trx->transaction_code
            ]);

            // 3. Hapus Data
            $trx->delete();
        });

        $this->dispatch('show-success', message: 'Transaksi Dibatalkan. Dana dikembalikan ke User.');
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        $transactions = Transaction::with(['user', 'store.owner'])
            ->whereIn('type', ['payment', 'transfer_in', 'transfer_out', 'transfer'])
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

        return view('Livewire.admin-transaction-history', [
            'transactions' => $transactions
        ]);
    }
}