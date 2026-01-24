<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Advertisement;
use Livewire\Attributes\Layout;

class AdDetail extends Component
{
    public $ad;

    public function mount($id)
    {
        // Ambil data iklan beserta Toko dan Ownernya (untuk nomor HP)
        // Pastikan relasi di model Advertisement dan Store sudah benar
        $this->ad = Advertisement::with('store.owner')->findOrFail($id);
    }

    // Kita pakai layout kosong/khusus biar lebih immersif (tanpa navbar user biasa)
    // Atau sesuaikan dengan layout utamamu
    public function render()
    {
        return view('Livewire.ad-detail');
    }
}