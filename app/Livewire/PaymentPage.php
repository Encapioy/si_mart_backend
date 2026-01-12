<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use App\Models\User;
use App\Models\Transaction;
use App\Models\BalanceMutation; // <--- JANGAN LUPA IMPORT INI

class PaymentPage extends Component
{
    public $store;
    public $amount = '';
    public $note = ''; // Catatan opsional
    public $pin = '';

    public function mount($storeId)
    {
        $this->store = Store::findOrFail($storeId);

        // Jika ID User yang login == ID Pemilik Toko
        if ($this->store->user_id == Auth::id()) {
            // Redirect balik ke dashboard dengan pesan error
            session()->flash('error', 'Anda tidak bisa melakukan pembayaran ke toko sendiri!');
            return redirect()->route('dashboard');
        }
    }

    public function processPayment()
    {
        // 1. Validasi
        $this->validate([
            'amount' => 'required|numeric|min:500',
            'pin' => 'required',
        ]);

        $user = Auth::user();

        if ($this->store->user_id == $user->id) {
            $this->addError('amount', 'Fraud detected: Tidak bisa bayar ke toko sendiri.');
            return;
        }

        // 2. Cek PIN (Plain Text)
        if ($this->pin != $user->pin) {
            $this->addError('pin', 'PIN Salah!');
            return;
        }

        // 3. Cek Saldo
        if ($user->saldo < $this->amount) {
            $this->addError('amount', 'Saldo tidak cukup!');
            return;
        }

        // 4. Eksekusi Database (Pakai Transaction biar aman)
        $transaction = DB::transaction(function () use ($user) {

            // A. Kurangi Saldo Pembeli
            $user->saldo -= $this->amount;
            $user->save();

            // B. Tambah Saldo Merchant
            $merchant = User::find($this->store->user_id);
            if ($merchant) {
                $merchant->merchant_balance += $this->amount;
                $merchant->save();
            }

            // C. Catat Struk
            // PERBAIKAN: Return object transaksi ini agar bisa dipakai di luar
            $newTrx = Transaction::create([
                'transaction_code' => 'TRX-' . time() . rand(100, 999),
                'user_id' => $user->id,
                'total_bayar' => $this->amount,
                'status' => 'paid',
                'tanggal_transaksi' => now(),
                'expired_at' => null,
                'type' => 'payment',
                'description' => 'Pembayaran ke ' . $this->store->nama_toko . ($this->note ? " ({$this->note})" : ''),
                'store_id' => $this->store->id, // PERBAIKAN: Wajib diisi agar muncul di history toko
            ]);

            // D. Catat Mutasi Pembeli
            BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $this->amount,
                'current_balance' => $user->saldo,
                'category' => 'payment',
                'description' => 'Pembayaran ke ' . $this->store->nama_toko,
                'related_user_id' => $merchant ? $merchant->id : null
            ]);

            // E. Catat Mutasi Penjual
            if ($merchant) {
                BalanceMutation::create([
                    'user_id' => $merchant->id,
                    'type' => 'credit',
                    'amount' => $this->amount,
                    'current_balance' => $merchant->merchant_balance,
                    'category' => 'payment',
                    'description' => 'Terima pembayaran dari ' . $user->nama_lengkap,
                    'related_user_id' => $user->id
                ]);
            }

            // PENTING: Return objek agar keluar dari scope transaction
            return $newTrx;
        });

        // Feedback Sukses
        session()->flash('success', 'Pembayaran Berhasil sebesar Rp ' . number_format($this->amount));

        // PERBAIKAN 2: Masukkan parameter code untuk redirect
        // Asumsi nama kolom di DB adalah 'transaction_code'
        return redirect()->route('payment.success', ['code' => $transaction->transaction_code]);
    }

    public function render()
    {
        return view('Livewire.payment-page');
    }
}