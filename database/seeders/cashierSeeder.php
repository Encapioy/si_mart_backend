<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class CashierSeeder extends Seeder
{
    public function run()
    {

        // 2. Kasir Kantin A
        Admin::create([
            'nama_lengkap' => 'kasir_dreamland_1',
            'username' => 'Dampridiansyah',
            'password' => Hash::make('fidiansyah'),
            'role' => 'kasir',
            'pin' => '111111',
            'saldo' => 0
        ]);

        // 3. Kasir Kantin B
        Admin::create([
            'nama_lengkap' => 'kasir_dreamland_2',
            'username' => 'hamzah',
            'password' => Hash::make('hamzah'),
            'role' => 'kasir',
            'pin' => '123456',
            'saldo' => 0
        ]);
    }
}