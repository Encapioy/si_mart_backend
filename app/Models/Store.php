<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    // Pastikan nama tabel benar. Kalau tabelmu namanya 'merchants', buka komen di bawah:
    // protected $table = 'merchants';

    protected $fillable = [
        'user_id',
        'nama_toko', // Perhatikan ini
        'kategori',
        'deskripsi',
        'lokasi',
        'gambar',
        'is_open'
    ];

    // --- TAMBAHAN PENTING 1: Casting ---
    // Biar 'is_open' otomatis jadi true/false (bukan 1/0) saat dikirim ke Flutter/Web
    protected $casts = [
        'is_open' => 'boolean',
    ];

    // --- TAMBAHAN PENTING 2: Accessor ---
    // Supaya $store->name di Controller tetap jalan walaupun kolom aslinya nama_toko
    public function getNameAttribute()
    {
        return $this->nama_toko;
    }

    // Toko milik siapa?
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Toko punya banyak produk
    public function products()
    {
        // Pastikan tabel products punya kolom 'store_id'
        return $this->hasMany(Product::class, 'store_id');
    }
}