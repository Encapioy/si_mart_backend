<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel users (Satu user cuma punya satu toko)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('shop_name')->unique();
            $table->text('description')->nullable();
            $table->string('ktp_image')->nullable(); // Path foto verifikasi

            // Status pengajuan
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
            $table->string('rejection_reason')->nullable(); // Kalau ditolak, alasannya apa

            // Saldo Toko (Terpisah dari saldo user pribadi)
            $table->decimal('balance', 15, 2)->default(0);

            // Log audit simpel
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Siapa admin yg approve

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};