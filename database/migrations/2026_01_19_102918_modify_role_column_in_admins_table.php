<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Ubah tipe kolom role menjadi String biasa (Max 50 karakter)
            // Agar bisa menampung 'dreamland', 'kasir', dll tanpa batasan ENUM
            $table->string('role', 50)->change();
        });
    }

    public function down(): void
    {
        // Opsional: Kembalikan ke Enum jika di-rollback (sesuaikan dengan enum lama kamu)
        Schema::table('admins', function (Blueprint $table) {
            $table->enum('role', ['kasir', 'keuangan', 'developer', 'pusat'])->change();
        });
    }
};