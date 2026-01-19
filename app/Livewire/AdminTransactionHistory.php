<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\BalanceMutation;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class AdminTransactionHistory extends Component
{
    use WithPagination;

    public $search = ''; // Fitur Pencarian

    // Reset pagination saat search berubah
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function deleteTransaction($id)
    {
        $trx = Transaction::find($id);

        if (!$trx)
            return;

        // 1. Cek Status Lunas
        if ($trx->status != 'paid') {
            $trx->delete();
            $this->dispatch('show-success', message: 'Data riwayat dihapus (Status belum lunas).');
            return;
        }

        // 2. Ambil Pihak Terkait
        // user_id        = Pengirim / Pembeli
        // target_user_id = Penerima / Penjual
        $sender = User::find($trx->user_id);
        $receiver = User::find($trx->target_user_id);

        if (!$sender || !$receiver) {
            $this->dispatch('show-error', message: 'User pengirim atau penerima tidak ditemukan.');
            return;
        }

        // 3. LOGIKA PERCABANGAN (SWITCH LOGIC)
        // Kita cek berdasarkan kolom 'type' di database
        $isP2P = ($trx->type === 'transfer');

        // --- TAHAP VALIDASI SALDO ---
        if ($isP2P) {
            // KASUS P2P: Cek Saldo Utama Penerima
            // Apakah teman yang dikirimi uang sudah menghabiskannya?
            if ($receiver->saldo < $trx->total_bayar) {
                $this->dispatch('show-error', message: "GAGAL: Saldo Penerima ({$receiver->nama_lengkap}) tidak cukup untuk ditarik kembali!");
                return;
            }
        } else {
            // KASUS MERCHANT: Cek Merchant Balance
            // Apakah toko sudah mencairkan uangnya?
            if ($receiver->merchant_balance < $trx->total_bayar) {
                $this->dispatch('show-error', message: "GAGAL: Merchant Balance toko tidak cukup untuk refund!");
                return;
            }
        }

        // 4. EKSEKUSI DATABASE
        DB::transaction(function () use ($trx, $sender, $receiver, $isP2P) {

            // A. KEMBALIKAN UANG KE PENGIRIM/PEMBELI (Sama untuk kedua kasus)
            $sender->saldo += $trx->total_bayar;
            $sender->save();

            // Mutasi Pengirim (Uang Balik)
            BalanceMutation::create([
                'user_id' => $sender->id,
                'type' => 'credit',
                'amount' => $trx->total_bayar,
                'current_balance' => $sender->saldo,
                'category' => 'correction',
                'description' => 'REFUND: Batal ' . ($isP2P ? 'Transfer' : 'Transaksi') . ' ' . $trx->transaction_code,
                'related_user_id' => auth()->id()
            ]);

            // B. TARIK UANG DARI PENERIMA (Beda Dompet)
            if ($isP2P) {
                // --- LOGIKA P2P (Tarik Saldo Utama) ---
                $receiver->saldo -= $trx->total_bayar;
                $receiver->save();
                $currentBalanceReceiver = $receiver->saldo;
                $descReceiver = 'KOREKSI: Batal Terima Transfer ' . $trx->transaction_code;
            } else {
                // --- LOGIKA MERCHANT (Tarik Merchant Balance) ---
                $receiver->merchant_balance -= $trx->total_bayar;
                $receiver->save();
                $currentBalanceReceiver = $receiver->merchant_balance;
                $descReceiver = 'KOREKSI: Batal Penjualan ' . $trx->transaction_code;
            }

            // Mutasi Penerima (Uang Ditarik)
            BalanceMutation::create([
                'user_id' => $receiver->id,
                'type' => 'debit',
                'amount' => $trx->total_bayar,
                'current_balance' => $currentBalanceReceiver, // Saldo sesuai jenis dompet
                'category' => 'correction',
                'description' => $descReceiver,
                'related_user_id' => auth()->id()
            ]);

            // C. NOTIFIKASI
            $judul = $isP2P ? 'Transfer Dibatalkan' : 'Transaksi Dibatalkan';

            // Notif ke Pengirim
            NotificationService::send(
                $sender->id,
                'Dana Dikembalikan',
                "$judul oleh Admin. Saldo Rp " . number_format($trx->total_bayar) . " telah dikembalikan.",
                'system'
            );

            // Notif ke Penerima
            NotificationService::send(
                $receiver->id,
                'Saldo Disesuaikan',
                "$judul oleh Admin. Dana senilai Rp " . number_format($trx->total_bayar) . " ditarik kembali.",
                'system'
            );

            // D. Hapus Data
            $trx->delete();
        });

        $this->dispatch('show-success', message: 'Sukses membatalkan transaksi & menyesuaikan saldo kedua pihak.');
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        $transactions = Transaction::with(['user', 'store.owner'])
            ->whereIn('type', ['payment', 'transfer_in', 'transfer_out', 'transfer'])
            ->when($this->search, function ($query) {
                // Logic Pencarian Pintar
                $query->where(function ($q) {
                    $q->where('transaction_code', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($u) {
                            $u->where('nama_lengkap', 'like', '%' . $this->search . '%')
                                ->orWhere('username', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('store', function ($s) {
                            $s->where('nama_toko', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(10);

        return view('Livewire.admin-transaction-history', [
            'transactions' => $transactions
        ]);
    }
}