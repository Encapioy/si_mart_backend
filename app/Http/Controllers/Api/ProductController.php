<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // Kita pakai driver GD (bawaan PHP)
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // 1. LIHAT SEMUA PRODUK (DENGAN FITUR PENCARIAN)
    public function index(Request $request)
    {
        $query = Product::query();

        // ------------------------------------------
        // 1. FILTER TOKO
        // ------------------------------------------
        // Filter Toko Simart (Barang Konsinyasi/Umum)
        if ($request->input('toko') == 'simart') {

            // Perbaikan Logika:
            // Kadang data tersimpan sebagai 0, kadang NULL. Kita tangkap keduanya.
            $query->where(function ($q) {
                $q->whereNull('store_id')
                    ->orWhere('store_id', 0);
            });

        }

        // Filter Toko Spesifik
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        // ------------------------------------------
        // 2. SEARCH (SUDAH DISESUAIKAN: barcode)
        // ------------------------------------------
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_produk', 'like', "%{$keyword}%")
                    ->orWhere('barcode', 'like', "%{$keyword}%"); // [FIX] Pakai 'barcode'
            });
        }

        // ------------------------------------------
        // 3. FILTER LAIN
        // ------------------------------------------
        if ($request->filled('category')) {
            // Pastikan kolom 'kategori' ada di tabel.
            // Di gambar kamu tidak terlihat kolom 'kategori', tapi mungkin ada di scroll bawah?
            // Jika tidak ada, hapus blok if ini.
            // $query->where('kategori', $request->category);
        }

        if ($request->filled('status')) {
            if ($request->status == 'habis') {
                $query->where('stok', '<=', 0);
            } elseif ($request->status == 'tersedia') {
                $query->where('stok', '>', 0);
            }
        }

        // ------------------------------------------
        // 4. EAGER LOAD (Relasi)
        // ------------------------------------------
        // Kita load data penjual (User) dan data Toko (Store)
        // Pastikan model Product.php punya function seller() dan store()
        $query->with(['seller:id,nama_lengkap', 'store:id,nama_toko']);

        $products = $query->latest()->paginate(10);

        // Append attribute custom (jika ada logika favorit)
        // $products->each(function($p) { $p->append('is_favorited'); });

        return response()->json([
            'message' => 'List produk berhasil diambil',
            'data' => $products
        ]);
    }

    // 2. TAMBAH PRODUK BARU (Hybrid: Scan atau Auto-Generate) (Untuk Penjual/Admin)
    public function store(Request $request)
    {
        $user = $request->user();

        // 1. CEK USER VERIFIED
        if ($user instanceof \App\Models\User) {
            if ($user->status_verifikasi != 'verified')
                return response()->json(['message' => 'Wajib verifikasi KTP!'], 403);
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
        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // 3. LOGIKA TOKO & PRE-ORDER
        $targetStoreId = null;

        if ($request->filled('store_id')) {
            // --- KASUS A: JUAL DI TOKO SENDIRI ---

            // Cek Kepemilikan
            $cekToko = \App\Models\Store::where('id', $request->store_id)->where('user_id', $user->id)->first();
            if (!$cekToko)
                return response()->json(['message' => 'Toko tidak valid'], 403);

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

        // 5. LOGIKA UPLOAD GAMBAR (ORIGINAL & THUMBNAIL)
        $namaFileGambar = null;

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension(); // Bikin nama unik (contoh: a7s8d6f.jpg)

            // Siapkan Manager Gambar
            $manager = new ImageManager(new Driver());

            // A. SIMPAN ORIGINAL
            // Kita simpan manual ke storage public
            $file->storeAs('products/originals', $filename, 'public');

            // B. SIMPAN THUMBNAIL (RESIZE)
            // Baca gambar
            $image = $manager->read($file);
            // Resize (Lebar 300px, Tinggi menyesuaikan rasio)
            $image->scale(width: 300);
            // Encode jadi file lagi
            $encoded = $image->toJpeg(80); // Kualitas 80%

            // Simpan ke folder thumbnails
            Storage::disk('public')->put('products/thumbnails/' . $filename, $encoded);

            $namaFileGambar = $filename; // Ini yang masuk DB
        }

        // 6. SIMPAN
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

            'gambar' => $namaFileGambar,
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
    public function destroy(Request $request, $id) // Tambahkan Request $request
    {
        $user = $request->user();

        // 1. Cari Produk
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        // 2. SECURITY CHECK (Wajib!)
        // Pastikan produk ini milik Toko, dan Toko itu milik User yang login
        // Asumsi: Relasi Product -> Store -> User
        $store = $product->store;

        // Cek: Apakah toko ada? Dan apakah pemilik toko = user yg login?
        if (!$store || $store->user_id !== $user->id) {
            return response()->json([
                'message' => 'Anda tidak berhak menghapus produk ini'
            ], 403); // 403 Forbidden (Dilarang)
        }

        // 3. Hapus Gambar Fisik (Biar server gak penuh sampah)
        // Asumsi kolom gambar bernama 'image'
        if ($product->image && Storage::exists('public/' . $product->image)) {
            Storage::delete('public/' . $product->image);
        }

        // 4. Hapus Data Database
        $product->delete();

        return response()->json(['message' => 'Produk berhasil dihapus']);
    }

    // 6. ALL PRODUCT
    public function myGroupedProducts()
    {
        $user = auth()->user();

        // 1. Ambil semua ID Toko milik user ini
        // Hasil: [1, 5, 8] (ID dari Waroeng Snack, Warteg Kopag, dll)
        $myStoreIds = \App\Models\Merchant::where('user_id', $user->id)->pluck('id');

        // 2. Query Produk
        $query = Product::query();

        if ($user->role === 'admin') {
            // Kalau Admin: Ambil produk di tokonya SENDIRI + Produk SIMART (store_id NULL)
            $query->where(function ($q) use ($myStoreIds) {
                $q->whereIn('store_id', $myStoreIds)
                    ->orWhereNull('store_id');
            });
        } else {
            // Kalau User Biasa: Ambil produk di toko-toko miliknya saja
            $query->whereIn('store_id', $myStoreIds);
        }

        // Load data toko (merchant) biar kita tahu nama tokonya
        $products = $query->with('merchant:id,shop_name')
            ->latest()
            ->get();

        // 3. GROUPING (Pengelompokan)
        // Kita kelompokkan berdasarkan Nama Toko
        $grouped = $products->groupBy(function ($product) {
            // Kalau store_id kosong, berarti punya SI MART
            if (!$product->store_id) {
                return 'SI MART (Pusat)';
            }
            // Kalau ada, ambil nama tokonya dari relasi
            return $product->merchant->shop_name ?? 'Toko Tidak Dikenal';
        });

        return response()->json([
            'message' => 'Data produk berhasil dikelompokkan',
            'data' => $grouped
        ]);
    }
}