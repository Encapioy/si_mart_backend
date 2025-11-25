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
        Schema::table('withdrawals', function (Blueprint $table) {
            // Info Rekening User (Nullable karena bisa aja dia minta Tunai)
            $table->string('bank_name')->nullable()->after('amount'); // BCA, BRI, DANA, dll
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('account_name')->nullable()->after('account_number');

            // Info dari Admin (Saat Approve)
            $table->decimal('admin_fee', 15, 2)->default(0)->after('status'); // Biaya admin
            $table->string('bukti_transfer_admin')->nullable()->after('admin_fee'); // Bukti admin sudah transfer
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'account_number', 'account_name', 'admin_fee', 'bukti_transfer_admin']);
        });
    }
};
