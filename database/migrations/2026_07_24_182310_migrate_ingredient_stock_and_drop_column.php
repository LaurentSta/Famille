<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $familyId = DB::table('families')->where('code', 'STAELENS')->value('id');

        if ($familyId) {
            $now = now();
            $rows = DB::table('ingredients')
                ->where('in_stock', true)
                ->pluck('id')
                ->map(fn ($ingredientId) => [
                    'family_id' => $familyId,
                    'ingredient_id' => $ingredientId,
                    'in_stock' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            if ($rows->isNotEmpty()) {
                DB::table('ingredient_stocks')->insert($rows->all());
            }
        }

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('in_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->boolean('in_stock')->default(false)->after('category');
        });

        $stockedIds = DB::table('ingredient_stocks')->where('in_stock', true)->pluck('ingredient_id');
        DB::table('ingredients')->whereIn('id', $stockedIds)->update(['in_stock' => true]);
    }
};
