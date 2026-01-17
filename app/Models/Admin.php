<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; // <--- TAMBAHAN 1
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable; // <--- TAMBAHAN 2

    // Pastikan nama tabel benar
    protected $table = 'admins';

    protected $fillable = [
        'identity_code',
        'username',
        'password',
        'plain_password',
        'nama_lengkap', // <--- PENTING: Kita pakai ini (bukan 'nama_admin')
        'role',
        'saldo',
        'pin',
    ];

    protected $hidden = [
        'password',
        'pin', // PIN harus disembunyikan saat return JSON
        'remember_token',
    ];

    // <--- TAMBAHAN 3: FITUR WAJIB (CASTS) --->
    // Agar saldo tidak error saat matematika & password aman
    protected $casts = [
        'saldo' => 'integer',
        'password' => 'hashed', // Fitur Hashing otomatis Laravel
        'pin' => 'string',      // PIN tetap string agar angka '0' di depan tidak hilang
    ];

    // Relasi Admin ke Produk
    public function products()
    {
        return $this->morphMany(Product::class, 'seller');
    }

    public function topUps()
    {
        // Relasi ke tabel top_ups berdasarkan admin_id
        return $this->hasMany(TopUp::class, 'admin_id');
    }
}