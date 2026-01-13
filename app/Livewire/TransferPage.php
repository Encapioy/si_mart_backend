<?php

namespace App\Livewire;

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

    public function processTransfer()
    {
        // 1. Validasi Input
        $this->validate([
            'amount' => 'required|numeric|min:1000',
            'pin' => 'required',
        ]);

        $sender = Auth::user();

        // 2. Cek PIN (Plain Text)
        if ($this->pin != $sender->pin) {
            $this->addError('pin', 'PIN Salah!');
            return;
        }

        // 3. Cek Saldo Pengirim
        if ($sender->saldo < $this->amount) {
            $this->addError('amount', 'Saldo gak cukup bos!');
            return;
        }

        // 4. Proses Database (Pakai Transaction)
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
                'target_user_id' => $this->targetUser->id,
                'total_bayar' => $this->amount,
                'status' => 'paid', // Transfer dianggap langsung sukses/paid
                'tanggal_transaksi' => now(),
                'expired_at' => null,
                'type' => 'transfer',
                'description' => 'Transfer ke ' . $this->targetUser->nama_lengkap . ($this->note ? " ({$this->note})" : ''),
                'store_id' => null, // Pastikan nullable di database jika bukan transaksi toko
            ]);

            // D. Mutasi Pengirim (Debit)
            BalanceMutation::create([
                'user_id' => $sender->id,
                'type' => 'debit',
                'amount' => $this->amount,
                'current_balance' => $sender->saldo,
                'category' => 'transfer_out',
                'description' => 'Transfer ke ' . $this->targetUser->nama_lengkap,
                'related_user_id' => $this->targetUser->id
            ]);

            // E. Mutasi Penerima (Kredit)
            BalanceMutation::create([
                'user_id' => $this->targetUser->id,
                'type' => 'credit',
                'amount' => $this->amount,
                'current_balance' => $this->targetUser->saldo,
                'category' => 'transfer_in',
                'description' => 'Terima saldo dari ' . $sender->nama_lengkap,
                'related_user_id' => $sender->id
            ]);

            // PENTING: Return objek transaksi agar keluar dari fungsi closure ini
            return $newTrx;
        });

        // Feedback Sukses
        session()->flash('success', 'Transfer Berhasil!');

        // 5. Redirect (Sekarang variabel $transaction sudah dikenali)
        // Pastikan parameter 'code' mengambil dari 'transaction_code'
        return redirect()->route('payment.success', ['code' => $transaction->transaction_code]);
    }

    public function render()
    {
        return view('Livewire.transfer-page');
    }
}