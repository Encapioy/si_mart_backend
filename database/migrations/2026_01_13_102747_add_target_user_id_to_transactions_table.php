<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Kolom boleh kosong (nullable) karena kalau TopUp/Bayar Toko kan gak ada target user-nya
            $table->foreignId('target_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('target_user_id');
        });
    }
};
