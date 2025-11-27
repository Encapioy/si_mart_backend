<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Kita gunakan Raw SQL agar aman mengubah ENUM
        // Kita tambahkan 'waiting_confirmation' ke dalam daftar
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'paid', 'cancelled', 'waiting_confirmation') NOT NULL DEFAULT 'paid'");
    }

    public function down(): void
    {
        // Kembalikan ke daftar status yang lama (jika rollback)
        // Hati-hati, data dengan status 'waiting_confirmation' bisa error jika di-rollback
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'paid'");
    }
};