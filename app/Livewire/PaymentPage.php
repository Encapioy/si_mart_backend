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
    public $amount = ''; // String biar bisa dihapus/edit
    public $note = '';
    public $pin = '';

    public function mount($storeId)
    {
        $this->store = Store::findOrFail($storeId);
    }

    public function processPayment()
    {
        $this->validate([
            'amount' => 'required|numeric|min:500',
            'pin' => 'required',
        ]);

        $user = Auth::user();

        // 1. Cek PIN
        if ($this->pin != $user->pin) {
            $this->addError('pin', 'PIN Salah!');
            return;
        }

        // 2. Cek Saldo
        if ($user->saldo < $this->amount) {
            $this->addError('amount', 'Saldo tidak cukup!');
            return;
        }

        // 3. Eksekusi
        DB::transaction(function () use ($user) {
            // Kurangi Saldo User
            $user->saldo -= $this->amount;
            $user->save();

            // Tambah Saldo Merchant
            $merchant = User::find($this->store->user_id);
            $merchant->saldo += $this->amount;
            $merchant->save();

            // Catat
            Transaction::create([
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'store_id' => $this->store->id,
                'amount' => $this->amount,
                'type' => 'payment',
                'status' => 'paid',
                'description' => $this->note ?? 'Pembayaran Web',
                'reference_code' => 'WEB-' . time(),
            ]);
        });

        // Balik ke dashboard (atau halaman sukses)
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.payment-page');
    }
}