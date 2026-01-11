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
        DB::transaction(function () use ($sender) {

            // A. Kurangi Saldo Pengirim
            $sender->saldo -= $this->amount;
            $sender->save();

            // B. Tambah Saldo Penerima
            $this->targetUser->saldo += $this->amount;
            $this->targetUser->save();

            // C. Catat Transaksi (Laporan Admin)
            Transaction::create([
                'transaction_code' => 'TRF-' . time() . rand(100, 999),
                'user_id' => $sender->id, // Transaksi milik pengirim
                'total_bayar' => $this->amount,
                'status' => 'paid',
                'tanggal_transaksi' => now(),
                'expired_at' => null,

                // --- UPDATE LOGIKA BARU ---
                'type' => 'transfer', // Tipe Transfer
                'description' => 'Transfer ke ' . $this->targetUser->nama_lengkap . ($this->note ? " ({$this->note})" : ''),
            ]);

            // D. Catat Mutasi Pengirim (Uang Keluar / Debit)
            BalanceMutation::create([
                'user_id' => $sender->id,
                'type' => 'debit',
                'amount' => $this->amount,
                'current_balance' => $sender->saldo,
                'category' => 'transfer_out', // Kategori khusus transfer keluar
                'description' => 'Transfer ke ' . $this->targetUser->nama_lengkap,
                'related_user_id' => $this->targetUser->id
            ]);

            // E. Catat Mutasi Penerima (Uang Masuk / Kredit)
            BalanceMutation::create([
                'user_id' => $this->targetUser->id,
                'type' => 'credit',
                'amount' => $this->amount,
                'current_balance' => $this->targetUser->saldo,
                'category' => 'transfer_in', // Kategori khusus terima transfer
                'description' => 'Terima saldo dari ' . $sender->nama_lengkap,
                'related_user_id' => $sender->id
            ]);
        });

        // Feedback Sukses & Redirect
        session()->flash('success', 'Transfer Berhasil ke ' . $this->targetUser->nama_lengkap);

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.transfer-page');
    }
}