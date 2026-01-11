<?php

namespace App\Livewire;
use App\Models\Store;

use Livewire\Component;

class AdminMerchantList extends Component
{
    public function render()
    {
        // Ambil data toko + pemiliknya
        $stores = Store::with('user')->paginate(10);
        return view('livewire.admin-merchant-list', ['stores' => $stores])->layout('components.layouts.sidebar');
    }
}
