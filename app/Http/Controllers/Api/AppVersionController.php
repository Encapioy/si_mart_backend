<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function checkVersion(Request $request)
    {
        // LOGIC: Nanti bisa diambil dari database jika mau dinamis

        return response()->json([
            'data' => [
                // 1. Versi Aplikasi
                'version' => '1.0.2',
                'build_number' => 12, // Penting untuk cek internal dev

                // 2. Status Update
                'force_update' => true, // True = Wajib Update, False = Boleh Skip
                'maintenance_mode' => false, // True = Server lagi perbaikan

                // 3. Konten Tampilan Popup (Sesuai Request)
                'title_update' => 'Waktunya Update! 🚀',
                'description_update' => "Halo Sobat SI Mart!\n\nDi versi terbaru ini kami menghadirkan:\n✅ Fitur Transfer Antar Teman\n✅ Perbaikan Bug pada QRIS\n✅ Tampilan Lebih Fresh\n\nYuk update sekarang biar transaksi makin lancar!",

                // 4. Gambar Ilustrasi Update
                // Pastikan kamu taruh gambar 'update-banner.png' di folder public/img/
                'image_update' => asset('img/update-banner.png'),

                // 5. Link Toko
                'play_store_url' => 'https://play.google.com/store/apps/details?id=com.sekolahimpian.smartsimart',
                'app_store_url' => 'https://apps.apple.com/id/app/smart-si-mart/id123456789'
            ]
        ]);
    }
}