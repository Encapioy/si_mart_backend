<?php

namespace App\Livewire;
use App\Models\Store;

use Livewire\Component;
use Livewire\Attributes\Layout;

class AdminMerchantList extends Component
{
    #[Layout('components.layouts.admin')]
    public function render()
    {
        // Ambil data toko + pemiliknya
        $stores = Store::with('owner')->paginate(10);
        return view('livewire.admin-merchant-list', ['stores' => $stores]);
    }
}
