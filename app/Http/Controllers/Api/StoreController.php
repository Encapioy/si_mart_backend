<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Merchant;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Transaction;
use Carbon\Carbon;

class StoreController extends Controller
{
    // 1. BUAT TOKO BARU
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_toko' => 'required|string|max:50',
            'kategori' => 'required|string',
        ]);

        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        $user = $request->user();

        // Pastikan user verified sebelum bikin toko
        if ($user->status_verifikasi != 'verified') {
            return response()->json(['message' => 'Akun wajib verified untuk membuat toko'], 403);
        }

        $toko = Store::create([
            'user_id' => $user->id,
            'nama_toko' => $request->nama_toko,
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'lokasi' => $request->lokasi,
            'gambar' => $request->gambar,
        ]);

        return response()->json(['message' => 'Toko berhasil dibuat', 'data' => $toko], 201);
    }

    // 2. LIHAT TOKO SAYA (List Toko milik user login)
    public function myStores(Request $request)
    {
        $stores = Store::where('user_id', $request->user()->id)->get();
        return response()->json(['data' => $stores]);
    }

    // 3. UPDATE INFO TOKO (Buka/Tutup, Ganti Nama, dll)
    public function update(Request $request, $id)
    {
        $toko = Store::find($id);

        // 1. Cek kepemilikan
        if (!$toko || $toko->user_id != $request->user()->id) {
            return response()->json(['message' => 'Toko tidak ditemukan atau bukan milik anda'], 404);
        }

        // 2. Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'is_open' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // 3. Siapkan data update (kecuali gambar dulu)
        $dataToUpdate = $request->except(['image']);

        // Pastikan status toko jadi boolean (true/false)
        if ($request->has('is_open')) {
            $dataToUpdate['is_open'] = filter_var($request->is_open, FILTER_VALIDATE_BOOLEAN);
        }

        // 4. Handle Gambar (Hapus lama, simpan baru)
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada di storage
            if ($toko->image_path && Storage::disk('public')->exists($toko->image_path)) {
                Storage::disk('public')->delete($toko->image_path);
            }

            // Upload gambar baru
            $path = $request->file('image')->store('stores', 'public');
            $dataToUpdate['image_path'] = $path;
        }

        // 5. Eksekusi Update
        $toko->update($dataToUpdate);

        // Tambahkan URL gambar untuk response balik ke Flutter
        $toko->image_url = $toko->image_path ? url('storage/' . $toko->image_path) : null;

        return response()->json([
            'message' => 'Info toko berhasil diperbarui',
            'data' => $toko
        ]);
    }

    // 4. LIST SEMUA TOKO
    public function index(Request $request)
    {
        // Ambil toko yang statusnya SUDAH DISETUJUI (Approved)
        $query = Merchant::where('status', 'approved');

        // Filter Pencarian Nama Toko
        if ($request->has('search')) {
            $query->where('shop_name', 'like', '%' . $request->search . '%');
        }

        // Filter Toko Buka/Tutup (Opsional, kalau ada kolom is_open)
        // if ($request->has('is_open')) {
        //    $query->where('is_open', $request->is_open);
        // }

        $stores = $query->with('user:id,name,username') // Load data pemilik
            ->orderBy('is_open', 'desc') // Yang buka taruh atas (kalau ada kolom is_open)
            ->get();

        return response()->json($stores);
    }

    // 5. DETAIL SATU TOKO (Header Halaman Toko)
    public function show($id)
    {
        // PERBAIKAN 1: Tambahkan ->with('products')
        // Ini artinya: "Cari Toko ID sekian, DAN tolong bawakan semua produk dia sekalian"
        // Pastikan modelnya 'Store' ya, sesuai konsep "Banyak Cabang" tadi.
        // Kalau kamu masih pakai 'Merchant' sebagai toko, sesuaikan saja namanya.
        $store = Store::with('products')
            ->where('is_open', true) // Opsional: Cuma tampilkan toko yang buka
            ->findOrFail($id);

        return response()->json([
            'message' => 'Detail toko berhasil diambil',
            'data' => $store,
            // Di dalam object $store ini nanti otomatis ada field 'products': [...]

            // Statistik tambahan (Opsional, tapi bagus buat UI)
            'stats' => [
                'total_products' => $store->products->count(), // Hitung dari data yg sudah diambil
            ]
        ]);
    }

    // 6. DETAIL TOKO (FULL)
    public function myStoreDetail($id)
    {
        $user = auth()->user();

        // 1. Cari Toko & Pastikan Milik User yang Login
        $store = Merchant::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$store) {
            return response()->json(['message' => 'Toko tidak ditemukan atau bukan milik Anda.'], 404);
        }

        // 2. Hitung Penghasilan HARI INI untuk toko ini saja
        // Asumsi tabel transactions punya kolom 'store_id' dan 'amount'
        // Status transaksi harus 'success'
        $todayIncome = Transaction::where('store_id', $store->id)
            ->where('status', 'success')
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        // 3. Hitung Jumlah Transaksi Hari Ini (Opsional, biar keren)
        $todayCount = Transaction::where('store_id', $store->id)
            ->where('status', 'success')
            ->whereDate('created_at', Carbon::today())
            ->count();

        return response()->json([
            'store_info' => $store, // Data toko (Nama, gambar, lokasi)
            'statistics' => [
                'income_today' => $todayIncome, // Rp 150.000
                'transaction_count' => $todayCount // 12 Transaksi
            ]
        ]);
    }

    // 7. GENERATE QR STORE
    public function generateQrCode(Request $request, $id)
    {
        // 1. Cari Toko
        $store = Store::find($id);

        // 2. Validasi (Toko ada & Milik User yg login)
        if (!$store || $store->user_id != $request->user()->id) {
            return response()->json(['message' => 'Toko tidak ditemukan atau bukan milik Anda'], 404);
        }

        // 3. Racik Payload
        // PERBAIKAN: Ganti $store->name jadi $store->nama_toko
        // TIPS: Kita hapus karakter ':' di nama toko biar gak ngerusak format separator payload
        $cleanName = str_replace(':', '', $store->nama_toko);

        $rawData = "SIPAY:STORE:" . $store->id . ":" . $cleanName;

        return response()->json([
            'status' => 'success',
            'data' => [
                'store_id' => $store->id,

                // PERBAIKAN: Gunakan nama_toko
                'store_name' => $store->nama_toko,

                'qr_payload' => $rawData,

                'description' => 'Scan QR ini untuk membayar di ' . $store->nama_toko
            ]
        ]);
    }

    // 8. PEMASUKAN TOKO
    public function getIncomeReport(Request $request, $id)
    {

        $user = $request->user();

        // 1. Ambil Toko milik User yg login
        $store = Store::where('id', $id)
            ->where('user_id', $user->id)
            ->first();


        if (!$store) {
            return response()->json(['message' => 'Anda tidak memiliki toko'], 404);
        }

        $baseQuery = Transaction::where('store_id', $store->id)
            ->where('status', 'paid');

        // 2. Query Data Hari Ini
        $todayTransactions = (clone $baseQuery)
            ->whereDate('created_at', Carbon::today())
            ->get();

        // 3. Query Data Bulan Ini
        $monthTransactions = (clone $baseQuery)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->get();

        // 4. Query Data Selamanya (ALL TIME) - BARU!
        $allTimeIncome = (clone $baseQuery)->sum('total_bayar');
        $allTimeCount = (clone $baseQuery)->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'today' => [
                    'total_income' => $todayTransactions->sum('total_bayar'), // Pake kolom total_bayar
                    'transaction_count' => $todayTransactions->count(),
                    'details' => $todayTransactions // List transaksinya
                ],
                'this_month' => [
                    'total_income' => $monthTransactions->sum('total_bayar'),
                    'transaction_count' => $monthTransactions->count(),
                    'details' => $monthTransactions
                ],
                'all_time' => [
                    'total_income' => (int) $allTimeIncome,
                    'transaction_count' => $allTimeCount,
                ]
            ]
        ]);
    }
}