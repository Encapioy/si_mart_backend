<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'identity_code',
        'username',
        'password',
        'nama_lengkap',
        'role',
        'saldo',
        'pin',
    ];

    // Admin juga bisa punya banyak produk
    public function products()
    {
        return $this->morphMany(Product::class, 'seller');
    }

    protected $hidden = [
        'password', // Sembunyikan password
        'pin',
        'remember_token',
    ];
}