<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Validator;

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

        // Cek kepemilikan: Jangan sampai user A ngedit toko user B
        if (!$toko || $toko->user_id != $request->user()->id) {
            return response()->json(['message' => 'Toko tidak ditemukan atau bukan milik anda'], 404);
        }

        $toko->update($request->all()); // Update semua field yg dikirim

        return response()->json(['message' => 'Info toko diupdate', 'data' => $toko]);
    }
}