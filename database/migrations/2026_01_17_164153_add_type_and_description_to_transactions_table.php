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
        Schema::table('transactions', function (Blueprint $table) {
            // 1. Kolom TYPE (misal: 'debit', 'credit', 'topup', 'payment')
            // Kita taruh setelah kolom 'amount' (atau ganti 'id' jika tidak yakin)
            if (!Schema::hasColumn('transactions', 'type')) {
                $table->string('type')->after('total_bayar')->index();
            }

            // 2. Kolom DESCRIPTION (Keterangan transaksi)
            // Dibuat nullable karena tidak semua transaksi butuh deskripsi panjang
            if (!Schema::hasColumn('transactions', 'description')) {
                $table->string('description')->nullable()->after('type');
            }


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Hapus kolom jika migration di-rollback
            $table->dropColumn(['type', 'description']);
        });
    }
};