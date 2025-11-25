<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    use HasFactory;

    // Kita definisikan nama tabelnya eksplisit (karena bahasa inggris 'information' tidak ada plural 's')
    protected $table = 'informations';

    protected $fillable = [
        'admin_id',
        'judul',
        'kategori',
        'konten',
        'gambar',
        'kode_promo',
        'berlaku_sampai',
        'syarat_ketentuan'
    ];

    // Relasi: Siapa yang posting
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}