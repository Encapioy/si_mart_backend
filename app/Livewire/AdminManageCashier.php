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

    // --- Modal History Variables ---
    public $selectedCashierId = null; // Ganti object jadi ID biar ringan di state
    public $selectedCashierName = '';
    public $showHistoryModal = false;

    // --- Form CRUD Variables ---
    public $adminId;
    public $nama_lengkap = '';
    public $username = '';
    public $password = '';
    public $pin = '';
    public $isEditMode = false;
    public $showPasswordMap = [];

    public function mount()
    {
        $this->grandTotal = TopUp::where('status', 'approved')
            ->whereNotNull('admin_id')
            ->sum('amount');
    }

    protected function rules()
    {
        return [
            'nama_lengkap' => 'required|min:3',
            'username' => [
                'required',
                'alpha_dash',
                Rule::unique('admins', 'username')->ignore($this->adminId)
            ],
            'password' => $this->isEditMode ? 'nullable|min:6' : 'required|min:6',
            'pin' => 'required|numeric|digits:6',
        ];
    }

    // --- ACTION CRUD (Create, Store, Edit, Update, Delete) ---
    // (Kode CRUD sama persis seperti sebelumnya, saya persingkat di sini agar fokus ke History)

    public function create()
    {
        $this->resetInput();
        $this->isEditMode = false;
        $this->dispatch('open-form-modal');
    }

    public function store()
    {
        $this->validate();
        Admin::create([
            'nama_lengkap' => $this->nama_lengkap,
            'username' => $this->username,
            'password' => Hash::make($this->password),
            'plain_password' => $this->password,
            'pin' => $this->pin,
            'role' => 'kasir',
        ]);
        $this->dispatch('close-form-modal');
        $this->dispatch('notify', message: 'Akun kasir berhasil dibuat!');
        $this->resetInput();
    }

    public function edit($id)
    {
        $admin = Admin::findOrFail($id);
        $this->adminId = $admin->id;
        $this->nama_lengkap = $admin->nama_lengkap;
        $this->username = $admin->username;
        $this->pin = $admin->pin;
        $this->password = '';
        $this->isEditMode = true;
        $this->dispatch('open-form-modal');
    }

    public function update()
    {
        $this->validate();
        $admin = Admin::findOrFail($this->adminId);
        $data = ['nama_lengkap' => $this->nama_lengkap, 'username' => $this->username, 'pin' => $this->pin];
        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
            $data['plain_password'] = $this->password;
        }
        $admin->update($data);
        $this->dispatch('close-form-modal');
        $this->dispatch('notify', message: 'Data kasir berhasil diperbarui!');
        $this->resetInput();
    }

    public function delete($id)
    {
        $admin = Admin::findOrFail($id);
        if ($admin->id === auth()->id() || $admin->role === 'pusat')
            return;
        $admin->delete();
        $this->dispatch('notify', message: 'Akun kasir dihapus.');
    }

    // --- HISTORY MODAL LOGIC (PERBAIKAN UTAMA DISINI) ---

    public function showHistory($adminId)
    {
        $admin = Admin::find($adminId);
        if (!$admin)
            return;

        $this->selectedCashierId = $adminId;
        $this->selectedCashierName = $admin->username;
        $this->showHistoryModal = true;

        // Reset pagination history ke halaman 1 setiap kali buka modal baru
        $this->resetPage('historyPage');

        $this->dispatch('open-history-modal');
    }

    public function closeHistory()
    {
        $this->showHistoryModal = false;
        $this->selectedCashierId = null;
    }

    public function save()
    {
        if ($this->isEditMode)
            $this->update();
        else
            $this->store();
    }

    public function resetInput()
    {
        $this->nama_lengkap = '';
        $this->username = '';
        $this->password = '';
        $this->pin = '';
        $this->adminId = null;
        $this->resetErrorBag();
    }

    public function togglePasswordVisibility($id)
    {
        if (isset($this->showPasswordMap[$id]))
            unset($this->showPasswordMap[$id]);
        else
            $this->showPasswordMap[$id] = true;
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        // 1. Query Daftar Kasir (Pagination Utama)
        $cashiers = Admin::where('role', '=', 'kasir')
            ->when($this->search, function ($q) {
                $q->where('username', 'like', '%' . $this->search . '%');
            })
            ->withSum(['topUps' => fn($q) => $q->where('status', 'approved')], 'amount')
            ->withCount(['topUps' => fn($q) => $q->where('status', 'approved')])
            ->orderByDesc('top_ups_sum_amount')
            ->paginate(10); // Page name default: 'page'

        // 2. Query History Topup (Pagination Kedua KHUSUS MODAL)
        $historyTopups = [];
        if ($this->selectedCashierId) {
            $historyTopups = TopUp::with('user')
                ->where('admin_id', $this->selectedCashierId)
                ->where('status', 'approved')
                ->latest()
                // PENTING: Gunakan pageName berbeda agar tidak bentrok
                ->paginate(10, ['*'], 'historyPage');
        }

        return view('Livewire.admin-manage-cashier', [
            'cashiers' => $cashiers,
            'historyTopups' => $historyTopups
        ]);
    }
}