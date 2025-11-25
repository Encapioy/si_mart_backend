<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom Saldo untuk Admin
        Schema::table('admins', function (Blueprint $table) {
            $table->decimal('saldo', 15, 2)->default(0)->after('nama_lengkap');
        });

        // 2. Tambahkan Penanda Pemilik di Tabel Produk
        Schema::table('products', function (Blueprint $table) {
            // Ini akan membuat 2 kolom: seller_id (int) dan seller_type (string)
            // seller_type isinya nanti: "App\Models\User" atau "App\Models\Admin"
            $table->nullableMorphs('seller');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('saldo');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropMorphs('seller');
        });
    }
};
