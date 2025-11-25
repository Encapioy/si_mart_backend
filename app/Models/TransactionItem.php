<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'qty',
        'harga_saat_itu'
    ];

    // Relasi: Item ini bagian dari transaksi mana?
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi: Item ini sebenarnya produk apa?
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}