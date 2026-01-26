<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TopUp;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\BalanceMutation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // <--- JANGAN LUPA IMPORT INI

class AdminTopupHistory extends Component
{
    use WithPagination;

    public function deleteTopup($id)
    {
        $topup = TopUp::find($id);

        if (!$topup)
            return;

        // Jika status bukan approved, hapus saja
        if ($topup->status != 'approved') {
            $topup->delete();
            $this->dispatch('show-success', message: 'Data sampah dihapus.');
            return;
        }

        $user = User::find($topup->user_id);

        // Cek Saldo User
        if ($user->saldo < $topup->amount) {
            $this->dispatch('show-error', message: 'GAGAL: Saldo User tidak cukup! Uang TopUp sudah terpakai.');
            return;
        }

        // Eksekusi Pembatalan
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
        // 1. Ambil Admin yang sedang login
        $currentAdmin = Auth::guard('admin')->user();

        // 2. Mulai Query Dasar
        $query = TopUp::with(['user', 'admin'])
            ->where('status', 'approved');

        // 3. LOGIKA SCOPE DATA BERDASARKAN ROLE
        // Jika role-nya 'kasir', filter hanya transaksi miliknya
        if ($currentAdmin->role === 'kasir') {
            $query->where('executor_id', $currentAdmin->id);
        }

        // Catatan: Jika role 'pusat', 'dev', atau 'keuangan',
        // kode di atas dilewati, jadi otomatis menampilkan ALL data.

        // 4. Eksekusi Query
        $history = $query->latest()->paginate(10);

        return view('Livewire.admin-topup-history', [
            'history' => $history
        ]);
    }
}