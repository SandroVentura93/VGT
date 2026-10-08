<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_dni', 8)->after('customer_name');
            $table->string('customer_first_name', 80)->after('customer_dni');
            $table->string('customer_last_name', 120)->after('customer_first_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['customer_dni', 'customer_first_name', 'customer_last_name']);
        });
    }
};
