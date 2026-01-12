<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Store;
use App\Models\BalanceMutation;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

class AdminMerchantDetail extends Component
{
    public $store;
    public $incomeToday = 0;
    public $incomeMonth = 0;
    public $incomeTotal = 0; // Added Total All-time Income

    public function mount($storeId)
    {
        // 1. Get Store Data with Relations
        $this->store = Store::with(['owner', 'products'])->findOrFail($storeId);

        // 2. Base Query for Income (Mutations where money comes IN from payments)
        $trxQuery = Transaction::where('store_id', $this->store->id)
            ->where('status', 'paid');

        // 3. Calculate Stats
        $this->incomeToday = (clone$trxQuery)
            ->whereDate('created_at', Carbon::today())
            ->sum('total_bayar');

        $this->incomeMonth = (clone$trxQuery)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_bayar');

        $this->incomeTotal = (clone$trxQuery)->sum('total_bayar');
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        return view('livewire.admin-merchant-detail');
    }
}
