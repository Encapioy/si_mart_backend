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
        Schema::create('balance_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Bisa User biasa / Admin

            // Jenis: 'credit' (Uang Masuk/+) atau 'debit' (Uang Keluar/-)
            $table->enum('type', ['credit', 'debit']);

            $table->decimal('amount', 15, 2); // Nominal transaksi
            $table->decimal('current_balance', 15, 2); // Saldo akhir setelah transaksi (buat jejak audit)

            // Kategori untuk Ikon di Frontend
            // Contoh: topup, purchase, sale, transfer, withdraw, refund, penalty, adjustment
            $table->string('category');

            $table->string('description'); // Penjelasan (cth: "Beli Bakwan", "Transfer ke Udin")

            $table->timestamps(); // Tanggal transaksi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_mutations');
    }
};
