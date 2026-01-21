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
        Schema::table('advertisements', function (Blueprint $table) {
            // Tambahkan Title dan Caption setelah kolom store_id
            $table->string('title', 100)->after('store_id'); // Judul pendek
            $table->text('caption')->nullable()->after('title'); // Deskripsi agak panjang
        });
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn(['title', 'caption']);
        });
    }
};
