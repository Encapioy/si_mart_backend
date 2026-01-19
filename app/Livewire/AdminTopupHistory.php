<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TopUp;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\BalanceMutation;
use Illuminate\Support\Facades\DB;

class AdminTopupHistory extends Component
{
    use WithPagination;

    public function deleteTopup($id)
    {
        $topup = TopUp::find($id);

        if (!$topup)
            return;

        // Jangan hapus kalau status pending/failed, langsung delete aja gak usah potong saldo
        if ($topup->status != 'approved') {
            $topup->delete();
            $this->dispatch('show-success', message: 'Data sampah dihapus.');
            return;
        }

        DB::transaction(function () use ($topup) {
            $user = User::find($topup->user_id);

            // 1. Tarik Saldo
            $user->saldo -= $topup->amount;
            $user->save();

            // 2. Catat Mutasi
            BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $topup->amount,
                'current_balance' => $user->saldo,
                'category' => 'correction',
                'description' => 'KOREKSI ADMIN: Batal TopUp #' . $topup->id
            ]);

            // 3. Hapus Data
            $topup->delete();
        });

        $this->dispatch('show-success', message: 'Topup Dibatalkan. Saldo User dikurangi.');
    }

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