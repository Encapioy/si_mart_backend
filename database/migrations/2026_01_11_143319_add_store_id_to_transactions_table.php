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
            // Menambahkan kolom store_id (Foreign Key ke tabel stores)
            // Kita buat nullable() agar transaksi lama (yang bukan ke toko) tidak error
            $table->foreignId('store_id')
                ->nullable()
                ->after('user_id') // Posisi kolom setelah user_id
                ->constrained('stores')
                ->onDelete('set null'); // Jika toko dihapus, riwayat transaksi tetap ada (store_id jadi null)
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Hapus foreign key dan kolom jika rollback
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
