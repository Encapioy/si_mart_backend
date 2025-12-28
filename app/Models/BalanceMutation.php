<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceMutation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'current_balance',
        'category',
        'related_user_id',
        'description'
    ];

    // 1. Relasi ke Pemilik Saldo
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 2. Relasi ke Lawan Transaksi (Teman Transfer)
    // Ini berguna jika nanti kita mau menampilkan history lengkap dengan nama teman
    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }
}