<?php

namespace App\Livewire;

use App\Models\Dish;
use App\Models\PlannedMeal;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class MealPlanner extends Component
{
    #[Url]
    public ?string $week = null;

    public function mount(): void
    {
        $this->week ??= Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    public function previousWeek(): void
    {
        $this->week = $this->weekStart()->subWeek()->toDateString();
    }

    public function nextWeek(): void
    {
        $this->week = $this->weekStart()->addWeek()->toDateString();
    }

    public function updateSlot(string $date, string $slot, $dishId): void
    {
        PlannedMeal::updateOrCreate(
            ['date' => $date, 'meal_slot' => $slot],
            ['dish_id' => $dishId !== '' && $dishId !== null ? (int) $dishId : null],
        );
    }

    private function weekStart(): Carbon
    {
        return Carbon::parse($this->week)->startOfWeek(Carbon::MONDAY);
    }

    public function render()
    {
        $start = $this->weekStart();
        $days = collect(range(0, 6))->map(fn ($i) => $start->copy()->addDays($i));

        $planned = PlannedMeal::whereBetween('date', [$start->toDateString(), $start->copy()->addDays(6)->toDateString()])
            ->get()
            ->keyBy(fn ($meal) => $meal->date->toDateString().'-'.$meal->meal_slot);

        $dishesByType = Dish::orderBy('name')->get()->groupBy(fn ($dish) => $dish->type ?? 'Autre');

        return view('livewire.meal-planner', [
            'days' => $days,
            'planned' => $planned,
            'dishesByType' => $dishesByType,
            'weekStart' => $start,
        ])->layout('layouts.app');
    }
}
