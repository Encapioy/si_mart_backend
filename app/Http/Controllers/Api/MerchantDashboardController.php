<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Transaction; // Sesuaikan dengan nama model transaksimu
use App\Models\TransactionDetail; // Jika produk ada di tabel detail

class MerchantDashboardController extends Controller
{
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
}