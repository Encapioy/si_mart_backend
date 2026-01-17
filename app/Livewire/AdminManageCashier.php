<?php

namespace App\Livewire;

use App\Models\Admin;
use App\Models\TopUp;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManageCashier extends Component
{
    use WithPagination;

    // --- Filter & Data ---
    public $search = '';
    public $grandTotal = 0;

    // --- Modal History ---
    public $selectedCashier = null;
    public $historyTopups = [];

    // --- Form CRUD ---
    public $adminId;
    public $nama_lengkap = '';
    public $username = '';
    public $password = '';
    public $pin = '';

    public $isEditMode = false;
    public $showPasswordMap = []; // Array untuk toggle lihat password di tabel

    public function mount()
    {
        // Hitung total uang yang diproses semua kasir (status: approved)
        $this->grandTotal = TopUp::where('status', 'approved')
            ->whereNotNull('admin_id')
            ->sum('amount');
    }

    // --- VALIDASI ---
    protected function rules()
    {
        return [
            'nama_lengkap' => 'required|min:3',
            'username' => [
                'required',
                'alpha_dash',
                // Cek unique username di tabel admins, abaikan ID sendiri jika sedang edit
                Rule::unique('admins', 'username')->ignore($this->adminId)
            ],
            // Password wajib jika buat baru, opsional jika edit
            'password' => $this->isEditMode ? 'nullable|min:6' : 'required|min:6',
            'pin' => 'required|numeric|digits:6',
        ];
    }

    // --- ACTION CRUD ---

    // 1. Buka Modal Tambah
    public function create()
    {
        $this->resetInput();
        $this->isEditMode = false;
        $this->dispatch('open-form-modal');
    }

    // 2. Simpan Data Baru
    public function store()
    {
        $this->validate();

        Admin::create([
            'nama_lengkap' => $this->nama_lengkap,
            'username' => $this->username,
            'password' => Hash::make($this->password), // Hash untuk sistem login
            'plain_password' => $this->password,       // Simpan mentah untuk dilihat admin pusat
            'pin' => $this->pin,
            'role' => 'kasir',
        ]);

        $this->dispatch('close-form-modal');
        $this->dispatch('notify', message: 'Akun kasir berhasil dibuat!');
        $this->resetInput();
    }

    // 3. Buka Modal Edit
    public function edit($id)
    {
        $admin = Admin::findOrFail($id);

        $this->adminId = $admin->id;
        $this->nama_lengkap = $admin->nama_lengkap;
        $this->username = $admin->username;
        $this->pin = $admin->pin;
        $this->password = ''; // Kosongkan password saat edit (biar user isi kalau mau ganti aja)

        $this->isEditMode = true;
        $this->dispatch('open-form-modal');
    }

    // 4. Update Data
    public function update()
    {
        $this->validate();

        $admin = Admin::findOrFail($this->adminId);

        $data = [
            'nama_lengkap' => $this->nama_lengkap,
            'username' => $this->username,
            'pin' => $this->pin,
        ];

        // Jika password diisi, maka update password & plain_password
        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
            $data['plain_password'] = $this->password;
        }

        $admin->update($data);

        $this->dispatch('close-form-modal');
        $this->dispatch('notify', message: 'Data kasir berhasil diperbarui!');
        $this->resetInput();
    }

    // 5. Hapus Data
    public function deleteConfirm($id)
    {
        // Opsional: Bisa pakai sweetalert confirm di frontend
        $this->dispatch('confirm-delete', id: $id);
    }

    public function delete($id)
    {
        $admin = Admin::findOrFail($id);

        // Validasi: Jangan hapus diri sendiri atau admin pusat
        if ($admin->id === auth()->id() || $admin->role === 'pusat') {
            return;
        }

        $admin->delete();
        $this->dispatch('notify', message: 'Akun kasir dihapus.');
    }

    // --- HELPER FUNCTIONS ---

    public function resetInput()
    {
        $this->nama_lengkap = '';
        $this->username = '';
        $this->password = '';
        $this->pin = '';
        $this->adminId = null;
        $this->resetErrorBag();
    }

    // Toggle mata untuk melihat password di tabel
    public function togglePasswordVisibility($id)
    {
        if (isset($this->showPasswordMap[$id])) {
            unset($this->showPasswordMap[$id]);
        } else {
            $this->showPasswordMap[$id] = true;
        }
    }

    // --- HISTORY MODAL (LOGIC LAMA) ---
    public function showHistory($adminId)
    {
        $this->selectedCashier = Admin::find($adminId);

        $this->historyTopups = TopUp::with('user')
            ->where('admin_id', $adminId)
            ->where('status', 'approved') // STATUS: APPROVED
            ->latest()
            ->take(10)
            ->get();

        $this->dispatch('open-history-modal'); // Nama event disesuaikan agar beda dengan form modal
    }

    public function save()
    {
        if ($this->isEditMode) {
            $this->update();
        } else {
            $this->store();
        }
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        $cashiers = Admin::where('role', '=', 'kasir')
            ->when($this->search, function ($q) {
                // PENCARIAN BERDASARKAN USERNAME
                $q->where('username', 'like', '%' . $this->search . '%');
            })
            // Hitung Total Nominal (status: approved)
            ->withSum([
                'topUps' => function ($q) {
                    $q->where('status', 'approved');
                }
            ], 'amount')
            // Hitung Total Frekuensi (status: approved)
            ->withCount([
                'topUps' => function ($q) {
                    $q->where('status', 'approved');
                }
            ])
            ->orderByDesc('top_ups_sum_amount')
            ->paginate(10);

        return view('Livewire.admin-manage-cashier', [
            'cashiers' => $cashiers
        ]);
    }
}