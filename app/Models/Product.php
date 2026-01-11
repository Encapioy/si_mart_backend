<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // 1. Definisikan kolom yang boleh diisi (Tambahkan 'deskripsi')
    protected $fillable = [
        'store_id',
        'barcode',
        'nama_produk',
        'deskripsi',   // <-- JANGAN LUPA INI
        'harga',
        'diskon',
        'stok',
        'gambar',
        'ingredients',
        'lokasi_rak',
        'seller_id',
        'seller_type'
    ];

    // Otomatis tambahkan URL gambar ke JSON response
    protected $appends = ['gambar_url'];

    // --- RELASI ---

    // Produk milik Toko
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Produk milik Seller (Polymorphic: Bisa User / Admin)
    public function seller()
    {
        return $this->morphTo();
    }

    // Relasi Favorit (User menyukai produk)
    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites', 'product_id', 'user_id');
    }

    // --- ACCESSOR (Logic Tambahan) ---

    // Hitung harga setelah diskon
    public function getHargaAkhirAttribute()
    {
        if ($this->diskon > 0) {
            $potongan = ($this->harga * $this->diskon) / 100;
            return $this->harga - $potongan;
        }
        return $this->harga;
    }

    // Cek status Like user login
    public function getIsFavoritedAttribute()
    {
        if (auth('sanctum')->check()) {
            return $this->favorites()->where('user_id', auth('sanctum')->id())->exists();
        }
        return false;
    }

    // URL Gambar yang Aman (Handle jika null)
    public function getGambarUrlAttribute()
    {
        if ($this->gambar) {
            // PASTIKAN: Path ini sesuai dengan tempat kamu menyimpan file saat upload.
            // Kalau uploadnya sederhana, biasanya cuma 'storage/' . $this->gambar
            return asset('storage/' . $this->gambar);
        }
        // Gambar default kalau produk gak punya foto
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->nama_produk) . '&background=random';
    }
}