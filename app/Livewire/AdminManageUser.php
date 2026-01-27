<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManageUser extends Component
{
    use WithPagination;

    // --- Data & Filter ---
    public $search = '';

    // --- Form Variables ---
    public $userId;
    public $nama_lengkap = '';
    public $username = '';
    public $email = '';
    public $no_hp = '';
    public $pin = '';
    public $password = ''; // Untuk reset password

    // --- State ---
    public $isEditMode = false;

    // --- Validasi ---
    protected function rules()
    {
        return [
            'nama_lengkap' => 'required|min:3',
            'username'     => ['required', 'alpha_dash', Rule::unique('users', 'username')->ignore($this->userId)],
            'email'        => ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)],
            'no_hp'        => ['nullable', 'numeric', 'digits_between:10,15'],
            'pin'          => 'required|numeric|digits:6',
            // Password opsional saat edit (hanya diisi jika ingin reset)
            'password'     => $this->isEditMode ? 'nullable|min:6' : 'required|min:6',
        ];
    }

    // --- ACTIONS ---

    // 1. Buka Modal Tambah User (Opsional, biasanya User daftar sendiri/import)
    public function create()
    {
        $this->resetInput();
        $this->isEditMode = false;
        $this->dispatch('open-form-modal');
    }

    // 2. Simpan User Baru
    public function store()
    {
        $this->validate();

        User::create([
            'nama_lengkap' => $this->nama_lengkap,
            'username'     => $this->username,
            'email'        => $this->email,
            'no_hp'        => $this->no_hp,
            'pin'          => $this->pin,
            'password'     => Hash::make($this->password),
            'saldo'        => 0, // Default saldo 0
            // 'member_id' => ... (Bisa digenerate otomatis di Model atau Observer)
        ]);

        $this->dispatch('close-form-modal');
        $this->dispatch('notify', message: 'User baru berhasil ditambahkan!');
        $this->resetInput();
    }

    // 3. Buka Modal Edit
    public function edit($id)
    {
        $user = User::findOrFail($id);

        $this->userId       = $user->id;
        $this->nama_lengkap = $user->nama_lengkap;
        $this->username     = $user->username;
        $this->email        = $user->email;
        $this->no_hp        = $user->no_hp;
        $this->pin          = $user->pin;
        $this->password     = ''; // Kosongkan, admin isi cuma kalau mau reset

        $this->isEditMode = true;
        $this->dispatch('open-form-modal');
    }

    // 4. Update Data User
    public function update()
    {
        $this->validate();

        $user = User::findOrFail($this->userId);

        $data = [
            'nama_lengkap' => $this->nama_lengkap,
            'username'     => $this->username,
            'email'        => $this->email,
            'no_hp'        => $this->no_hp,
            'pin'          => $this->pin,
        ];

        // Hanya update password jika admin mengisi kolom password
        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);

        $this->dispatch('close-form-modal');
        $this->dispatch('notify', message: 'Data user berhasil diperbarui!');
        $this->resetInput();
    }

    // 5. Hapus User
    public function delete($id)
    {
        $user = User::findOrFail($id);

        // Cek saldo dulu, jangan hapus kalau masih ada saldo (Opsional Safety)
        if ($user->saldo > 0) {
            $this->dispatch('notify', message: 'GAGAL: User masih memiliki saldo Rp ' . number_format($user->saldo), type: 'error');
            return;
        }

        $user->delete();
        $this->dispatch('notify', message: 'User berhasil dihapus.');
    }

    // --- HELPER ---
    public function save()
    {
        if ($this->isEditMode) $this->update(); else $this->store();
    }

    public function resetInput()
    {
        $this->userId = null;
        $this->nama_lengkap = ''; $this->username = ''; $this->email = '';
        $this->no_hp = ''; $this->pin = ''; $this->password = '';
        $this->resetErrorBag();
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        $users = User::query()
            ->when($this->search, function($q) {
                $q->where('nama_lengkap', 'like', '%'.$this->search.'%')
                  ->orWhere('username', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(10);

        return view('Livewire.admin-manage-user', [
            'users' => $users
        ]);
    }
}