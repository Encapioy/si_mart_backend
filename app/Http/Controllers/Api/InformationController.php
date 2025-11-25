<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Information;
use Illuminate\Support\Facades\Validator;

class InformationController extends Controller
{
    // 1. PUBLIC: LIHAT SEMUA INFORMASI (Untuk Homepage HP)
    public function index(Request $request)
    {
        // Bisa filter by kategori ?kategori=promo
        $query = Information::query();

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Urutkan dari yang terbaru
        $infos = $query->with('admin:id,nama_lengkap')->latest()->get();

        return response()->json([
            'message' => 'List Informasi',
            'data' => $infos
        ]);
    }

    // 2. ADMIN PUSAT: BUAT INFORMASI BARU
    public function store(Request $request)
    {
        // Cek Role Admin Pusat
        $user = $request->user();
        if (!($user instanceof \App\Models\Admin) || $user->role !== 'pusat') {
            return response()->json(['message' => 'Hanya Admin Pusat yang boleh posting info!'], 403);
        }

        // Validasi Dasar
        $rules = [
            'judul' => 'required|string',
            'kategori' => 'required|in:promo,pondok,sistem',
            'konten' => 'required|string',
            'gambar' => 'nullable|string', // Nanti bisa diganti image upload
        ];

        // Validasi Tambahan Khusus Promo
        if ($request->kategori == 'promo') {
            $rules['kode_promo'] = 'required|string';
            $rules['berlaku_sampai'] = 'required|date';
            $rules['syarat_ketentuan'] = 'required|string';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails())
            return response()->json($validator->errors(), 400);

        // Simpan
        $info = Information::create([
            'admin_id' => $user->id,
            'judul' => $request->judul,
            'kategori' => $request->kategori,
            'konten' => $request->konten,
            'gambar' => $request->gambar,
            'kode_promo' => $request->kode_promo,
            'berlaku_sampai' => $request->berlaku_sampai,
            'syarat_ketentuan' => $request->syarat_ketentuan,
        ]);

        return response()->json(['message' => 'Informasi berhasil diposting', 'data' => $info], 201);
    }

    // 3. ADMIN PUSAT: HAPUS INFORMASI
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!($user instanceof \App\Models\Admin) || $user->role !== 'pusat') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $info = Information::find($id);
        if (!$info)
            return response()->json(['message' => 'Data not found'], 404);

        $info->delete();
        return response()->json(['message' => 'Informasi dihapus']);
    }
}