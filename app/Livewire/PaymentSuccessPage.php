<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

class PaymentSuccessPage extends Component
{
    public $transaction;

    public function mount($code)
    {
        // 1. Cari Transaksi berdasarkan Kode
        // 2. Pastikan transaksi itu milik user yang sedang login (Security)
        $this->transaction = Transaction::where('transaction_code', $code)
            ->where('user_id', Auth::id())
            ->with('store') // Load data toko
            ->firstOrFail(); // Kalau ga ketemu, otomatis 404
    }

    public function render()
    {
        return view('Livewire.payment-success-page');
    }
}