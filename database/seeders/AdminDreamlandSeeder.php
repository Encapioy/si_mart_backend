<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin; // Pastikan import Model Admin
use Illuminate\Support\Facades\Hash;

class AdminDreamlandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat Akun Admin Dreamland (Device Kiosk)
        Admin::updateOrCreate(
            ['username' => 'admin_dreamland'], // Cek berdasarkan username
            [
                'nama_lengkap' => 'Admin Dreamland',
                'password' => Hash::make('admin123'), // Password Login App
                'pin' => '000000', // PIN Default (Jaga-jaga kalau diminta)
                'role' => 'dreamland', // <--- PENTING: Role harus 'dreamland'
                'saldo' => 0, // Dreamland tidak perlu saldo, karena pakai saldo Kasir
            ]
        );
    }
}