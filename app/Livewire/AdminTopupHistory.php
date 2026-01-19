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

        // Jika status bukan approved (misal pending/failed), hapus saja tanpa cek saldo
        if ($topup->status != 'approved') {
            $topup->delete();
            $this->dispatch('show-success', message: 'Data sampah dihapus.');
            return;
        }

        $user = User::find($topup->user_id);

        // --- LOGIC BARU: CEK SALDO USER ---
        if ($user->saldo < $topup->amount) {
            // Kirim notif error ke layar admin
            $this->dispatch('show-error', message: 'GAGAL: Saldo User tidak cukup! Uang TopUp sudah terpakai.');
            return; // BERHENTI DI SINI
        }

        // Jika lolos pengecekan, baru eksekusi
        DB::transaction(function () use ($topup, $user) {

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

        $this->dispatch('show-success', message: 'Topup Dibatalkan. Saldo User ditarik kembali.');
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