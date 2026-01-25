<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Admin;
use App\Models\TopUp;
use App\Models\BalanceMutation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Tambahkan ini
use Livewire\Attributes\Layout;
use App\Services\NotificationService;

class AdminTopup extends Component
{
    // --- PROPERTIES FORM ---
    public $search = '';
    public $amount = '';        // Akan otomatis di-sanitize dari titik
    public $cashier_id = '';    // [UBAH] Akan diisi otomatis oleh system
    public $cashier_pin = '';

    // --- DATA ---
    public $usersFound = [];
    public $selectedUserId = null;
    public $selectedUserName = '';
    // public $cashiers = []; // [HAPUS] Tidak perlu list kasir lagi

    // --- STATISTIK ---
    public $totalUangBeredar = 0;

    public function mount()
    {
        // [UBAH LOGIKA] Tidak perlu ambil list kasir.
        // Langsung ambil ID admin yang sedang login.
        $this->cashier_id = Auth::guard('admin')->id();

        $this->totalUangBeredar = User::sum('saldo');
    }

    // --- FITUR 2: AUTOCOMPLETE (TETAP SAMA) ---
    public function updatedSearch()
    {
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
            $this->search = $user->username;
            $this->usersFound = [];
        }
    }

    // --- FITUR 3: CEK PIN & TRIGGER MODAL ---
    public function triggerConfirm()
    {
        // 1. Validasi Input
        $this->validate([
            'search' => 'required',
            'amount' => 'required|numeric|min:1000',
            // 'cashier_id' => 'required', // [HAPUS] Tidak perlu validasi input user
            'cashier_pin' => 'required|digits:6',
        ], [
            'amount.min' => 'Minimal Top Up Rp 1.000',
            'cashier_pin.digits' => 'PIN harus 6 digit angka',
            // 'cashier_id.required' => 'Pilih kasir terlebih dahulu'
        ]);

        // 2. Pastikan User Dipilih/Valid
        if (!$this->selectedUserId) {
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
        // [UBAH LOGIKA] Ambil Kasir dari Session Auth, bukan dari input ID
        $kasir = Auth::guard('admin')->user();

        if (!$kasir) {
            $this->dispatch('show-error', message: 'Sesi Kasir Habis. Silakan login ulang.');
            return;
        }

        // Cek PIN Benar? (Bandingkan input PIN dengan PIN Auth User)
        // Asumsi di database PIN disimpan plain/hashed. Sesuaikan logic komparasinya.
        // Jika pakai Hash::check, ganti logic dibawah.
        // Disini kita pakai string compare sesuai kode lama kamu.
        if ((string) $kasir->pin !== (string) $this->cashier_pin) {
            $this->addError('cashier_pin', 'PIN Salah! Cek kembali.');
            return;
        }

        // 4. Jika PIN Benar, Baru Munculkan Modal
        $this->dispatch(
            'show-confirmation-modal',
            username: $this->selectedUserName,
            amount: $this->amount,
            cashier_name: $kasir->nama_lengkap // Nama kasir yang login
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
        // [UBAH LOGIKA] Pakai Auth lagi
        $kasir = Auth::guard('admin')->user();

        if (!$kasir || (string) $kasir->pin !== (string) $this->cashier_pin) {
            $this->dispatch('show-error', message: 'Verifikasi Gagal / Sesi Habis.');
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
                'admin_id' => $kasir->id, // ID Kasir yang login
                'bukti_transfer' => 'MANUAL_CASH',
            ]);

            // C. Simpan Mutasi (Buku Tabungan)
            BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $this->amount,
                'current_balance' => $user->saldo,
                'category' => 'topup',
                'description' => 'Setoran Tunai via Kasir ' . explode(' ', $kasir->nama_lengkap)[0], // Gunakan nama_lengkap biar rapi
                'related_user_id' => $kasir->id
            ]);

            // D. BUAT NOTIFIKASI
            NotificationService::send(
                $user->id,
                'Top Up Berhasil',
                'Saldo senilai Rp ' . number_format($this->amount, 0, ',', '.') . ' berhasil ditambahkan oleh Kasir.',
                'topup',
                ['amount' => $this->amount, 'admin_id' => $kasir->id]
            );

            DB::commit();

            // D. Sukses & Reset
            $targetName = $user->nama_lengkap;

            // Reset Form (Kecuali cashier_id karena itu Auth)
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
        return view('Livewire.admin-topup');
    }
}