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
    public ?string $month = null;

    #[Url]
    public ?string $year = null;

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
        $ingredient = Ingredient::findOrFail($ingredientId);
        $ingredient->update(['in_stock' => ! $ingredient->in_stock]);
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
        $start = $this->monthStart();
        $end = $start->copy()->endOfMonth();

        $ingredients = PlannedMeal::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('dish_id')
            ->with('dish.ingredients')
            ->get()
            ->pluck('dish.ingredients')
            ->flatten()
            ->unique('id')
            ->sortBy([['in_stock', 'asc'], ['name', 'asc']])
            ->groupBy('category');

        $yearStart = $this->yearStart();

        $filledCounts = PlannedMeal::whereBetween('date', [$yearStart->toDateString(), $yearStart->copy()->endOfYear()->toDateString()])
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

        return view('livewire.shopping-list', [
            'ingredients' => $ingredients,
            'monthStart' => $start,
            'yearStart' => $yearStart,
            'monthTabs' => $monthTabs,
        ])->layout('layouts.app');
    }
}
