<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // Kita pakai driver GD (bawaan PHP)
use Illuminate\Support\Facades\Storage;
use App\Models\Store;
use App\Models\Admin;

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
            'store_id' => 'nullable|exists:stores,id',
            'is_preorder' => 'boolean',
            'po_estimasi' => 'required_if:is_preorder,true|date',
            'po_kuota' => 'required_if:is_preorder,true|integer',
            'lokasi_rak' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120', // Max 5MB
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // 3. LOGIKA TOKO & HARGA (Tetap Sama)
        $targetStoreId = null;

        if ($request->filled('store_id')) {
            // --- KASUS A: JUAL DI TOKO SENDIRI ---
            $cekToko = Store::where('id', $request->store_id)->where('user_id', $user->id)->first();
            if (!$cekToko)
                return response()->json(['message' => 'Toko tidak valid'], 403);

            $targetStoreId = $request->store_id;
            if ($request->boolean('is_preorder')) {
                return response()->json(['message' => 'Toko sendiri tidak boleh jualan Pre-Order!'], 400);
            }
            $hargaModal = $request->harga;
            $hargaJual = $request->harga;
        } else {
            // --- KASUS B: JUAL DI SIMART (TITIP) ---
            $hargaModal = $request->harga;
            if ($user instanceof Admin) {
                $hargaJual = $hargaModal;
            } else {
                $targetReceive = $hargaModal + 100;
                $rawPrice = $targetReceive / 0.85;
                $hargaJual = ceil($rawPrice / 500) * 500;
            }
        }

        // 4. LOGIKA BARCODE
        $final_barcode = $request->filled('barcode') ? $request->barcode : 'SIS-' . mt_rand(100000, 999999);

        // 5. LOGIKA UPLOAD GAMBAR (REVISI: EXPLICIT NAMING)
        $namaFileGambar = null;

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');

            // Buat Nama Dasar Unik
            $baseName = 'PROD-' . uniqid();

            try {
                // Gunakan Intervention Image
                $manager = new ImageManager(new Driver());

                // A. Proses Thumbnail (Resize & Convert ke WebP)
                $imgThumb = $manager->read($file)->scale(width: 300)->toWebp(80);

                // B. Proses Original (Convert ke WebP biar hemat storage & konsisten)
                $imgOriginal = $manager->read($file)->toWebp(90);

                // Tentukan Nama File Akhir (Pasti .webp)
                $finalName = $baseName . '.webp';

                // Simpan Fisik
                Storage::disk('public')->put('products/thumbnails/' . $finalName, (string) $imgThumb);
                Storage::disk('public')->put('products/originals/' . $finalName, (string) $imgOriginal);

                $namaFileGambar = $finalName;

            } catch (\Exception $e) {
                // Fallback: Jika server gagal proses image, simpan original apa adanya
                $ext = $file->getClientOriginalExtension();
                $fallbackName = $baseName . '.' . $ext;

                Storage::disk('public')->putFileAs('products/originals', $file, $fallbackName);
                // Copy ke thumbnail juga biar gak error saat dipanggil
                Storage::disk('public')->copy('products/originals/' . $fallbackName, 'products/thumbnails/' . $fallbackName);

                $namaFileGambar = $fallbackName;
            }
        }

        // 6. SIMPAN DB
        $product = Product::create([
            'barcode' => $final_barcode,
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'harga' => $hargaJual,
            'harga_modal' => $hargaModal,
            'diskon' => $request->diskon ?? 0,
            'stok' => $request->stok,
            'lokasi_rak' => $request->lokasi_rak,
            'is_preorder' => $request->boolean('is_preorder'),
            'po_estimasi' => $request->po_estimasi,
            'po_kuota' => $request->po_kuota,
            'gambar' => $namaFileGambar, // Nama file sudah pasti sinkron dengan fisik
            'ingredients' => $request->ingredients,
            'seller_id' => $user->id,
            'seller_type' => get_class($user),
            'store_id' => $targetStoreId,
        ]);

        // Refresh agar image_url (accessor) terload jika ada
        $product->refresh();

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
        // 1. CARI PRODUK
        $product = Product::find($id);
        if (!$product)
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);

        // 2. CEK KEPEMILIKAN
        $user = $request->user();
        if ($user->role !== 'admin' && $user->role !== 'kasir' && $product->seller_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak berhak mengedit produk ini!'], 403);
        }

        // 3. VALIDASI
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'sometimes|string|max:255',
            'barcode' => 'sometimes|string|unique:products,barcode,' . $product->id,
            'harga' => 'sometimes|numeric|min:0',
            'harga_modal' => 'sometimes|numeric|min:0',
            'stok' => 'sometimes|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'store_id' => 'nullable|exists:stores,id'
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // 4. LOGIKA UPDATE GAMBAR (REVISI: EXPLICIT NAMING)
        $namaFileGambar = $product->gambar; // Default pakai lama

        if ($request->hasFile('gambar')) {
            // A. Hapus Gambar Lama
            if ($product->gambar) {
                Storage::disk('public')->delete('products/originals/' . $product->gambar);
                Storage::disk('public')->delete('products/thumbnails/' . $product->gambar);
            }

            // B. Upload Gambar Baru (Logic sama persis dengan Store)
            $file = $request->file('gambar');
            $baseName = 'PROD-' . uniqid();

            try {
                $manager = new ImageManager(new Driver());

                // Resize Thumbnail & Convert Original
                $imgThumb = $manager->read($file)->scale(width: 300)->toWebp(80);
                $imgOriginal = $manager->read($file)->toWebp(90);

                $finalName = $baseName . '.webp';

                Storage::disk('public')->put('products/thumbnails/' . $finalName, (string) $imgThumb);
                Storage::disk('public')->put('products/originals/' . $finalName, (string) $imgOriginal);

                $namaFileGambar = $finalName;

            } catch (\Exception $e) {
                // Fallback
                $ext = $file->getClientOriginalExtension();
                $fallbackName = $baseName . '.' . $ext;

                Storage::disk('public')->putFileAs('products/originals', $file, $fallbackName);
                Storage::disk('public')->copy('products/originals/' . $fallbackName, 'products/thumbnails/' . $fallbackName);

                $namaFileGambar = $fallbackName;
            }
        }

        // 5. UPDATE DB
        $product->update([
            'nama_produk' => $request->input('nama_produk', $product->nama_produk),
            'barcode' => $request->input('barcode', $product->barcode),
            'harga' => $request->input('harga', $product->harga),
            'harga_modal' => $request->input('harga_modal', $product->harga_modal),
            'stok' => $request->input('stok', $product->stok),
            'deskripsi' => $request->input('deskripsi', $product->deskripsi),
            'gambar' => $namaFileGambar,
            'store_id' => $request->input('store_id', $product->store_id),
        ]);

        // Refresh data untuk response terbaru
        $product->refresh();

        return response()->json([
            'message' => 'Produk berhasil diperbarui',
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