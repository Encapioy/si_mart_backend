<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'nama_lengkap' => $this->nama_lengkap,
            'username' => $this->username,
            'email' => $this->email,
            'no_hp' => $this->no_hp,
            'status_verifikasi' => $this->status_verifikasi,
            'profile_photo_url' => $this->profile_photo
                ? asset('storage/' . $this->profile_photo)
                : 'https://ui-avatars.com/api/?name=' . urlencode($this->nama_lengkap) . '&color=7F9CF5&background=EBF4FF',

            // --- BAGIAN PENTING (SALDO) ---

            // 1. Kirim Saldo Asli (Integer) untuk kalkulasi matematika di HP
            'saldo' => (int) $this->saldo,

            // 2. Kirim Flag (Penanda) apakah minus?
            // Agar frontend mudah ganti warna teks (True = Merah, False = Hitam)
            'is_saldo_minus' => $this->saldo < 0,

            // 3. Kirim Format Rupiah Siap Tampil (Frontend gak perlu mikir)
            'saldo_formatted' => $this->saldo < 0
                ? '- Rp ' . number_format(abs($this->saldo), 0, ',', '.') // Jika Minus
                : 'Rp ' . number_format($this->saldo, 0, ',', '.'),       // Jika Positif


            'nfc_id' => $this->nfc_id,
            'joined_at' => $this->created_at->format('d M Y'),
        ];
    }
}