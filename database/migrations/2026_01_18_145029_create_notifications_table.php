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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Punya Siapa?
            $table->string('title'); // Judul: "Topup Berhasil"
            $table->text('body');    // Isi: "Saldo Rp 100.000 telah masuk..."
            $table->string('type')->default('info'); // Jenis: transaction, promo, system
            $table->boolean('is_read')->default(false); // Sudah dibaca belum?
            $table->json('data')->nullable(); // Data tambahan (misal: transaction_id)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
