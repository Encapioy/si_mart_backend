<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Admin; // Make sure Admin model is imported
use App\Models\TopUp;
use App\Models\BalanceMutation;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

class AdminTopup extends Component
{
    // --- PROPERTIES UNTUK FORM ---
    public $search = '';         // Input (Username/Email/HP/Member ID)
    public $amount = '';         // Input Nominal
    public $cashier_id = '';     // Dropdown Kasir
    public $cashier_pin = '';    // Input PIN

    // --- DATA ---
    public $usersFound = [];     // Hasil Autocomplete
    public $selectedUser = null; // User yang dipilih dari autocomplete
    public $cashiers = [];       // List Kasir untuk dropdown

    // --- STATISTIK (Kanan Layar) ---
    public $totalUangBeredar = 0;

    public function mount()
    {
        // 1. Ambil daftar Kasir dari tabel ADMIN (Bukan User)
        // Pastikan di tabel admins ada kolom 'role' atau sesuaikan query ini
        $this->cashiers = Admin::where('role', 'kasir')->get();

        // 2. Hitung statistik real
        $this->totalUangBeredar = User::sum('saldo');
    }

    // --- FITUR 1: AUTOCOMPLETE (SMART SEARCH) ---
    public function updatedSearch()
    {
        if (strlen($this->search) > 0) {
            // PERBAIKAN: Cari berdasarkan Username, Nama, Email, No HP, atau Member ID
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

        // Reset selected user kalau admin mengetik ulang pencarian
        $this->selectedUser = null;
    }

    public function selectUser($id)
    {
        $this->selectedUser = User::find($id);
        // Tampilkan username atau nama di input setelah dipilih
        $this->search = $this->selectedUser->username;
        $this->usersFound = [];
    }

    // --- FITUR 2: QUICK AMOUNT ---
    public function setAmount($val)
    {
        $this->amount = $val;
    }

    // --- FITUR 3: VALIDASI & KONFIRMASI (Tahap 1) ---
    public function triggerConfirm()
    {
        // 1. Validasi Input Dasar
        $this->validate([
            'search' => 'required',
            'amount' => 'required|numeric|min:100000',
            'cashier_id' => 'required',
            'cashier_pin' => 'required|digits:6',
        ], [
            'amount.min' => 'Minimal Top Up Rp 100.000',
            'cashier_pin.digits' => 'PIN harus 6 digit'
        ]);

        // 2. Pastikan User Tujuan Valid
        if (!$this->selectedUser) {
            // Coba cari exact match jika user tidak klik dropdown
            $this->selectedUser = User::where('username', $this->search)
                ->orWhere('email', $this->search)
                ->orWhere('no_hp', $this->search)
                ->orWhere('member_id', $this->search)
                ->first();

            if (!$this->selectedUser) {
                $this->dispatch('show-error', message: 'User tidak ditemukan! Pastikan Username/Email/HP benar.');
                return;
            }
        }

        // 3. Ambil Nama Kasir dari Model ADMIN (Fix Error "on null")
        $kasir = Admin::find($this->cashier_id);

        if (!$kasir) {
            $this->dispatch('show-error', message: 'Data Kasir tidak valid!');
            return;
        }

        // 4. Kirim Event ke Browser
        $this->dispatch(
            'show-confirmation-modal',
            username: $this->selectedUser->username,
            amount: $this->amount,
            cashier_name: $kasir->nama_lengkap
        );
    }

    // --- FITUR 4: EKSEKUSI TRANSAKSI (Tahap 2) ---
    public function processTopUp()
    {
        if (!$this->selectedUser)
            return;

        // Cek Kasir & PIN (Pakai Model Admin)
        $kasir = Admin::find($this->cashier_id);

        if (!$kasir || $kasir->pin != $this->cashier_pin) {
            $this->dispatch('show-error', message: 'PIN Kasir Salah!');
            return;
        }

        DB::beginTransaction();
        try {
            // A. Update Saldo User
            $this->selectedUser->saldo += $this->amount;
            $this->selectedUser->save();

            // B. Simpan TopUp
            TopUp::create([
                'user_id' => $this->selectedUser->id,
                'amount' => $this->amount,
                'status' => 'approved',
                'admin_id' => $kasir->id,
                'bukti_transfer' => 'MANUAL_CASH',
            ]);

            // D. Simpan Mutasi (Buku Tabungan)
            BalanceMutation::create([
                'user_id' => $this->selectedUser->id,
                'type' => 'credit',
                'amount' => $this->amount,
                'current_balance' => $this->selectedUser->saldo,
                'category' => 'topup',
                'description' => 'Setoran Tunai via Kasir',
                'related_user_id' => $kasir->id // ID dari tabel Admin
            ]);

            DB::commit();

            // Reset & Response
            $targetName = $this->selectedUser->nama_lengkap;
            $this->reset(['search', 'amount', 'cashier_pin', 'selectedUser', 'usersFound']);
            $this->totalUangBeredar = User::sum('saldo'); // Update Statistik

            $this->dispatch('show-success', message: "Saldo berhasil dikirim ke $targetName");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-error', message: 'Gagal: ' . $e->getMessage());
        }
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        return view('Livewire.admin-topup');
    }
}
