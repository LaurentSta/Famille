<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * L'unicite (date, meal_slot, course, position) a ete introduite avant le
     * multi-famille et n'a jamais ete etendue a family_id lors de son ajout
     * (contrairement a shopping_list_overrides, qui l'a ete au meme moment).
     * Deux familles planifiant un repas au meme creneau du meme jour se
     * heurtaient donc a une violation de contrainte SQL.
     */
    public function up(): void
    {
        Schema::table('planned_meals', function (Blueprint $table) {
            $table->dropUnique(['date', 'meal_slot', 'course', 'position']);
            $table->unique(['family_id', 'date', 'meal_slot', 'course', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planned_meals', function (Blueprint $table) {
            $table->dropUnique(['family_id', 'date', 'meal_slot', 'course', 'position']);
            $table->unique(['date', 'meal_slot', 'course', 'position']);
        });
    }
};
