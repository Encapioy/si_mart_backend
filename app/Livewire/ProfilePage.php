<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class ProfilePage extends Component
{
    use WithFileUploads;

    // 1. Properti Data Form
    public $nama_lengkap;
    public $email;
    public $no_hp;
    public $photo;

    // 2. Isi data saat halaman dimuat
    public function mount()
    {
        $user = Auth::user();
        $this->nama_lengkap = $user->nama_lengkap;
        $this->email = $user->email;
        $this->no_hp = $user->no_hp;
    }

    // 3. Logika Update Profile
    public function updateProfile()
    {
        $user = Auth::user();

        // Validasi Input
        $this->validate([
            'nama_lengkap' => 'required|string|min:3',
            'no_hp' => 'required|numeric|digits_between:10,14',
            // Cek email unik, tapi abaikan email milik user ini sendiri
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'photo' => 'nullable|image|max:2048',
        ]);

        // Simpan ke Database
        $dataToUpdate = [
            'nama_lengkap' => $this->nama_lengkap,
            'email' => $this->email,
            'no_hp' => $this->no_hp,
        ];

        // 5. LOGIKA UPLOAD FOTO
        if ($this->photo) {
            // Hapus foto lama jika ada (bukan UI Avatar default)
            // Pastikan nama kolom di database kamu 'profile_photo_path' atau sesuaikan
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Simpan foto baru
            $path = $this->photo->store('profile-photos', 'public');
            $dataToUpdate['profile_photo_path'] = $path; // <--- Simpan path ke DB
        }

        $user->update($dataToUpdate);

        // Reset variabel photo agar input file kosong kembali
        $this->photo = null;

        // Kirim notifikasi sukses ke Frontend (SweetAlert)
        $this->dispatch('profile-updated');
    }

    public function logout()
    {
        // 1. Logout dari Guard Web
        Auth::guard('web')->logout();

        // 2. Hapus Sesi (Penting agar tidak bisa di-back)
        session()->invalidate();
        session()->regenerateToken();

        // 3. Redirect ke Halaman Login
        // Kita tidak pakai return redirect() biasa, tapi pakai helper Livewire
        return $this->redirect(route('login'), navigate: true);
    }

    #[Layout('components.layouts.userbar')]
    public function render()
    {
        return view('Livewire.profile-page', [
            'user' => Auth::user()
        ]);
    }
}