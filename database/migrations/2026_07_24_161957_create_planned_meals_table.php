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
        Schema::create('planned_meals', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('meal_slot', ['midi', 'soir']);
            $table->foreignId('dish_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['date', 'meal_slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planned_meals');
    }
};
