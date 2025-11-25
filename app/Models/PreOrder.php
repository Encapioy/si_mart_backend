<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreOrder extends Model
{
    use HasFactory;

    // Pastikan semua field ini ada agar bisa diisi oleh Controller
    protected $fillable = [
        'po_code',
        'user_id',
        'product_id',
        'qty',
        'total_bayar',
        'nama_penerima',
        'catatan',
        'status' // paid, ready, taken, cancelled
    ];

    // Relasi: Pesanan ini barangnya apa?
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi: Pesanan ini punya siapa?
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}