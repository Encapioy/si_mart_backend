<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'status',
        'user_id',
        'total_bayar',
        'tanggal_transaksi',
        'expired_at'
    ];

    // Relasi: Transaksi ini milik siapa?
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Transaksi ini isinya barang apa saja?
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}