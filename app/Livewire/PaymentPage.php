<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use App\Models\User;
use App\Models\Transaction;

class PaymentPage extends Component
{
    public $store;
    public $amount = '';
    public $note = '';
    public $pin = '';

    public function mount($storeId)
    {
        $this->store = Store::findOrFail($storeId);
    }

    public function processPayment()
    {
        // Validasi
        $this->validate([
            'amount' => 'required|numeric|min:500',
            'pin' => 'required',
        ]);

        $user = Auth::user();

        // 1. Cek PIN (Plain Text sesuai request kamu)
        if ($this->pin != $user->pin) {
            $this->addError('pin', 'PIN Salah!');
            return;
        }

        // 2. Cek Saldo
        if ($user->saldo < $this->amount) {
            $this->addError('amount', 'Saldo tidak cukup!');
            return;
        }

        // 3. Eksekusi Database
        DB::transaction(function () use ($user) {

            // A. Kurangi Saldo Pembeli
            $user->saldo -= $this->amount;
            $user->save();

            // B. Tambah Saldo Merchant
            $merchant = User::find($this->store->user_id);
            if ($merchant) {
                $merchant->saldo += $this->amount;
                $merchant->save();
            }

            // C. Catat Riwayat Transaksi (SESUAI KOLOM DB KAMU)
            Transaction::create([
                'transaction_code' => 'TRX-' . time() . rand(100, 999), // Pengganti reference_code
                'user_id' => $user->id,
                'total_bayar' => $this->amount,
                'status' => 'paid', // Pastikan ENUM di db ada 'paid' atau 'success'
                'tanggal_transaksi' => now(),  // Mengisi waktu saat ini
                'expired_at' => null,   // Kosongkan karena langsung lunas (pastikan kolom nullable)
            ]);
        });

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.payment-page');
    }
}