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
        Schema::create('pre_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_code')->unique(); // Kode unik pesanan (misal: PO-X7281)

            // Relasi
            $table->foreignId('user_id')->constrained('users'); // Pembeli
            $table->foreignId('product_id')->constrained('products'); // Barang yang dibeli

            // Detail Pesanan
            $table->integer('qty'); // Jumlah barang
            $table->decimal('total_bayar', 15, 2); // Total uang yang dibayar

            // Info Pengambilan
            $table->string('nama_penerima');
            $table->text('catatan')->nullable();

            // Status Pesanan
            // paid = Sudah bayar, menunggu barang
            // ready = Barang sudah sampai di Simart (opsional)
            // taken = Barang sudah diambil user (transaksi selesai)
            // cancelled = Dibatalkan (kena denda)
            $table->enum('status', ['paid', 'ready', 'taken', 'cancelled'])->default('paid');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_orders');
    }
};
