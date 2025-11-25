<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // 1. LIHAT SEMUA PRODUK (DENGAN FITUR PENCARIAN)
    public function index(Request $request)
    {
        // Mulai membangun query (belum dieksekusi)
        $query = Product::query();

        // --- LOGIKA PENCARIAN ---

        // Jika frontend mengirim parameter ?toko=simart
        if ($request->query('toko') == 'simart') {
            $query->whereNull('store_id');
        }

        // Cek apakah ada kiriman parameter 'search' dari Frontend/Postman?
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_produk', 'like', "%{$keyword}%")
                    ->orWhere('barcode', 'like', "%{$keyword}%");
            });
        }

        // Jika frontend minta ?status=habis -> Tampilkan yg stok 0
        // Jika frontend minta ?status=tersedia -> Tampilkan yg stok > 0
        if ($request->filled('status')) {
            if ($request->status == 'habis') {
                $query->where('stok', '<=', 0);
            } elseif ($request->status == 'tersedia') {
                $query->where('stok', '>', 0);
            }
        }

        // Eksekusi & Pagination
        // Ambil 10 data per halaman, urutkan dari yang terbaru
        $products = $query->latest()->paginate(10);

        // Lampirkan status favorit ke setiap item di halaman ini
        foreach ($products as $product) {
            $product->append('is_favorited');
        }

        return response()->json([
            'message' => 'List produk',
            'data' => $products
        ]);
    }

    // 2. TAMBAH PRODUK BARU (Hybrid: Scan atau Auto-Generate) (Untuk Penjual/Admin)
    public function store(Request $request)
    {
        $user = $request->user();

        // 1. CEK USER VERIFIED
        if ($user instanceof \App\Models\User) {
            if ($user->status_verifikasi != 'verified') return response()->json(['message' => 'Wajib verifikasi KTP!'], 403);
        }

        // 2. RULES VALIDASI
        $rules = [
            'barcode' => 'nullable|unique:products',
            'nama_produk' => 'required|string',
            'harga' => 'required|numeric', // Ini Harga Modal Merchant
            'stok' => 'required|integer',
            'diskon' => 'nullable|integer|min:0|max:100',
            'store_id' => 'nullable|exists:stores,id', // Null = Simart, Isi = Toko Sendiri
            'is_preorder' => 'boolean',
            'po_estimasi' => 'required_if:is_preorder,true|date',
            'po_kuota' => 'required_if:is_preorder,true|integer',
            // Lokasi Rak sekarang SELALU NULLABLE saat input awal (Tugas Admin Kasir nanti)
            'lokasi_rak' => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return response()->json($validator->errors(), 400);

        // 3. LOGIKA TOKO & PRE-ORDER
        $targetStoreId = null;

        if ($request->filled('store_id')) {
            // --- KASUS A: JUAL DI TOKO SENDIRI ---

            // Cek Kepemilikan
            $cekToko = \App\Models\Store::where('id', $request->store_id)->where('user_id', $user->id)->first();
            if (!$cekToko) return response()->json(['message' => 'Toko tidak valid'], 403);

            $targetStoreId = $request->store_id;

            // Validasi: Toko Sendiri TIDAK BOLEH PO
            if ($request->boolean('is_preorder')) {
                return response()->json(['message' => 'Toko sendiri tidak boleh jualan Pre-Order!'], 400);
            }

            // Logika Harga: TIDAK ADA MARKUP
            $hargaModal = $request->harga;
            $hargaJual = $request->harga;

        } else {
            // --- KASUS B: JUAL DI SIMART (TITIP) ---

            $hargaModal = $request->harga;

            // Logika Harga: MARKUP +1000 (Keuntungan Simart)
            // Kecuali yang input adalah Admin Pusat (Simart sendiri), harga tetap.
            if ($user instanceof \App\Models\Admin) {
                $hargaJual = $hargaModal;
            } else {
                // 1. Hitung Target Minimal (Modal + 100)
                $targetReceive = $hargaModal + 100;

                // 2. Cari Harga Jual Mentah (Target / 0.85)
                // Kenapa 0.85? Karena 100% - 15% = 85%
                $rawPrice = $targetReceive / 0.85;

                // 3. Pembulatan ke atas kelipatan 500
                // Contoh: ceil(3647 / 500) = ceil(7.29) = 8 * 500 = 4000
                $hargaJual = ceil($rawPrice / 500) * 500;
            }
        }

        // 4. LOGIKA BARCODE HYBRID
        $final_barcode = $request->filled('barcode') ? $request->barcode : 'SIS-' . mt_rand(100000, 999999);

        // 5. SIMPAN
        $product = Product::create([
            'barcode' => $final_barcode,
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,

            'harga' => $hargaJual,       // Harga Tampil
            'harga_modal' => $hargaModal,// Harga Asli Merchant
            'diskon' => $request->diskon ?? 0,
            'stok' => $request->stok,
            'lokasi_rak' => $request->lokasi_rak, // Merchant bebas kosongkan ini

            'is_preorder' => $request->boolean('is_preorder'),
            'po_estimasi' => $request->po_estimasi,
            'po_kuota' => $request->po_kuota,

            'gambar' => $request->gambar,
            'ingredients' => $request->ingredients,
            'seller_id' => $user->id,
            'seller_type' => get_class($user),
            'store_id' => $targetStoreId,
        ]);

        return response()->json(['message' => 'Produk berhasil ditambahkan', 'data' => $product], 201);
    }

    // 3. CARI PRODUK BY BARCODE (Penting untuk fitur Scan)
    public function getByBarcode($barcode)
    {
        $product = Product::where('barcode', $barcode)->first();

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail produk',
            'data' => $product
        ]);
    }

    // 4. UPDATE STOK/HARGA
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $product->update($request->all());

        return response()->json([
            'message' => 'Produk berhasil diupdate',
            'data' => $product
        ]);
    }

    // 5. HAPUS PRODUK
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Produk berhasil dihapus']);
    }
}