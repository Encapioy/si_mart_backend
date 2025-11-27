<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Information;

class UserDashboardController extends Controller
{
    // 1. DATA HOMEPAGE (Nama, Saldo, Foto, Notifikasi Belum Dibaca)
    public function home(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Data Homepage',
            'data' => [
                'nama_lengkap' => $user->nama_lengkap,
                'saldo' => $user->saldo, // Saldo Utama
                'profile_photo_url' => $user->profile_photo_url, // URL Foto yang tadi kita buat

                // (Opsional) Data tambahan biar Homepage makin kaya
                'member_id' => $user->member_id,
                'status_verified' => $user->status_verifikasi,
            ]
        ]);
    }

    // 2. DATA HALAMAN INFO (Promo, Pondok, Sistem)
    public function infos(Request $request)
    {
        // Fitur Filter Kategori ( ?kategori=promo )
        $query = Information::query();

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Ambil data terbaru
        $infos = $query->latest()->get()->map(function ($info) {
            return [
                'id' => $info->id,
                'judul' => $info->judul,
                'kategori' => $info->kategori,
                'tanggal' => $info->created_at->format('d M Y'), // Format tanggal cantik
                'gambar' => $info->gambar,
                'konten' => $info->konten,
                // Khusus Promo
                'kode_promo' => $info->kode_promo,
                'berlaku_sampai' => $info->berlaku_sampai,
            ];
        });

        return response()->json([
            'message' => 'List Informasi',
            'data' => $infos
        ]);
    }
}