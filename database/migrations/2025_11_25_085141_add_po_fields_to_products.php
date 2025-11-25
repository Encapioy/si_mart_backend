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
        Schema::table('products', function (Blueprint $table) {
            // Cek dulu biar gak error "Duplicate column" kalau sebagian udah ada
            if (!Schema::hasColumn('products', 'harga_modal')) {
                $table->decimal('harga_modal', 15, 2)->default(0)->after('harga');
            }
            if (!Schema::hasColumn('products', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('nama_produk');
            }
            if (!Schema::hasColumn('products', 'is_preorder')) {
                $table->boolean('is_preorder')->default(false)->after('stok');
            }
            if (!Schema::hasColumn('products', 'po_estimasi')) {
                $table->dateTime('po_estimasi')->nullable()->after('is_preorder');
            }
            if (!Schema::hasColumn('products', 'po_kuota')) {
                $table->integer('po_kuota')->default(0)->after('po_estimasi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['harga_modal', 'deskripsi', 'is_preorder', 'po_estimasi', 'po_kuota']);
        });
    }
};
