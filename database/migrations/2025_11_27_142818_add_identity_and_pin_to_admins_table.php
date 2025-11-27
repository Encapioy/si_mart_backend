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
            // Identity Code: Unik, misal "ADM-2025-001"
            $table->string('identity_code')->unique()->nullable()->after('id');

            // PIN: 6 Digit angka
            $table->string('pin', 6)->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['identity_code', 'pin']);
        });
    }
};
