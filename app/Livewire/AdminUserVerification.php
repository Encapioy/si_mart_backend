<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class AdminUserVerification extends Component
{
    use WithPagination;

    // State untuk Modal Reject
    public $showRejectModal = false;
    public $showImageModal = false;
    public $selectedUser = null; // User yang sedang dipilih
    public $alasan_penolakan = '';
    public $activeImage = ''; // Untuk preview gambar besar

    // 1. GET PENDING USERS (Diganti jadi Computed Property di Livewire)
    #[Layout('components.layouts.admin')]
    public function render()
    {
        // Mengambil user pending, diurutkan dari yang terlama (FIFO)
        $pendingUsers = User::where('status_verifikasi', 'pending')
            ->orderBy('updated_at', 'asc')
            ->paginate(10);

        return view('Livewire.admin-user-verification', [
            'pendingUsers' => $pendingUsers
        ]);
    }

    // Fitur: Lihat Gambar KTP Besar
    public function viewImage($path)
    {
        $this->activeImage = asset('storage/' . $path);
        $this->showImageModal = true;
    }

    // 2. APPROVE USER
    public function approve($id)
    {
        $user = User::find($id);

        if (!$user) {
            session()->flash('error', 'User tidak ditemukan.');
            return;
        }

        // [CATATAN 1] Validasi Status: Pastikan hanya yang pending yang bisa diapprove
        if ($user->status_verifikasi !== 'pending') {
            session()->flash('error', 'User ini statusnya bukan pending (mungkin sudah diproses).');
            return;
        }

        $user->status_verifikasi = 'verified';
        $user->save();

        session()->flash('success', "User {$user->nama_lengkap} berhasil diverifikasi!");
    }

    // Persiapan Reject (Buka Modal)
    public function confirmReject($id)
    {
        $this->selectedUser = User::find($id);
        $this->alasan_penolakan = ''; // Reset input
        $this->showRejectModal = true;
    }

    // 3. REJECT USER (Eksekusi)
    public function submitReject()
    {
        if (!$this->selectedUser)
            return;

        // [CATATAN 1] Validasi Status
        if ($this->selectedUser->status_verifikasi !== 'pending') {
            session()->flash('error', 'Gagal: User tidak dalam status pending.');
            $this->showRejectModal = false;
            return;
        }

        // [CATATAN 3] Simpan Alasan (Bisa kirim notif atau simpan ke DB jika ada kolomnya)
        // Disini kita ubah status saja
        $this->selectedUser->status_verifikasi = 'rejected';

        // [CATATAN 2] Kita TIDAK menghapus foto fisik, agar ada bukti history.

        $this->selectedUser->save();

        // TODO: Kirim Notifikasi ke User berisi $this->alasan_penolakan

        session()->flash('success', "Verifikasi {$this->selectedUser->nama_lengkap} ditolak.");
        $this->showRejectModal = false;
    }
}