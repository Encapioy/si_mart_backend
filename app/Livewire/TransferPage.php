<?php

namespace App\Livewire;

use App\Services\NotificationService;
use Livewire\Component;
use App\Models\User;
use App\Models\Transaction;
use App\Models\BalanceMutation; // <--- WAJIB IMPORT INI
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferPage extends Component
{
    public $targetUser; // Object User Penerima
    public $amount = '';
    public $note = '';
    public $pin = '';

    // PERBAIKAN 1: Parameter diganti jadi $memberId (Sesuai hasil Scan QR)
    public function mount($memberId)
    {
        // Cari user berdasarkan member_id (String panjang tadi)
        $this->targetUser = User::where('member_id', $memberId)->first();

        // Cek User Ketemu gak?
        if (!$this->targetUser) {
            abort(404, 'User dengan Member ID ini tidak ditemukan.');
        }

        // Cek Transfer ke diri sendiri
        if ($this->targetUser->id == Auth::id()) {
            abort(403, 'Gak bisa transfer ke diri sendiri woy!');
        }
    }

    public function updatedAmount($value)
    {
        // Hapus titik setiap kali nilai berubah
        if ($value) {
            $this->amount = str_replace('.', '', $value);
        }
    }

    public function processTransfer()
    {
        // 1. CEK DUPLIKAT (MENCEGAH DOUBLE TRANSFER)
        // Cek apakah ada transaksi serupa dalam 10 detik terakhir
        $lastTrx = Transaction::where('user_id', Auth::id())
            ->where('target_user_id', $this->targetUser->id) // Cek penerima yang sama
            ->where('total_bayar', $this->amount) // Cek nominal yang sama
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();

        if ($lastTrx) {
            // Jika duplikat, langsung redirect ke halaman sukses
            return redirect()->route('payment.success', ['code' => $lastTrx->transaction_code]);
        }

        $this->amount = (int) str_replace('.', '', $this->amount);

        // 2. Validasi Input
        $this->validate([
            'amount' => 'required|numeric|min:1000',
            'pin' => 'required',
        ]);

        $sender = Auth::user();

        // 3. Validasi Logika (Self Transfer)
        if ($sender->id == $this->targetUser->id) {
            $this->addError('amount', 'Tidak bisa transfer ke diri sendiri.');
            return;
        }

        // 4. Cek PIN (Plain Text)
        if ((string) $this->pin !== (string) $sender->pin) {
            $this->addError('pin', 'PIN Salah!');
            return;
        }

        // 5. Cek Saldo Pengirim
        if ($sender->saldo < $this->amount) {
            $this->addError('amount', 'Saldo tidak cukup!');
            return;
        }

        // 6. Proses Database (Transaction)
        $transaction = DB::transaction(function () use ($sender) {

            // A. Kurangi Saldo Pengirim
            $sender->saldo -= $this->amount;
            $sender->save();

            // B. Tambah Saldo Penerima
            $this->targetUser->saldo += $this->amount;
            $this->targetUser->save();

            // C. Buat Transaksi
            $newTrx = Transaction::create([
                'transaction_code' => 'TRF-' . time() . rand(100, 999),
                'user_id' => $sender->id,
                'target_user_id' => $this->targetUser->id, // Pastikan kolom ini ada di DB
                'total_bayar' => $this->amount,
                'status' => 'paid',
                'tanggal_transaksi' => now(),
                'type' => 'transfer',
                'description' => 'Transfer ke ' . $this->targetUser->nama_lengkap . ($this->note ? " ({$this->note})" : ''),
                'store_id' => null,
            ]);

            // D. Mutasi Pengirim (Debit - Uang Keluar)
            BalanceMutation::create([
                'user_id' => $sender->id,
                'type' => 'debit',
                'amount' => $this->amount,
                'current_balance' => $sender->saldo,
                'category' => 'transfer_out',
                'description' => 'Transfer ke ' . $this->targetUser->nama_lengkap,
                'related_user_id' => $this->targetUser->id
            ]);

            // E. Mutasi Penerima (Kredit - Uang Masuk)
            BalanceMutation::create([
                'user_id' => $this->targetUser->id,
                'type' => 'credit',
                'amount' => $this->amount,
                'current_balance' => $this->targetUser->saldo,
                'category' => 'transfer_in',
                'description' => 'Terima saldo dari ' . $sender->nama_lengkap,
                'related_user_id' => $sender->id
            ]);

            return $newTrx;
        });

        // 7. KIRIM NOTIFIKASI
        if ($transaction) {
            $formattedAmount = number_format($this->amount, 0, ',', '.');

            // A. Notifikasi ke PENGIRIM
            NotificationService::send(
                $sender->id,
                'Transfer Berhasil',
                "Anda berhasil transfer Rp $formattedAmount ke {$this->targetUser->nama_lengkap}.",
                'transaction',
                [
                    'trx_code' => $transaction->transaction_code,
                    'amount' => $this->amount,
                    'action' => 'transfer_out'
                ]
            );

            // B. Notifikasi ke PENERIMA
            NotificationService::send(
                $this->targetUser->id,
                'Dana Masuk',
                "Anda menerima Rp $formattedAmount dari {$sender->nama_lengkap}.",
                'transaction',
                [
                    'trx_code' => $transaction->transaction_code,
                    'amount' => $this->amount,
                    'action' => 'transfer_in'
                ]
            );
        }

        // Feedback Sukses (Opsional karena sudah redirect)
        session()->flash('success', 'Transfer Berhasil!');

        // 8. Redirect ke Halaman Sukses
        return redirect()->route('payment.success', ['code' => $transaction->transaction_code]);
    }

    public function render()
    {
        return view('Livewire.transfer-page');
    }
}