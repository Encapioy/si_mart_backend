<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            // 1. Rename kolom lama (banner_image -> banner_original)
            $table->renameColumn('banner_image', 'banner_original');

            // 2. Tambah kolom baru
            $table->string('banner_medium')->nullable()->after('banner_image'); // Nanti posisinya menyesuaikan
            $table->string('banner_low')->nullable()->after('banner_medium');
        });
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->renameColumn('banner_original', 'banner_image');
            $table->dropColumn(['banner_medium', 'banner_low']);
        });
    }
};
