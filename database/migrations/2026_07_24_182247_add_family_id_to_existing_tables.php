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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('family_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('dishes', function (Blueprint $table) {
            $table->foreignId('family_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('planned_meals', function (Blueprint $table) {
            $table->foreignId('family_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('shopping_list_overrides', function (Blueprint $table) {
            $table->dropUnique(['month', 'ingredient_id']);
            $table->foreignId('family_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->unique(['family_id', 'month', 'ingredient_id']);
        });

        $familyId = DB::table('families')->insertGetId([
            'name' => 'STAELENS',
            'code' => 'STAELENS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->whereNull('family_id')->update(['family_id' => $familyId]);
        DB::table('dishes')->whereNull('family_id')->update(['family_id' => $familyId]);
        DB::table('planned_meals')->whereNull('family_id')->update(['family_id' => $familyId]);
        DB::table('shopping_list_overrides')->whereNull('family_id')->update(['family_id' => $familyId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopping_list_overrides', function (Blueprint $table) {
            $table->dropUnique(['family_id', 'month', 'ingredient_id']);
            $table->dropConstrainedForeignId('family_id');
            $table->unique(['month', 'ingredient_id']);
        });

        Schema::table('planned_meals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('family_id');
        });

        Schema::table('dishes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('family_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('family_id');
        });
    }
};
