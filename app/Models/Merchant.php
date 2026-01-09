<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi: Toko milik User siapa?
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Toko punya banyak produk (Opsional, buat nanti)
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Helper: Cek apakah toko aktif
    public function isActive()
    {
        return $this->status === 'approved';
    }
}