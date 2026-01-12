<?php

namespace App\Livewire;

use Livewire\WithPagination;
use Livewire\Component;
use App\Models\Store;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

class AdminMerchantDetail extends Component
{

    use WithPagination;
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

        $transactions = Transaction::with('user') // Load pembelinya siapa
            ->where('store_id', $this->store->id)
            ->latest() // Urutkan dari yang terbaru
            ->paginate(10); // 10 per halaman

        return view('Livewire.admin-merchant-detail', [
            'transactions' => $transactions
        ]);
    }
}
