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
                'image_update' => asset('img/update-banner.jpg'),

                // 5. Link Toko
                'download_url' => 'https://drive.google.com/drive/folders/1gc-pH9oYvdwCWIGLKtjZvsna-RqJ8n0R?usp=sharing'
            ]
        ]);
    }
}