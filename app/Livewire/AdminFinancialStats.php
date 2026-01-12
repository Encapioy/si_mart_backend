<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Livewire\Attributes\Layout;

class AdminFinancialStats extends Component
{
    #[Layout('components.layouts.admin')]
    public function render()
    {
        // 1. Hitung Total Uang
        $totalUserSaldo = User::sum('saldo');
        $totalMerchantBalance = User::sum('merchant_balance');
        $totalUangBeredar = $totalUserSaldo + $totalMerchantBalance;

        // 2. Hitung Persentase (Handle division by zero)
        $persenUser = $totalUangBeredar > 0 ? ($totalUserSaldo / $totalUangBeredar) * 100 : 0;
        $persenMerchant = $totalUangBeredar > 0 ? ($totalMerchantBalance / $totalUangBeredar) * 100 : 0;

        // 3. Top 5 User Sultan (Berdasarkan Saldo Pribadi)
        $topUsers = User::orderByDesc('saldo')
            ->take(5)
            ->get(['nama_lengkap', 'username', 'saldo']);

        // 4. Top 5 Merchant Laris (Berdasarkan Merchant Balance)
        // Kita filter yang balance-nya > 0 saja biar rapi
        $topMerchants = User::where('merchant_balance', '>', 0)
            ->orderByDesc('merchant_balance')
            ->take(5)
            ->get(['nama_lengkap', 'username', 'merchant_balance']);

        return view('livewire.admin-financial-stats', [
            'totalUangBeredar' => $totalUangBeredar,
            'totalUserSaldo' => $totalUserSaldo,
            'totalMerchantBalance' => $totalMerchantBalance,
            'persenUser' => round($persenUser, 1),
            'persenMerchant' => round($persenMerchant, 1),
            'topUsers' => $topUsers,
            'topMerchants' => $topMerchants,
        ]);
    }
}