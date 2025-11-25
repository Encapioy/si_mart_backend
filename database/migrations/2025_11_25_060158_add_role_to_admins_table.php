<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Kita pakai ENUM agar isinya pasti salah satu dari 4 ini
            $table->enum('role', ['pusat', 'developer', 'kasir', 'keuangan'])
                ->default('pusat') // Default ke pusat biar aman
                ->after('nama_lengkap');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
