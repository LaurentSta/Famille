<?php

namespace App\Livewire;

use App\Models\Ingredient;
use Livewire\Component;

class Pantry extends Component
{
    public function toggle(int $ingredientId): void
    {
        $ingredient = Ingredient::findOrFail($ingredientId);
        $ingredient->update(['in_stock' => ! $ingredient->in_stock]);
    }

    public function render()
    {
        $ingredients = Ingredient::orderBy('name')->get()->groupBy('category');

        return view('livewire.pantry', [
            'ingredients' => $ingredients,
        ])->layout('layouts.app');
    }
}
