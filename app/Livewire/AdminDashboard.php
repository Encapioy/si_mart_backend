<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TopUp; // Jangan lupa import ini

class AdminDashboard extends Component
{
    public function render()
    {
        return view('livewire.admin-dashboard', [
            // 1. Uang Beredar (Hanya User & Merchant, Admin jangan dihitung)
            'uang_beredar' => User::sum('saldo'),

            // 2. Jumlah Topup (Ambil dari tabel TopUp yang sukses)
            'jumlah_topup' => TopUp::where('status', 'approved')->count(),

            // 3. Jumlah Transaksi (Payment + Transfer dari tabel Transaction)
            'jumlah_transaksi' => Transaction::whereIn('type', ['payment', 'transfer'])->count(),

            // 4. Jumlah Merchant
            'jumlah_merchant' => Store::count(),

            // 5. Jumlah User (Hanya User biasa)
            'jumlah_user' => User::count(),

            // BONUS: Ambil 5 Transaksi Terakhir untuk tabel mini
            'recent_transactions' => Transaction::latest()->take(5)->get()
        ])->layout('components.layouts.sidebar');
    }
}