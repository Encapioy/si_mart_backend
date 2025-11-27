<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'barcode',
        'nama_produk',
        'harga',
        'diskon',
        'stok',
        'gambar',
        'ingredients',
        'lokasi_rak',
        'seller_id',
        'seller_type'
    ];

    // Relasi: Produk ini milik siapa? (Bisa User, Bisa Admin)
    public function seller()
    {
        return $this->morphTo();
    }

    // --- FITUR HITUNG HARGA DISKON ---
    // Ini helper function biar di controller gak ribet ngitungnya
    public function getHargaAkhirAttribute()
    {
        if ($this->diskon > 0) {
            $potongan = ($this->harga * $this->diskon) / 100;
            return $this->harga - $potongan;
        }
        return $this->harga;
    }

    // Cek apakah produk ini sedang difavoritkan oleh user yg sedang login?
    // Fitur ini penting untuk Frontend (menyalakan ikon hati ❤️)
    public function getIsFavoritedAttribute()
    {
        // Jika ada user login, cek apakah dia ada di list user yg me-like produk ini
        if (auth('sanctum')->check()) {
            return $this->favorites()->where('user_id', auth('sanctum')->id())->exists();
        }
        return false;
    }

    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites', 'product_id', 'user_id');
    }

    protected $appends = ['gambar_url', 'gambar_thumb_url'];

    // 1. URL GAMBAR ASLI (Detail Produk)
    public function getGambarUrlAttribute()
    {
        if ($this->gambar) {
            return asset('storage/products/originals/' . $this->gambar);
        }
        return null; // Atau URL gambar default
    }

    // 2. URL GAMBAR KECIL (List Produk)
    public function getGambarThumbUrlAttribute()
    {
        if ($this->gambar) {
            return asset('storage/products/thumbnails/' . $this->gambar);
        }
        return null;
    }
}