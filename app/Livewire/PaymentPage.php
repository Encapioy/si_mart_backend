<?php

namespace App\Livewire;

use App\Services\NotificationService;
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

    public function updatedAmount($value)
    {
        // Pastikan amount selalu bersih dari titik jika user copy-paste
        if ($value) {
            $this->amount = str_replace('.', '', $value);
        }
    }

    public function processPayment()
    {
        // 1. CEK DUPLIKAT (PENTING UNTUK MENCEGAH DOUBLE PAYMENT)
        // Cek apakah user ini baru saja membayar ke toko yang sama dengan nominal sama dalam 10 detik terakhir
        $lastTrx = Transaction::where('user_id', Auth::id())
            ->where('store_id', $this->store->id)
            ->where('total_bayar', $this->amount)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();

        if ($lastTrx) {
            // Jika terdeteksi duplikat, langsung lempar ke halaman sukses transaksi sebelumnya
            return redirect()->route('payment.success', ['code' => $lastTrx->transaction_code]);
        }

        $this->amount = str_replace('.', '', $this->amount);

        // 2. Validasi Input
        $this->validate([
            'amount' => 'required|numeric|min:1000',
            'pin' => 'required',
        ]);

        $user = Auth::user();

        // Fraud Check
        if ($this->store->user_id == $user->id) {
            $this->addError('amount', 'Fraud detected: Tidak bisa bayar ke toko sendiri.');
            return;
        }

        // 3. Cek PIN (Plain Text sesuai request)
        if ((string) $this->pin !== (string) $user->pin) {
            $this->addError('pin', 'PIN Salah!');
            $this->dispatch('validation-failed');
            return;
        }

        // 4. Cek Saldo
        if ($user->saldo < $this->amount) {
            $this->addError('amount', 'Saldo tidak cukup!');
            return;
        }

        // 5. Eksekusi Database (Transaction)
        // Kita tampung hasilnya di variabel $transaction
        $transaction = DB::transaction(function () use ($user) {

            // A. Kurangi Saldo Pembeli
            $user->saldo -= $this->amount;
            $user->save();

            // B. Tambah Saldo Merchant
            $merchant = User::find($this->store->user_id);
            if ($merchant) {
                $merchant->merchant_balance += $this->amount; // Asumsi ada kolom ini
                $merchant->save();
            }

            // C. Catat Transaksi
            $newTrx = Transaction::create([
                'transaction_code' => 'TRX-' . time() . rand(100, 999),
                'user_id' => $user->id,
                'total_bayar' => $this->amount,
                'status' => 'paid',
                'tanggal_transaksi' => now(),
                'type' => 'payment',
                'description' => 'Pembayaran ke ' . $this->store->nama_toko . ($this->note ? " ({$this->note})" : ''),
                'store_id' => $this->store->id,
            ]);

            // D. Catat Mutasi Pembeli (Uang Keluar)
            BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $this->amount,
                'current_balance' => $user->saldo,
                'category' => 'payment',
                'description' => 'Pembayaran ke ' . $this->store->nama_toko,
                'related_user_id' => $merchant ? $merchant->id : null
            ]);

            // E. Catat Mutasi Penjual (Uang Masuk)
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

            return $newTrx;
        });

        // 6. KIRIM NOTIFIKASI (Jika Transaksi Sukses)
        if ($transaction) {
            $formattedAmount = number_format($this->amount, 0, ',', '.');

            // A. Notif ke PEMBELI
            NotificationService::send(
                $user->id,
                'Pembayaran Berhasil',
                "Pembayaran ke {$this->store->nama_toko} sebesar Rp $formattedAmount berhasil.",
                'transaction',
                [
                    'trx_code' => $transaction->transaction_code,
                    'amount' => $this->amount
                ]
            );

            // B. Notif ke MERCHANT (Penjual)
            $merchant = User::find($this->store->user_id);
            if ($merchant) {
                NotificationService::send(
                    $merchant->id,
                    'Pembayaran Diterima',
                    "Diterima Rp $formattedAmount dari {$user->nama_lengkap} di {$this->store->nama_toko}.",
                    'transaction',
                    [
                        'trx_code' => $transaction->transaction_code,
                        'amount' => $this->amount
                    ]
                );
            }
        }

        // 7. Redirect ke Halaman Sukses
        return redirect()->route('payment.success', ['code' => $transaction->transaction_code]);
    }

    public function render()
    {
        return view('Livewire.payment-page');
    }
}