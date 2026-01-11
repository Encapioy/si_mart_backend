<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\BalanceMutation;
use App\Models\Transaction;

class MerchantDashboardController extends Controller
{
    // 1. Penghasilan hari ini
    public function getDailySummary(Request $request)
    {
        $user = $request->user(); // Asumsi merchant adalah user yang login
        $today = Carbon::today();

        // 1. Ambil Transaksi Hari Ini milik Merchant ini
        // Asumsi status 'paid' atau 'success' adalah transaksi valid
        $query = Transaction::where('merchant_id', $user->id)
            ->whereDate('created_at', $today)
            ->where('status', 'success');

        // 2. Hitung Financials
        $grossRevenue = $query->sum('total_amount'); // Total Kotor
        $totalTransactions = $query->count(); // Jumlah Transaksi

        // Hitung Donasi Palestina (10%)
        // Kita hitung di backend biar konsisten, frontend tinggal display
        $palestineDonation = $grossRevenue * 0.10;

        // Sisa Pendapatan setelah donasi (tapi belum dikurangi modal)
        $revenueAfterDonation = $grossRevenue - $palestineDonation;

        // 3. Cari Produk Terlaris Hari Ini (Best Seller)
        // Ini agak tricky, butuh join ke tabel detail transaksi
        $bestSellingProduct = DB::table('transaction_details')
            ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->join('products', 'products.id', '=', 'transaction_details.product_id')
            ->where('transactions.merchant_id', $user->id)
            ->whereDate('transactions.created_at', $today)
            ->where('transactions.status', 'success')
            ->select('products.name', DB::raw('SUM(transaction_details.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->first();

        // Format Data untuk Produk Terlaris
        $topProductData = null;
        if ($bestSellingProduct) {
            $topProductData = [
                'name' => $bestSellingProduct->name,
                'total_sold' => (int) $bestSellingProduct->total_sold,
            ];
        }

        // 4. Return JSON response
        return response()->json([
            'status' => 'success',
            'data' => [
                'date' => $today->format('Y-m-d'),
                'summary' => [
                    'gross_revenue' => (int) $grossRevenue,       // Rp 1.000.000
                    'donation_amount' => (int) $palestineDonation, // Rp 100.000
                    'revenue_after_donation' => (int) $revenueAfterDonation, // Rp 900.000
                    'total_transactions' => (int) $totalTransactions, // 25
                ],
                'best_seller' => $topProductData, // Bisa null kalau belum ada penjualan
                'message' => 'Data dashboard berhasil diambil'
            ]
        ]);
    }

    // 2. TOTAL PENDAPATAN
    public function getIncomeStats(Request $request)
    {
        $user = $request->user();

        // Base Query: Cari riwayat uang hasil jualan
        $query = BalanceMutation::where('user_id', $user->id)
            ->where('type', 'credit')
            ->where('category', 'payment');

        // 1. Pendapatan Hari Ini (Tetap query mutation)
        $incomeToday = (clone $query)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        // 2. Pendapatan Bulan Ini (Tetap query mutation)
        $incomeMonth = (clone $query)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        // 3. Total Omzet Seumur Hidup (Tetap query mutation)
        // Ini menunjukkan performa toko dari nol sampai sekarang (walaupun uangnya udah ditarik)
        $totalOmzet = (clone $query)->sum('amount');

        return response()->json([
            'message' => 'Data pendapatan berhasil diambil',
            'data' => [
                // Statistik Performa (Omzet)
                'income_today' => (int) $incomeToday,
                'income_this_month' => (int) $incomeMonth,
                'income_all_time' => (int) $totalOmzet,

                // --- BAGIAN INI YANG KITA SESUAIKAN ---

                // 1. Saldo Hasil Jualan (Yang bisa di-withdraw)
                'current_merchant_balance' => (int) $user->merchant_balance,

                // 2. Saldo Utama (Yang bisa buat belanja)
                'current_wallet_balance' => (int) $user->saldo
            ]
        ]);
    }

    // 3. REKAP PENDAPATAN
    public function getFinancialDashboard(Request $request)
    {
        $user = $request->user();

        // 1. Tangkap Filter dari Frontend (Default: 'today')
        // Opsi: 'today', 'month', 'all'
        $period = $request->input('period', 'today');

        // 2. Ambil List ID Toko User
        $storeIds = \App\Models\Store::where('user_id', $user->id)->pluck('id');

        if ($storeIds->isEmpty()) {
            return response()->json(['message' => 'Belum ada toko'], 200);
        }

        // 3. Logic Tanggal Dinamis
        // Kita siapkan variable tanggal, tapi biarkan null jika periodenya 'all'
        $startDate = null;
        $endDate = null;

        if ($period === 'today') {
            $startDate = Carbon::today(); // 00:00 hari ini
            $endDate = Carbon::now()->endOfDay();
        } elseif ($period === 'month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }
        // Jika 'all', startDate tetap null (artinya tidak difilter waktu)

        // --- BAGIAN A & B: Detail per Sumber & Total Pendapatan ---

        $stores = \App\Models\Store::where('user_id', $user->id)
            ->withSum([
                'transactions' => function ($query) use ($startDate, $endDate) {
                    $query->where('status', 'paid');

                    // Terapkan filter tanggal HANYA JIKA bukan 'all'
                    if ($startDate && $endDate) {
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }

                }
            ], 'total_bayar')
            ->get();

        // Hitung Total Gabungan (Kotak Besar) dari hasil query di atas
        $totalRevenue = $stores->sum('transactions_sum_total_bayar');

        // --- BAGIAN C: Transaksi Terakhir (Riwayat Global) ---
        // User minta list 10 transaksi terakhir.
        // Biasanya list "Recent Activity" itu TETAP menampilkan yang terbaru (absolut),
        // tidak peduli filter dashboardnya hari ini/bulan ini.
        // Tapi kalau kamu mau list ini juga ikut terfilter tanggalnya,
        // tinggal copy logic if($startDate) ke bawah.
        // Di sini saya buat logic DEFAULT (Absolut 10 Terbaru tanpa filter waktu) biar wajar.

        $recentTransactions = Transaction::whereIn('store_id', $storeIds)
            ->where('status', 'paid')
            ->with('store:id,nama_toko') // Kita butuh nama tokonya
            ->latest() // Urut dari paling baru
            ->take(10) // Limit 10 biji
            ->get();

        // 4. Return Response Siap Pakai
        return response()->json([
            'status' => 'success',
            'filter_used' => $period, // Info balik ke frontend
            'data' => [
                // KOTAK BESAR (Total)
                'summary' => [
                    'total_revenue' => (int) $totalRevenue,
                    'label' => $this->getLabel($period), // Helper kecil buat teks UI
                ],

                // LIST TENGAH (Per Toko)
                'store_details' => $stores->map(function ($store) {
                    return [
                        'store_id' => $store->id,
                        'store_name' => $store->nama_toko,
                        'revenue' => (int) $store->transactions_sum_total_bayar,
                    ];
                }),

                // LIST BAWAH (Riwayat Gabungan)
                'recent_transactions' => $recentTransactions->map(function ($trx) {
                    return [
                        'id' => $trx->id,
                        'description' => $trx->description,
                        'amount' => (int) $trx->total_bayar,
                        'store_name' => $trx->store->nama_toko ?? 'Toko Dihapus',
                        'time' => $trx->created_at->format('H:i'),
                        'date' => $trx->created_at->format('d M'),
                        'full_datetime' => $trx->created_at->toIso8601String(), // Buat sorting di frontend kalau perlu
                    ];
                })
            ]
        ]);
    }

    // Helper Function kecil untuk label (Bisa ditaruh di bawah controller)
    private function getLabel($period)
    {
        if ($period == 'today')
            return 'Hari Ini';
        if ($period == 'month')
            return 'Bulan Ini';
        return 'Total Keseluruhan';
    }
}