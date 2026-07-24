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

    public string $search = '';

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

    public function placeDish(string $date, string $slot, string $course, int $position, $dishId): void
    {
        if ($dishId === '' || $dishId === null) {
            $this->removeDish($date, $slot, $course, $position);

            return;
        }

        PlannedMeal::updateOrCreate(
            ['date' => $date, 'meal_slot' => $slot, 'course' => $course, 'position' => $position],
            ['dish_id' => (int) $dishId],
        );
    }

    public function removeDish(string $date, string $slot, string $course, int $position): void
    {
        PlannedMeal::where(['date' => $date, 'meal_slot' => $slot, 'course' => $course, 'position' => $position])
            ->delete();
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
            ->with('dish')
            ->get()
            ->keyBy(fn ($meal) => $meal->date->toDateString().'-'.$meal->meal_slot.'-'.$meal->course.'-'.$meal->position);

        $savoryQuery = Dish::where(function ($q) {
            $q->where('type', '!=', 'Dessert')->orWhereNull('type');
        });
        $dessertQuery = Dish::where('type', 'Dessert');

        if ($this->search !== '') {
            $savoryQuery->where('name', 'like', '%'.$this->search.'%');
            $dessertQuery->where('name', 'like', '%'.$this->search.'%');
        }

        return view('livewire.meal-planner', [
            'days' => $days,
            'planned' => $planned,
            'savoryDishes' => $savoryQuery->orderBy('name')->get(),
            'dessertDishes' => $dessertQuery->orderBy('name')->get(),
            'weekStart' => $start,
        ])->layout('layouts.app');
    }
}
