<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Admin;
use App\Models\TopUp;
use App\Models\BalanceMutation;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

class AdminTopup extends Component
{
    // --- PROPERTIES FORM ---
    public $search = '';
    public $amount = '';        // Akan otomatis di-sanitize dari titik
    public $cashier_id = '';
    public $cashier_pin = '';

    // --- DATA ---
    public $usersFound = [];
    public $selectedUserId = null; // Kita simpan ID-nya saja biar lebih ringan
    public $selectedUserName = ''; // Untuk display di input search
    public $cashiers = [];

    // --- STATISTIK ---
    public $totalUangBeredar = 0;

    public function mount()
    {
        // Ambil kasir (pastikan role sesuai database kamu)
        $this->cashiers = Admin::where('role', 'kasir')->get();
        $this->totalUangBeredar = User::sum('saldo');
    }

    // --- FITUR 2: AUTOCOMPLETE ---
    public function updatedSearch()
    {
        // Jika user mengetik ulang, reset pilihan sebelumnya
        $this->selectedUserId = null;

        if (strlen($this->search) > 0) {
            $this->usersFound = User::where(function ($q) {
                $q->where('username', 'like', '%' . $this->search . '%')
                    ->orWhere('nama_lengkap', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('no_hp', 'like', '%' . $this->search . '%')
                    ->orWhere('member_id', 'like', '%' . $this->search . '%');
            })
                ->limit(5)
                ->get();
        } else {
            $this->usersFound = [];
        }
    }

    public function selectUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $this->selectedUserId = $user->id;
            $this->selectedUserName = $user->nama_lengkap;
            $this->search = $user->username; // Tampilkan username di input
            $this->usersFound = []; // Tutup dropdown
        }
    }

    // --- FITUR 3: CEK PIN & TRIGGER MODAL ---
    public function triggerConfirm()
    {
        // 1. Validasi Input
        $this->validate([
            'search' => 'required',
            'amount' => 'required|numeric|min:1000',
            'cashier_id' => 'required',
            'cashier_pin' => 'required|digits:6',
        ], [
            'amount.min' => 'Minimal Top Up Rp 1.000',
            'cashier_pin.digits' => 'PIN harus 6 digit angka',
            'cashier_id.required' => 'Pilih kasir terlebih dahulu'
        ]);

        // 2. Pastikan User Dipilih/Valid
        if (!$this->selectedUserId) {
            // Coba cari exact match jika user lupa klik dropdown
            $user = User::where('username', $this->search)->first();
            if ($user) {
                $this->selectedUserId = $user->id;
                $this->selectedUserName = $user->nama_lengkap;
            } else {
                $this->dispatch('show-error', message: 'User tidak ditemukan! Silakan pilih dari list.');
                return;
            }
        }

        // 3. [PENTING] CEK PIN SEBELUM MODAL MUNCUL
        $kasir = Admin::find($this->cashier_id);

        // Cek 1: Kasir ada?
        if (!$kasir) {
            $this->dispatch('show-error', message: 'Data Kasir error.');
            return;
        }

        // Cek 2: PIN Benar?
        // Pastikan tipe data sama-sama string agar aman
        if ((string) $kasir->pin !== (string) $this->cashier_pin) {
            // Kirim error spesifik ke input PIN agar tulisan merah muncul
            $this->addError('cashier_pin', 'PIN Salah! Cek kembali.');
            return; // STOP DISINI. Modal tidak akan muncul.
        }

        // 4. Jika PIN Benar, Baru Munculkan Modal
        $this->dispatch(
            'show-confirmation-modal',
            username: $this->selectedUserName,
            amount: $this->amount, // Kirim angka murni (tanpa titik) ke JS
            cashier_name: $kasir->nama_lengkap // Optional
        );
    }

    // --- FITUR 4: EKSEKUSI TRANSAKSI ---
    public function processTopUp()
    {
        // Double Check User
        if (!$this->selectedUserId)
            return;
        $user = User::find($this->selectedUserId);
        if (!$user)
            return;

        // Double Check Kasir (Keamanan Ganda)
        $kasir = Admin::find($this->cashier_id);
        if (!$kasir || (string) $kasir->pin !== (string) $this->cashier_pin) {
            $this->dispatch('show-error', message: 'Verifikasi Gagal.');
            return;
        }

        DB::beginTransaction();
        try {
            // A. Update Saldo User
            $user->saldo += $this->amount;
            $user->save();

            // B. Simpan TopUp History
            TopUp::create([
                'user_id' => $user->id,
                'amount' => $this->amount,
                'status' => 'approved',
                'admin_id' => $kasir->id,
                'bukti_transfer' => 'MANUAL_CASH',
            ]);

            // C. Simpan Mutasi (Buku Tabungan)
            BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $this->amount,
                'current_balance' => $user->saldo,
                'category' => 'topup',
                'description' => 'Setoran Tunai via Kasir ' . explode(' ', $kasir->username)[0],
                'related_user_id' => $kasir->id
            ]);

            DB::commit();

            // D. Sukses & Reset
            $targetName = $user->nama_lengkap;

            // Reset Form tapi biarkan Kasir terpilih (biar gak capek milih lagi kalau kasirnya sama)
            $this->reset(['search', 'amount', 'cashier_pin', 'selectedUserId', 'usersFound', 'selectedUserName']);

            // Update Statistik
            $this->totalUangBeredar = User::sum('saldo');

            $this->dispatch('show-success', message: "Saldo masuk ke: $targetName");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-error', message: 'Gagal: ' . $e->getMessage());
        }
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        return view('livewire.admin-topup');
    }
}