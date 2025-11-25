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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            // Pemilik Toko (Merchant)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Info Wajib
            $table->string('nama_toko');
            $table->string('kategori'); // Makanan, Jasa, Alat Tulis, dll

            // Info Tambahan (Bisa diupdate nanti)
            $table->text('deskripsi')->nullable();
            $table->text('lokasi')->nullable(); // Misal: "Gedung A Lt 2"
            $table->string('gambar')->nullable();
            $table->boolean('is_open')->default(true); // Status Buka/Tutup

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
