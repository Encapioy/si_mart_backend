<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class SimartManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['username' => 'admin_simart'], // Key unik
            [
                'nama_lengkap' => 'Manager Toko Simart',
                'password' => Hash::make('admin123'),
                'pin' => '123456',
                'role' => 'simart', // <--- ROLE BARU KHUSUS
                'saldo' => 0,
            ]
        );

        Admin::where('username', 'admin_kasir')->delete();
    }
}
