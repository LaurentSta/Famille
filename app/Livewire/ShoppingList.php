<?php

namespace App\Livewire;

use App\Models\Ingredient;
use App\Models\PlannedMeal;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShoppingList extends Component
{
    #[Url]
    public ?string $week = null;

    public function mount(): void
    {
        $this->week ??= Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    public function toggle(int $ingredientId): void
    {
        $ingredient = Ingredient::findOrFail($ingredientId);
        $ingredient->update(['in_stock' => ! $ingredient->in_stock]);
    }

    public function render()
    {
        $start = Carbon::parse($this->week)->startOfWeek(Carbon::MONDAY);
        $end = $start->copy()->addDays(6);

        $ingredients = PlannedMeal::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('dish_id')
            ->with('dish.ingredients')
            ->get()
            ->pluck('dish.ingredients')
            ->flatten()
            ->unique('id')
            ->sortBy([['in_stock', 'asc'], ['name', 'asc']])
            ->groupBy('category');

        return view('livewire.shopping-list', [
            'ingredients' => $ingredients,
            'weekStart' => $start,
        ])->layout('layouts.app');
    }
}
