<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MultiAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bersihkan tabel admin lama (biar gak bingung)
        DB::table('admins')->truncate();

        $passwordDefault = Hash::make('admin123'); // Password sama semua biar gampang tes

        // 2. Buat Admin Pusat (Saldo 0 / Disembunyikan)
        Admin::create([
            'nama_lengkap' => 'Super Admin Pusat',
            'username' => 'admin_pusat',
            'password' => $passwordDefault,
            'role' => 'pusat',
            'saldo' => 0,
        ]);

        // 3. Buat Admin Developer (Saldo Aktif utk Pinalti)
        Admin::create([
            'nama_lengkap' => 'Tim Developer IT',
            'username' => 'admin_dev',
            'password' => $passwordDefault,
            'role' => 'developer',
            'saldo' => 0, // Awal 0, nanti nambah dari pinalti
        ]);

        // 4. Buat Admin Kasir (Saldo Aktif utk Profit Simart)
        Admin::create([
            'nama_lengkap' => 'Manager Toko Simart',
            'username' => 'admin_kasir',
            'password' => $passwordDefault,
            'role' => 'kasir',
            'saldo' => 5000000, // Modal awal toko
        ]);

        // 5. Buat Admin Keuangan (Saldo Aktif utk Brankas)
        Admin::create([
            'nama_lengkap' => 'Bendahara Sekolah',
            'username' => 'admin_keuangan',
            'password' => $passwordDefault,
            'role' => 'keuangan',
            'saldo' => 100000000, // Modal besar untuk operasional
        ]);
    }
}