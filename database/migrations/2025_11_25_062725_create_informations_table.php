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
        Schema::create('informations', function (Blueprint $table) {
            $table->id();
            // Siapa admin yang posting?
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');

            // Info Umum
            $table->string('judul');
            $table->enum('kategori', ['promo', 'pondok', 'sistem']);
            $table->text('konten'); // Deskripsi Promo / Isi Berita
            $table->string('gambar')->nullable(); // Untuk Pondok & Sistem

            // Khusus Kategori Promo
            $table->string('kode_promo')->nullable();
            $table->dateTime('berlaku_sampai')->nullable();
            $table->text('syarat_ketentuan')->nullable();

            $table->timestamps(); // Tanggal dibuat (created_at) sudah otomatis ada
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informations');
    }
};
