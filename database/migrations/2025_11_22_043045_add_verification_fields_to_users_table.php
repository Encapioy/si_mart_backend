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
        Schema::table('users', function (Blueprint $table) {
            // Data detail KTP
            $table->string('nik')->nullable();
            $table->text('alamat_ktp')->nullable();

            // Path foto (disimpan path-nya saja string)
            $table->string('foto_ktp')->nullable();
            $table->string('foto_selfie_ktp')->nullable();

            // Status: unverified (awal), pending (udah upload, nunggu admin), verified (sah), rejected (ditolak)
            $table->enum('status_verifikasi', ['unverified', 'pending', 'verified', 'rejected'])
                ->default('unverified');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nik', 'alamat_ktp', 'foto_ktp', 'foto_selfie_ktp', 'status_verifikasi']);
        });
    }
};
