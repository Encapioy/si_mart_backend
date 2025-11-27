<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MultiAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 2. MATIKAN PENGECEKAN FOREIGN KEY SEMENTARA
        Schema::disableForeignKeyConstraints();

        // 3. Bersihkan tabel (Sekarang aman karena cek dimatikan)
        DB::table('admins')->truncate();

        // 4. NYALAKAN LAGI PENGECEKANNYA
        Schema::enableForeignKeyConstraints();

        $passwordDefault = Hash::make('admin123');
        $pinDefault = '123456';

        // Fungsi kecil buat generate ID Admin Unik: ADM + Random
        $genId = function () {
            return 'ADM-' . mt_rand(10000, 99999);
        };

        // ... (Kodingan create admin di bawahnya BIARKAN SAMA) ...
        Admin::create([
            'nama_lengkap' => 'Super Admin Pusat',
            'username' => 'admin_pusat',
            'password' => $passwordDefault,
            'pin' => $pinDefault,
            'role' => 'pusat',
            'saldo' => 0,
        ]);

        // ... dst (lanjutkan admin lainnya)
        Admin::create([
            'nama_lengkap' => 'Tim Developer IT',
            'username' => 'admin_dev',
            'password' => $passwordDefault,
            'pin' => $pinDefault,
            'role' => 'developer',
            'saldo' => 0,
        ]);

        Admin::create([
            'nama_lengkap' => 'Manager Toko Simart',
            'username' => 'admin_kasir',
            'password' => $passwordDefault,
            'pin' => $pinDefault,
            'role' => 'kasir',
            'saldo' => 5000000,
        ]);

        Admin::create([
            'nama_lengkap' => 'Bendahara Sekolah',
            'username' => 'admin_keuangan',
            'password' => $passwordDefault,
            'pin' => $pinDefault,
            'role' => 'keuangan',
            'saldo' => 100000000,
        ]);
    }
}