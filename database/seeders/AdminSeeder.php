<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash; // Wajib import ini untuk enkripsi password

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'), // Password otomatis di-hash aman
            'nama_lengkap' => 'Super Admin Sekolah',
            'saldo' => 10000000, // Kita kasih modal awal 10 Juta biar bisa topup murid
        ]);
    }
}