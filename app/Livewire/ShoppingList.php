<?php

namespace App\Livewire;

use App\Models\Ingredient;
use App\Models\IngredientStock;
use App\Models\PlannedMeal;
use App\Models\ShoppingListOverride;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShoppingList extends Component
{
    #[Url]
    public ?string $month = null;

    #[Url]
    public ?string $year = null;

    public string $search = '';

    public string $newIngredientName = '';

    public function mount(): void
    {
        $this->month ??= Carbon::now()->startOfMonth()->toDateString();
        $this->year ??= Carbon::now()->startOfYear()->toDateString();
    }

    public function previousYear(): void
    {
        $this->year = $this->yearStart()->subYearNoOverflow()->toDateString();
        $this->month = Carbon::parse($this->year)->startOfMonth()->toDateString();
    }

    public function nextYear(): void
    {
        $this->year = $this->yearStart()->addYearNoOverflow()->toDateString();
        $this->month = Carbon::parse($this->year)->startOfMonth()->toDateString();
    }

    public function selectMonth(string $monthStart): void
    {
        $this->month = $monthStart;
    }

    public function toggle(int $ingredientId): void
    {
        $stock = IngredientStock::firstOrNew([
            'family_id' => $this->familyId(),
            'ingredient_id' => $ingredientId,
        ]);
        $stock->in_stock = ! $stock->in_stock;
        $stock->save();
    }

    public function addIngredient(int $ingredientId): void
    {
        ShoppingListOverride::updateOrCreate(
            ['family_id' => $this->familyId(), 'month' => $this->monthStart()->toDateString(), 'ingredient_id' => $ingredientId],
            ['included' => true],
        );
    }

    public function removeIngredient(int $ingredientId): void
    {
        ShoppingListOverride::updateOrCreate(
            ['family_id' => $this->familyId(), 'month' => $this->monthStart()->toDateString(), 'ingredient_id' => $ingredientId],
            ['included' => false],
        );
    }

    public function addNewIngredient(): void
    {
        $name = trim($this->newIngredientName);

        if ($name === '') {
            return;
        }

        $ingredient = Ingredient::firstOrCreate(['name' => $name]);
        $this->addIngredient($ingredient->id);
        $this->newIngredientName = '';
    }

    private function familyId(): int
    {
        return auth()->user()->family_id;
    }

    private function monthStart(): Carbon
    {
        return Carbon::parse($this->month)->startOfMonth();
    }

    private function yearStart(): Carbon
    {
        return Carbon::parse($this->year)->startOfYear();
    }

    public function render()
    {
        $familyId = $this->familyId();
        $start = $this->monthStart();
        $end = $start->copy()->endOfMonth();

        $derivedIds = PlannedMeal::where('family_id', $familyId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('dish_id')
            ->with('dish.ingredients')
            ->get()
            ->pluck('dish.ingredients')
            ->flatten()
            ->pluck('id')
            ->unique();

        $overrides = ShoppingListOverride::where('family_id', $familyId)
            ->where('month', $start->toDateString())
            ->pluck('included', 'ingredient_id');

        $excludedIds = $overrides->filter(fn ($included) => ! $included)->keys();
        $addedIds = $overrides->filter(fn ($included) => $included)->keys();

        $effectiveIds = $derivedIds->diff($excludedIds)->merge($addedIds)->unique();

        $stockMap = IngredientStock::where('family_id', $familyId)->pluck('in_stock', 'ingredient_id');

        $ingredients = Ingredient::whereIn('id', $effectiveIds)
            ->get()
            ->each(fn ($ingredient) => $ingredient->in_stock = (bool) $stockMap->get($ingredient->id, false))
            ->sortBy([['in_stock', 'asc'], ['category', 'asc'], ['name', 'asc']])
            ->groupBy('category');

        $yearStart = $this->yearStart();

        $filledCounts = PlannedMeal::where('family_id', $familyId)
            ->whereBetween('date', [$yearStart->toDateString(), $yearStart->copy()->endOfYear()->toDateString()])
            ->whereNotNull('dish_id')
            ->get()
            ->groupBy(fn ($meal) => $meal->date->copy()->startOfMonth()->toDateString())
            ->map->count();

        $monthTabs = collect(range(0, 11))->map(function ($i) use ($yearStart, $filledCounts) {
            $tabStart = $yearStart->copy()->addMonths($i);

            return [
                'start' => $tabStart,
                'filled' => $filledCounts->get($tabStart->toDateString(), 0),
            ];
        });

        $catalogQuery = Ingredient::orderBy('name');
        if ($this->search !== '') {
            $catalogQuery->where('name', 'like', '%'.$this->search.'%');
        }

        return view('livewire.shopping-list', [
            'ingredients' => $ingredients,
            'catalog' => $catalogQuery->get(),
            'effectiveIds' => $effectiveIds,
            'monthStart' => $start,
            'yearStart' => $yearStart,
            'monthTabs' => $monthTabs,
        ])->layout('layouts.app');
    }
}
