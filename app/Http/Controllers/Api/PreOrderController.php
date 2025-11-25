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
            }

            // Transfer Admin Kasir
            $adminKasir = Admin::where('role', 'kasir')->first();
            if ($adminKasir) {
                $adminKasir->saldo += $totalJatahKasir;
                $adminKasir->save();
            }

            // Transfer Admin Developer
            $adminDev = Admin::where('role', 'developer')->first();
            if ($adminDev) {
                $adminDev->saldo += $totalJatahDev;
                $adminDev->save();
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

    // 2. BATALKAN PRE-ORDER (PINALTI 60%)
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $po = PreOrder::where('id', $id)->where('user_id', $user->id)->first();

        if (!$po)
            return response()->json(['message' => 'PO tidak ditemukan'], 404);
        if ($po->status != 'paid')
            return response()->json(['message' => 'Hanya pesanan PAID yang bisa dibatalkan'], 400);

        return DB::transaction(function () use ($po, $user) {
            $product = $po->product;

            // --- TARIK KEMBALI UANG DARI MERCHANT & KASIR ---
            // Karena saat beli uangnya sudah disebar, sekarang kita harus tarik balik.

            $uangModal = $product->harga_modal * $po->qty; // Rp 3000 x Qty
            $uangMargin = ($product->harga - $product->harga_modal) * $po->qty; // Rp 1000 x Qty
            $totalUang = $po->total_bayar; // Total Rp 4000 x Qty

            // 1. Tarik dari Merchant
            if ($product->seller) {
                if ($product->seller->saldo < $uangModal) {
                    throw new \Exception("Gagal batal. Saldo Merchant tidak cukup untuk refund.");
                }
                $product->seller->saldo -= $uangModal;
                $product->seller->save();
            }

            // 2. Tarik dari Admin Kasir (Margin Awal)
            $adminKasir = Admin::where('role', 'kasir')->first();
            if ($adminKasir) {
                $adminKasir->saldo -= $uangMargin; // Kembalikan profit yg tadi didapat
                $adminKasir->save();
            }

            // --- BAGI-BAGI PINALTI (Denda 60%) ---
            // Uang sekarang ngumpul di sistem ($totalUang). Kita bagi sesuai aturan.

            // 1. Refund ke User (40%)
            $refundUser = $totalUang * 0.40;
            $user->saldo += $refundUser;
            $user->save();

            // 2. Denda ke Admin Kasir (40%)
            // Dia dapet lagi, tapi kali ini sebagai Denda, bukan Profit Jualan
            if ($adminKasir) {
                $adminKasir->saldo += ($totalUang * 0.40);
                $adminKasir->save();
            }

            // 3. Denda ke Admin Developer (20%)
            $adminDev = Admin::where('role', 'developer')->first();
            if ($adminDev) {
                $adminDev->saldo += ($totalUang * 0.20);
                $adminDev->save();
            }

            // --- FINALISASI ---
            // Kembalikan Kuota Barang
            $product->po_kuota += $po->qty;
            $product->save();

            // Update Status PO
            $po->status = 'cancelled';
            $po->save();

            return response()->json([
                'message' => 'PO Dibatalkan. Refund 40% telah masuk saldo.',
                'total_awal' => $totalUang,
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