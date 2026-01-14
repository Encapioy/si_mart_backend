<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Transaction; // Wajib import ini untuk hitung omset
use Livewire\Attributes\Layout;

class AdminFinancialStats extends Component
{
    // --- 1. PROPERTI STATISTIK UMUM ---
    public $totalUangBeredar = 0;
    public $totalUserSaldo = 0;
    public $totalMerchantBalance = 0;
    public $persenUser = 0;
    public $persenMerchant = 0;

    // --- 2. PROPERTI UNTUK DATA CHART (ARRAY) ---
    // Top 5 Siswa
    public $chartUserLabels = [];
    public $chartUserValues = [];

    // Top 5 Merchant
    public $chartMerchantLabels = [];
    public $chartMerchantValues = [];

    // Top 5 Toko (Omset)
    public $chartStoreLabels = [];
    public $chartStoreValues = [];


    public function mount()
    {
        // A. HITUNG TOTAL UANG BEREDAR
        $this->totalUserSaldo = User::sum('saldo');
        $this->totalMerchantBalance = User::sum('merchant_balance');
        $this->totalUangBeredar = $this->totalUserSaldo + $this->totalMerchantBalance;

        // Hitung Persentase (Cegah error division by zero)
        if ($this->totalUangBeredar > 0) {
            $this->persenUser = number_format(($this->totalUserSaldo / $this->totalUangBeredar) * 100, 1, ',', '.');
            $this->persenMerchant = number_format(($this->totalMerchantBalance / $this->totalUangBeredar) * 100, 1, ',', '.');
        }

        // B. DATA CHART 1: TOP 5 SISWA (SALDO)
        $topUsers = User::orderByDesc('saldo')->take(5)->get();
        // Pluck: Ambil kolom tertentu dan jadikan array
        $this->chartUserLabels = array_values($topUsers->pluck('nama_lengkap')->toArray());
        $this->chartUserValues = array_values($topUsers->pluck('saldo')->map(fn($v) => (float) $v)->toArray());

        // C. DATA CHART 2: TOP 5 MERCHANT (SALDO)
        $topMerchants = User::where('merchant_balance', '>', 0) // Ambil yang punya saldo merchant aja
            ->orderByDesc('merchant_balance') // Sorting via SQL (Cepat)
            ->take(5)
            ->get();
        $this->chartMerchantLabels = array_values($topMerchants->pluck('nama_lengkap')->toArray());
        $this->chartMerchantValues = array_values($topMerchants->pluck('merchant_balance')->map(fn($v) => (float) $v)->toArray());

        // D. DATA CHART 3: TOP 5 TOKO (OMSET PENJUALAN)
        // Kita ambil dari tabel Transaction, group by store_id, dan sum total_bayar
        $topStores = Transaction::selectRaw('store_id, sum(total_bayar) as total_omset')
            ->where('type', 'payment') // Hanya ambil tipe pembayaran (bukan transfer)
            ->where('status', 'paid')  // Hanya yang sukses
            ->groupBy('store_id')
            ->orderByDesc('total_omset')
            ->with('store') // Eager load relasi ke tabel stores
            ->take(5)
            ->get();

        // Mapping data toko (Handle jika toko dihapus/null)
        $this->chartStoreLabels = array_values($topStores->map(function ($item) {
            return $item->store->nama_toko ?? 'Toko Terhapus';
        })->toArray());

        $this->chartStoreValues = array_values($topStores->pluck('total_omset')->map(fn($v) => (float) $v)->toArray());
    }

    #[Layout('components.layouts.admin')]
    public function render()
    {
        return view('Livewire.admin-financial-stats');
    }
}