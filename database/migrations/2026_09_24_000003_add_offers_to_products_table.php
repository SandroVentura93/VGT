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
            $table->unsignedTinyInteger('offer_percentage')->default(0)->after('price');
            $table->timestamp('offer_ends_at')->nullable()->after('offer_percentage');
        });

        DB::table('products')->get(['id'])->each(function (object $product): void {
            DB::table('products')->where('id', $product->id)->update([
                'offer_percentage' => collect([20, 30, 40, 50])->random(),
                'offer_ends_at' => now()->addHours(random_int(36, 120)),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['offer_percentage', 'offer_ends_at']);
        });
    }
};
