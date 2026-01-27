<?php

namespace App\Livewire;
use App\Models\Store;

use Livewire\Component;
use Livewire\Attributes\Layout;

class AdminMerchantList extends Component
{

    // 1. Definisikan variabel search
    public $search = '';

    // Reset pagination ke halaman 1 jika user melakukan pencarian
    public function updatedSearch()
    {
        $this->resetPage();
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        // Ambil data toko + pemiliknya
        $stores = Store::query() // Sesuaikan Model Store kamu
            // 2. Tambahkan Logika Filter
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%') // Asumsi kolom nama toko adalah 'name'
                    ->orWhereHas('owner', function ($q) { // Opsional: Cari berdasarkan nama pemilik juga
                        $q->where('nama_lengkap', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(10);

        return view('Livewire.admin-merchant-list', ['stores' => $stores]);
    }
}
