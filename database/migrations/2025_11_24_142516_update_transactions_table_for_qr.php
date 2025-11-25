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
            // 1. Tambah kolom kode unik transaksi QR (setelah id)
            $table->string('transaction_code')->unique()->nullable()->after('id');

            // 2. Tambah status transaksi (setelah total_bayar)
            // Default 'paid' biar data lama dianggap sudah lunas
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('paid')->after('total_bayar');

            // 3. PENTING: Ubah user_id jadi BOLEH KOSONG (Nullable)
            // Karena pas kasir bikin QR, kita belum tau siapa murid yang bakal scan.
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['transaction_code', 'status']);
            // Kembalikan user_id jadi wajib isi
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
