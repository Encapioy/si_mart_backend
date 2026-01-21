<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'banner_original',
        'banner_medium',
        'banner_low',
        'title',
        'caption',
        'start_time',
        'end_time',
        'status',
        'is_notified'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_notified' => 'boolean',
    ];

    // Relasi ke Toko (Untuk ambil nama toko saat ditampilkan)
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Relasi ke User (Pemilik)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}