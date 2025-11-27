<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'member_id',
        'nama_lengkap',
        'profile_photo',
        'username',
        'email',
        'password',
        'pin',
        'nfc_id',
        'saldo',
        'parent_id',
        'nik',
        'alamat_ktp',
        'foto_ktp',
        'foto_selfie_ktp',
        'status_verifikasi'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'pin', // Sembunyikan PIN agar tidak bocor di API
    ];

    // Tambahkan Accessor (Otomatis generate URL lengkap)
    // Jadi nanti di JSON muncul field baru: "profile_photo_url"
    protected $appends = ['profile_photo_url'];

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo) {
            return asset('storage/' . $this->profile_photo);
        }
        return null; // Atau bisa return URL gambar default/avatar placeholder
    }

    // Relasi: User ini adalah anak dari siapa? (Milik 1 Orang Tua)
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Relasi: User ini punya anak siapa saja? (Punya Banyak Anak)
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    // User bisa punya banyak produk (sebagai merchant)
    public function products()
    {
        return $this->morphMany(Product::class, 'seller');
    }

    // User bisa punya banyak toko
    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    // List produk yang disukai user ini
    public function favorites()
    {
        return $this->belongsToMany(Product::class, 'favorites', 'user_id', 'product_id')->withTimestamps();
    }
}