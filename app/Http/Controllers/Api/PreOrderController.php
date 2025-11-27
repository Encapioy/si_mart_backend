<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PreOrder;
use App\Models\Product;
use App\Models\Admin;
use App\Models\User; // Buat ambil merchant
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PreOrderController extends Controller
{
    // 1. PESAN PRE-ORDER (BELI)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'nama_penerima' => 'required|string',
            'catatan' => 'nullable|string',
            'pin' => 'required|string|size:6',
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $user = $request->user();
        if ($user->pin !== $request->pin)
            return response()->json(['message' => 'PIN Salah'], 401);

        return DB::transaction(function () use ($request, $user) {
            $product = Product::lockForUpdate()->find($request->product_id);

            // Validasi: Apakah ini barang PO?
            if (!$product->is_preorder)
                throw new \Exception("Produk ini bukan Pre-Order!");

            // Cek Kuota PO
            if ($product->po_kuota < $request->qty)
                throw new \Exception("Kuota Pre-Order habis!");

            // Hitung Total Bayar (Harga Jual x Qty)
            // Harga Jual sudah termasuk margin Simart
            $totalBayar = $product->harga_akhir * $request->qty;

            // Cek Saldo User
            if ($user->saldo < $totalBayar)
                throw new \Exception("Saldo tidak cukup!");

            // --- ALUR UANG (SPLIT PAYMENT) ---

            // 1. Potong Saldo Pembeli
            $user->saldo -= $totalBayar;
            $user->save();

            $this->recordMutation($user, $totalBayar, 'debit', 'purchase_po', 'Pre-Order: ' . $product->nama_produk);

            // 2. Distribusi Uang (Logic Baru)

            // A. Hitung Potongan 15%
            $totalPotongan = $totalBayar * 0.15;

            // B. Jatah Developer (100 per qty)
            $totalJatahDev = 100 * $request->qty;

            // C. Jatah Admin Kasir (Sisa potongan)
            $totalJatahKasir = max(0, $totalPotongan - $totalJatahDev);

            // D. Jatah Merchant (Sisa uang)
            $totalJatahMerchant = $totalBayar - $totalPotongan;

            // Transfer Merchant
            if ($product->seller) {
                $product->seller->saldo += $totalJatahMerchant;
                $product->seller->save();

                $this->recordMutation($product->seller, $totalJatahMerchant, 'credit', 'sale', 'Penjualan: ' . $product->nama_produk);
            }

            // Transfer Admin Kasir
            $adminKasir = Admin::where('role', 'kasir')->first();
            if ($adminKasir) {
                $adminKasir->saldo += $totalJatahKasir;
                $adminKasir->save();

                $this->recordMutation($adminKasir, $totalJatahKasir, 'credit', 'sale_profit', 'Profit Simart: ' . $product->nama_produk);
            }

            // Transfer Admin Developer
            $adminDev = Admin::where('role', 'developer')->first();
            if ($adminDev) {
                $adminDev->saldo += $totalJatahDev;
                $adminDev->save();

                $this->recordMutation($adminDev, $totalJatahDev, 'credit', 'sale_tax', 'Tax Developer: ' . $product->nama_produk);
            }

            // 3. Kurangi Kuota PO
            $product->po_kuota -= $request->qty;
            $product->save();

            // 4. Buat Tiket PO
            $po = PreOrder::create([
                'po_code' => 'PO-' . strtoupper(Str::random(10)),
                'user_id' => $user->id,
                'product_id' => $product->id,
                'qty' => $request->qty,
                'total_bayar' => $totalBayar,
                'nama_penerima' => $request->nama_penerima,
                'catatan' => $request->catatan,
                'status' => 'paid', // Sudah bayar, menunggu barang
            ]);

            return response()->json([
                'message' => 'Pre-Order Berhasil!',
                'po_code' => $po->po_code,
                'sisa_saldo' => $user->saldo
            ]);
        });
    }

    // 2. BATALKAN PRE-ORDER (UPDATE: H-3 & PINALTI MERCHANT)
    public function cancel(Request $request, $id)
    {
        $user = $request->user();

        $po = PreOrder::where('id', $id)->where('user_id', $user->id)->first();

        if (!$po)
            return response()->json(['message' => 'PO tidak ditemukan'], 404);

        // --- LOGIKA H-3 ---
        $estimasi = \Carbon\Carbon::parse($po->product->po_estimasi);
        $hariIni = now();

        // Hitung selisih hari (false agar bisa negatif kalau lewat)
        $selisihHari = $hariIni->diffInDays($estimasi, false);

        // Jika tinggal 3 hari atau sudah lewat, tolak pembatalan
        if ($selisihHari <= 3) {
            return response()->json([
                'message' => 'Gagal! Pembatalan hanya bisa dilakukan maksimal H-3 dari estimasi tiba.',
                'estimasi' => $estimasi->format('d M Y'),
                'sisa_hari' => $selisihHari
            ], 400);
        }
        // ------------------

        if ($po->status != 'paid')
            return response()->json(['message' => 'Hanya pesanan PAID yang bisa dibatalkan'], 400);

        return DB::transaction(function () use ($po, $user) {
            $product = $po->product;
            $totalUang = $po->total_bayar;

            // =========================================================
            // TAHAP 1: ROLLBACK (TARIK SEMUA UANG DARI PEREDARAN)
            // Kita harus menarik uang sesuai rumus SAAT BELI (Purchase)
            // =========================================================

            // Hitung ulang pembagian saat beli dulu (Rumus Margin 15%)
            $potonganAwal = $totalUang * 0.15;
            $jatahDevAwal = 100 * $po->qty;
            $jatahKasirAwal = max(0, $potonganAwal - $jatahDevAwal);
            $jatahMerchantAwal = $totalUang - $potonganAwal;

            // A. Tarik dari Merchant (Jika ada seller)
            if ($product->seller) {
                if ($product->seller->saldo < $jatahMerchantAwal) {
                    throw new \Exception("Gagal batal. Saldo Merchant tidak cukup untuk ditarik.");
                }
                $product->seller->saldo -= $jatahMerchantAwal;
                $product->seller->save();

                $this->recordMutation($product->seller, $jatahMerchantAwal, 'debit', 'system_rollback', 'Tarik Saldo Batal PO: ' . $product->nama_produk);
            }

            // B. Tarik dari Admin Kasir (Profit Simart)
            $adminKasir = Admin::where('role', 'kasir')->first();
            if ($adminKasir) {
                $adminKasir->saldo -= $jatahKasirAwal;
                $adminKasir->save();

                $this->recordMutation($adminKasir, $jatahKasirAwal, 'debit', 'system_rollback', 'Batal Profit PO: ' . $product->nama_produk);
            }

            // C. Tarik dari Admin Developer (Tax)
            $adminDev = Admin::where('role', 'developer')->first();
            if ($adminDev) {
                $adminDev->saldo -= $jatahDevAwal;
                $adminDev->save();

                $this->recordMutation($adminDev, $jatahDevAwal, 'debit', 'system_rollback', 'Batal Tax PO: ' . $product->nama_produk);
            }

            // =========================================================
            // TAHAP 2: DISTRIBUSI PINALTI BARU (55% - 40% - 5%)
            // =========================================================

            // 1. REFUND USER (55%)
            $refundUser = $totalUang * 0.55;
            $user->saldo += $refundUser;
            $user->save();

            $this->recordMutation($user, $refundUser, 'credit', 'refund', 'Refund Batal PO (55%): ' . $product->nama_produk);

            // 2. DENDA KE DEVELOPER (5%)
            $dendaDev = $totalUang * 0.05;
            if ($adminDev) {
                $adminDev->saldo += $dendaDev;
                $adminDev->save();

                $this->recordMutation($adminDev, $dendaDev, 'credit', 'penalty_income', 'Pendapatan Denda PO (5%): ' . $product->nama_produk);
            }

            // 3. DENDA KE PENJUAL (40%)
            // Penjualnya bisa Merchant, bisa Simart (Admin Kasir)
            $dendaPenjual = $totalUang * 0.40;

            if ($product->store_id != null) {
                // KASUS A: Barang Merchant -> Denda masuk ke Merchant
                if ($product->seller) {
                    $product->seller->saldo += $dendaPenjual;
                    $product->seller->save();

                    $this->recordMutation($product->seller, $dendaPenjual, 'credit', 'penalty_income', 'Kompensasi Batal PO (40%): ' . $product->nama_produk);
                }
            } else {
                // KASUS B: Barang Simart -> Denda masuk ke Admin Kasir
                if ($adminKasir) {
                    $adminKasir->saldo += $dendaPenjual;
                    $adminKasir->save();

                    $this->recordMutation($adminKasir, $dendaPenjual, 'credit', 'penalty_income', 'Kompensasi Batal PO (40%): ' . $product->nama_produk);
                }
            }

            // --- FINALISASI ---
            $product->po_kuota += $po->qty;
            $product->save();

            $po->status = 'cancelled';
            $po->save();

            return response()->json([
                'message' => 'PO Dibatalkan. Refund 55% masuk ke saldo.',
                'refund_diterima' => $refundUser,
                'sisa_saldo_user' => $user->saldo
            ]);
        });
    }

    // 3. AMBIL BARANG (Scan QR di Kasir Simart)
    public function markAsTaken(Request $request)
    {
        // Request dilakukan oleh Admin Kasir
        if ($request->user()->role !== 'kasir')
            return response()->json(['message' => 'Unauthorized'], 403);

        $request->validate([
            'po_code' => 'required|exists:pre_orders,po_code'
        ]);

        $po = PreOrder::where('po_code', $request->po_code)->first();

        if ($po->status == 'cancelled')
            return response()->json(['message' => 'PO ini sudah dibatalkan!'], 400);
        if ($po->status == 'taken')
            return response()->json(['message' => 'PO ini sudah diambil sebelumnya!'], 400);

        $po->status = 'taken';
        $po->save();

        return response()->json(['message' => 'Barang berhasil diserahkan. Transaksi Selesai.']);
    }

    // 4. LIST PESANAN SAYA (History PO)
    public function myOrders(Request $request)
    {
        $orders = PreOrder::where('user_id', $request->user()->id)
            ->with('product')
            ->latest()
            ->get();

        return response()->json(['data' => $orders]);
    }
}