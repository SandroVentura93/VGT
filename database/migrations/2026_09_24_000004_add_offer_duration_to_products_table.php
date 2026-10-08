<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedSmallInteger('offer_duration_hours')->default(72)->after('offer_ends_at');
        });

        DB::table('products')->whereNotNull('offer_ends_at')->update([
            'offer_duration_hours' => 72,
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('offer_duration_hours');
        });
    }
};
