<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Tambah kolom type setelah total_bayar
            // Kita kasih default 'payment' biar data lama gak error
            $table->string('type', 20)->default('payment')->after('total_bayar');

            // Tambah kolom description setelah status (bisa kosong/null)
            $table->text('description')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Hapus kolom kalau di-rollback
            $table->dropColumn(['type', 'description']);
        });
    }
};
