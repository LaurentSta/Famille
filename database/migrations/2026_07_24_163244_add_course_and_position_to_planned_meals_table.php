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
        Schema::table('planned_meals', function (Blueprint $table) {
            $table->dropUnique(['date', 'meal_slot']);
            $table->enum('course', ['plat', 'dessert'])->default('plat')->after('meal_slot');
            $table->unsignedTinyInteger('position')->default(1)->after('course');
            $table->unique(['date', 'meal_slot', 'course', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planned_meals', function (Blueprint $table) {
            $table->dropUnique(['date', 'meal_slot', 'course', 'position']);
            $table->dropColumn(['course', 'position']);
            $table->unique(['date', 'meal_slot']);
        });
    }
};
