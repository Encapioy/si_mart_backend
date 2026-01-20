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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();

            // Pemilik Iklan (Merchant)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Toko yang diiklankan
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');

            // Gambar Banner
            $table->string('banner_image');

            // Waktu & Durasi
            $table->dateTime('start_time');
            $table->dateTime('end_time'); // Waktu habis (start + 3 jam)

            // Status Iklan
            // active: Masih dalam durasi 3 jam
            // grace_period: Masuk masa tenggang 10 menit
            // expired: Sudah lewat masa tenggang & mati
            $table->enum('status', ['active', 'grace_period', 'expired'])->default('active');

            // Helper untuk Scheduler: Agar notifikasi tidak dikirim berkali-kali
            $table->boolean('is_notified')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};