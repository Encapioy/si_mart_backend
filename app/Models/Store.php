<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_toko',
        'kategori',
        'deskripsi',
        'lokasi',
        'gambar',
        'is_open'
    ];

    // Toko milik siapa?
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Toko punya banyak produk
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}