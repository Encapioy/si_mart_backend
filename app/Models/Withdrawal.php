<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'bank_name',
        'account_number',
        'account_name',
        'admin_fee',
        'bukti_transfer_admin'
    ];

    // Relasi: Pengajuan ini punya siapa?
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}