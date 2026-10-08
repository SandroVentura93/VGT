<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_account_holder', 120)->nullable()->after('payment_method');
            $table->string('payment_operation_number', 60)->nullable()->after('payment_account_holder');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_account_holder', 'payment_operation_number']);
        });
    }
};
