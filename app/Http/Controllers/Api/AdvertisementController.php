<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advertisement;
use App\Models\User;
use App\Models\BalanceMutation; // Jangan lupa import ini
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AdvertisementController extends Controller
{
    // Konstanta Harga dan Durasi
    const AD_PRICE = 10000;
    const DURATION_HOURS = 3;

    // ======================================================
    // 1. PASANG IKLAN BARU (STORE)
    // ======================================================
    public function store(Request $request)
    {
        $user = $request->user();

        // A. Validasi Input
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id',
            // Validasi gambar: wajib ada, format gambar, maks 2MB
            'banner_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'title' => 'required|string|max:50',
            'caption' => 'required|string|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // B. Cek Saldo Cukup Gak?
        if ($user->saldo < self::AD_PRICE) {
            return response()->json(['message' => 'Saldo tidak cukup! Biaya pasang iklan Rp ' . number_format(self::AD_PRICE)], 400);
        }

        // C. Cek Kepemilikan Toko
        // Pastikan user mengiklankan tokonya sendiri
        $store = \App\Models\Store::where('id', $request->store_id)->where('user_id', $user->id)->first();
        if (!$store) {
            return response()->json(['message' => 'Toko tidak valid atau bukan milik Anda.'], 403);
        }

        return DB::transaction(function () use ($request, $user) {
            // 1. Potong Saldo
            $user->saldo -= self::AD_PRICE;
            $user->save();

            // 2. Catat Mutasi (Debit)
            BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => self::AD_PRICE,
                'current_balance' => $user->saldo,
                'category' => 'ads', // Kategori baru: Iklan
                'description' => 'Pasang Iklan: ' . $request->title,
            ]);

            // 3. Upload Gambar
            $manager = new ImageManager(new Driver());
            $file = $request->file('banner_image');

            // Generate Nama Unik Dasar
            $baseFilename = 'AD-' . uniqid();
            $ext = $file->getClientOriginalExtension();

            // ---------------------------------------------
            // A. VERSI ORIGINAL (Simpan apa adanya)
            // ---------------------------------------------
            $originalName = $baseFilename . '-original.' . $ext;
            Storage::disk('public')->putFileAs('ads', $file, $originalName);

            // ---------------------------------------------
            // B. VERSI MEDIUM (Untuk Tampilan Default)
            // ---------------------------------------------
            // Resize lebar ke 800px (tinggi auto), convert ke WebP biar ringan
            $mediumName = $baseFilename . '-mid.webp';
            $imgMid = $manager->read($file);
            $imgMid->scale(width: 800);
            Storage::disk('public')->put('ads/' . $mediumName, (string) $imgMid->toWebp(75)); // Kualitas 75%

            // ---------------------------------------------
            // C. VERSI LOW (Untuk Placeholder Blur)
            // ---------------------------------------------
            // Resize sangat kecil (lebar 50px). Nanti di Frontend di-stretch (blur)
            $lowName = $baseFilename . '-low.webp';
            $imgLow = $manager->read($file);
            $imgLow->scale(width: 50);
            Storage::disk('public')->put('ads/' . $lowName, (string) $imgLow->toWebp(20)); // Kualitas 20%

            // 4. Buat Data Iklan
            $ad = Advertisement::create([
                'user_id' => $user->id,
                'store_id' => $request->store_id,
                'banner_original' => $originalName,
                'banner_medium' => $mediumName,
                'banner_low' => $lowName,
                'title' => $request->title,
                'caption' => $request->caption,
                'start_time' => now(),
                'end_time' => now()->addHours(self::DURATION_HOURS), // 3 Jam dari sekarang
                'status' => 'active',
                'is_notified' => false
            ]);

            return response()->json([
                'message' => 'Iklan berhasil dipasang! Tayang selama 3 jam.',
                'data' => $ad,
                'sisa_saldo' => $user->saldo
            ], 201);
        });
    }

    // ======================================================
    // 2. PERPANJANG IKLAN (RENEW)
    // ======================================================
    public function renew(Request $request, $id)
    {
        $user = $request->user();
        $ad = Advertisement::find($id);

        // Validasi
        if (!$ad) {
            return response()->json(['message' => 'Iklan tidak ditemukan'], 404);
        }
        if ($ad->user_id !== $user->id) {
            return response()->json(['message' => 'Ini bukan iklan Anda!'], 403);
        }

        // Iklan cuma bisa diperbarui kalau statusnya Active atau Grace Period
        // Kalau sudah Expired, suruh bikin baru aja (opsional, tergantung kebijakan)
        if ($ad->status == 'expired') {
            return response()->json(['message' => 'Iklan sudah kadaluarsa. Silakan pasang baru.'], 400);
        }

        // Cek Saldo Lagi
        if ($user->saldo < self::AD_PRICE) {
            return response()->json(['message' => 'Saldo tidak cukup untuk perpanjang iklan.'], 400);
        }

        return DB::transaction(function () use ($ad, $user) {
            // 1. Potong Saldo
            $user->saldo -= self::AD_PRICE;
            $user->save();

            // 2. Catat Mutasi
            BalanceMutation::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => self::AD_PRICE,
                'current_balance' => $user->saldo,
                'category' => 'ads',
                'description' => 'Perpanjang Iklan (3 Jam)',
            ]);

            // 3. Update Waktu Iklan
            // Logika: Tambah 3 jam dari waktu berakhir sebelumnya
            // Jadi kalau renew pas menit ke-10 grace period, durasi total tetap nyambung rapi.
            $ad->end_time = $ad->end_time->addHours(self::DURATION_HOURS);

            // Reset Status & Notifikasi
            $ad->status = 'active';
            $ad->is_notified = false; // Reset biar nanti dinotif lagi pas mau habis
            $ad->save();

            return response()->json([
                'message' => 'Iklan berhasil diperpanjang 3 jam!',
                'data' => $ad,
                'new_end_time' => $ad->end_time,
                'sisa_saldo' => $user->saldo
            ]);
        });
    }

    // ======================================================
    // 3. LIST IKLAN (UNTUK BERANDA USER)
    // ======================================================
    public function index()
    {
        // Hanya tampilkan yang Active atau Grace Period
        // Urutkan secara acak (Random) biar adil bagi semua merchant
        $ads = Advertisement::whereIn('status', ['active', 'grace_period'])
            ->where('end_time', '>', now())
            ->with(['store:id,user_id,nama_toko,gambar', 'store.owner:id,nama_lengkap'])
            ->inRandomOrder()
            ->get()
            ->map(function ($ad) {
                return [
                    'id' => $ad->id,

                    // OUTPUT 3 VERSI GAMBAR
                    'banner' => [
                        'low' => asset('storage/ads/' . $ad->banner_low),      // Size ~1KB (Instan)
                        'medium' => asset('storage/ads/' . $ad->banner_medium),   // Size ~100KB (Cepat)
                        'original' => asset('storage/ads/' . $ad->banner_original), // Size ~2MB (Detail)
                    ],

                    'title' => $ad->title,
                    'caption' => $ad->caption,
                    'end_time' => $ad->end_time,
                    'toko' => [
                        'nama' => $ad->store->nama_toko,
                        'gambar_url' => $ad->store->gambar ? asset('storage/stores/' . $ad->store->gambar) : null,
                        'owner' => $ad->store->owner->nama_lengkap ?? 'Unknown'
                    ]
                ];
            });

        return response()->json([
            'message' => 'List iklan aktif',
            'data' => $ads
        ]);
    }

    // ======================================================
    // 4. LIST IKLAN SAYA YANG SEDANG TAYANG (MERCHANT)
    // ======================================================
    public function myActiveAds(Request $request)
    {
        $user = $request->user();

        // Ambil iklan milik user yang sedang login
        // Syarat: Status Active/Grace Period DAN Waktunya belum habis
        $ads = Advertisement::where('user_id', $user->id)
            ->whereIn('status', ['active', 'grace_period'])
            ->where('end_time', '>', now())
            ->with('store:id,nama_toko,gambar') // Load data toko sekalian
            ->latest()
            ->get()
            ->map(function ($ad) {
                // Format output lengkap dengan 3 resolusi gambar
                return [
                    'id' => $ad->id,
                    'title' => $ad->title,
                    'caption' => $ad->caption,

                    // Detail Waktu
                    'start_time' => $ad->start_time->format('d M Y H:i'),
                    'end_time' => $ad->end_time->format('d M Y H:i'),
                    'sisa_waktu' => $ad->end_time->diffForHumans(), // Contoh output: "1 hour from now"

                    // Status
                    'status' => $ad->status,

                    // 3 Versi Gambar
                    'banner' => [
                        'low' => asset('storage/ads/' . $ad->banner_low),
                        'medium' => asset('storage/ads/' . $ad->banner_medium),
                        'original' => asset('storage/ads/' . $ad->banner_original),
                    ],

                    // Info Toko
                    'toko' => [
                        'id' => $ad->store->id,
                        'nama' => $ad->store->nama_toko,
                        'gambar' => $ad->store->gambar ? asset('storage/stores/' . $ad->store->gambar) : null,
                    ]
                ];
            });

        return response()->json([
            'message' => 'List iklan Anda yang sedang tayang',
            'data' => $ads
        ]);
    }

    // ======================================================
    // 5. RIWAYAT SEMUA IKLAN SAYA (History - Termasuk Expired)
    // ======================================================
    public function myAdsHistory(Request $request)
    {
        $user = $request->user();

        // Ambil SEMUA iklan milik user (Tanpa filter active/time)
        $ads = Advertisement::where('user_id', $user->id)
            ->with('store:id,nama_toko,gambar') // Tetap load toko
            ->latest()
            ->get()
            ->map(function ($ad) {
                // KITA PAKAI FORMAT YANG SAMA BIAR FRONTEND GAK BINGUNG
                return [
                    'id' => $ad->id,
                    'title' => $ad->title,
                    'caption' => $ad->caption,

                    // Detail Waktu
                    'start_time' => $ad->start_time->format('d M Y H:i'),
                    'end_time' => $ad->end_time->format('d M Y H:i'),

                    // Status (Penting buat history biar tau mana yang expired)
                    'status' => $ad->status,

                    // Tetap sediakan 3 gambar lengkap
                    'banner' => [
                        'low' => $ad->banner_low ? asset('storage/ads/' . $ad->banner_low) : null,
                        'medium' => $ad->banner_medium ? asset('storage/ads/' . $ad->banner_medium) : null,
                        'original' => asset('storage/ads/' . $ad->banner_original),
                    ],

                    'toko' => [
                        'id' => $ad->store->id,
                        'nama' => $ad->store->nama_toko,
                        'gambar' => $ad->store->gambar ? asset('storage/stores/' . $ad->store->gambar) : null,
                    ]
                ];
            });

        return response()->json([
            'message' => 'Riwayat semua iklan Anda',
            'data' => $ads
        ]);
    }
}