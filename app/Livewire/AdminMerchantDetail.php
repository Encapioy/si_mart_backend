<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Store;
use App\Models\BalanceMutation; // We use this for accurate income calculation
use Carbon\Carbon;

class AdminMerchantDetail extends Component
{
    public $store;
    public $incomeToday = 0;
    public $incomeMonth = 0;
    public $incomeTotal = 0; // Added Total All-time Income

    public function mount($storeId)
    {
        // 1. Get Store Data with Relations
        $this->store = Store::with(['user', 'products'])->findOrFail($storeId);

        // Merchant's User ID (The owner of the wallet)
        $merchantUserId = $this->store->user_id;

        // 2. Base Query for Income (Mutations where money comes IN from payments)
        $incomeQuery = BalanceMutation::where('user_id', $merchantUserId)
            ->where('type', 'credit')       // Money IN
            ->where('category', 'payment'); // From Store Payments

        // 3. Calculate Stats
        $this->incomeToday = (clone $incomeQuery)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        $this->incomeMonth = (clone $incomeQuery)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        $this->incomeTotal = (clone $incomeQuery)->sum('amount');
    }

    public function render()
    {
        return view('livewire.admin-merchant-detail')
            ->layout('components.layouts.sidebar', ['title' => 'Detail Merchant']);
    }
}