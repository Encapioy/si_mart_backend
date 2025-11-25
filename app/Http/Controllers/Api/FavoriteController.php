<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class FavoriteController extends Controller
{
    // 1. TAMBAH / HAPUS FAVORIT (TOGGLE)
    // Kalau belum ada -> Jadi Ada (Like)
    // Kalau sudah ada -> Jadi Hilang (Unlike)
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $user = $request->user();
        $productId = $request->product_id;

        // Cek apakah produk ini khusus Simart? (Sesuai permintaanmu)
        $product = Product::find($productId);
        if ($product->store_id != null) {
            return response()->json(['message' => 'Hanya produk Simart yang bisa difavoritkan!'], 400);
        }

        // Cek apakah sudah ada di favorit?
        // toggle() adalah fungsi bawaan Laravel untuk Many-to-Many
        $result = $user->favorites()->toggle($productId);

        // $result['attached'] berisi array ID jika berhasil ditambahkan
        if (count($result['attached']) > 0) {
            return response()->json(['message' => 'Produk ditambahkan ke favorit', 'is_favorited' => true]);
        } else {
            return response()->json(['message' => 'Produk dihapus dari favorit', 'is_favorited' => false]);
        }
    }

    // 2. LIHAT LIST FAVORIT SAYA
    public function myFavorites(Request $request)
    {
        $user = $request->user();

        // Ambil produk yg disukai user
        $favorites = $user->favorites()->latest()->get();

        return response()->json([
            'message' => 'List Favorit Saya',
            'data' => $favorites
        ]);
    }
}