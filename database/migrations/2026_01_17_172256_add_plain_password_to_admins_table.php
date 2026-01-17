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
        Schema::table('admins', function (Blueprint $table) {
            // Kolom khusus untuk menyimpan password yang bisa dibaca admin pusat
            if (!Schema::hasColumn('admins', 'plain_password')) {
                $table->string('plain_password')->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['plain_password']);
        });
    }
};
