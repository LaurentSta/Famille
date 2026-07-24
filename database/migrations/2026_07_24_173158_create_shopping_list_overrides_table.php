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
        Schema::create('shopping_list_overrides', function (Blueprint $table) {
            $table->id();
            $table->date('month');
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->boolean('included');
            $table->timestamps();

            $table->unique(['month', 'ingredient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_list_overrides');
    }
};
