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

    #[Url]
    public ?string $month = null;

    public string $search = '';

    public function mount(): void
    {
        $this->week ??= Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $this->month ??= Carbon::now()->startOfMonth()->toDateString();
    }

    public function previousMonth(): void
    {
        $this->month = $this->monthStart()->subMonthNoOverflow()->toDateString();
        $this->week = Carbon::parse($this->month)->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    public function nextMonth(): void
    {
        $this->month = $this->monthStart()->addMonthNoOverflow()->toDateString();
        $this->week = Carbon::parse($this->month)->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    public function selectWeek(string $weekStart): void
    {
        $this->week = $weekStart;
    }

    public function placeDish(string $date, string $slot, string $course, int $position, $dishId): void
    {
        if ($dishId === '' || $dishId === null) {
            $this->removeDish($date, $slot, $course, $position);

            return;
        }

        PlannedMeal::updateOrCreate(
            ['family_id' => $this->familyId(), 'date' => $date, 'meal_slot' => $slot, 'course' => $course, 'position' => $position],
            ['dish_id' => (int) $dishId],
        );
    }

    public function removeDish(string $date, string $slot, string $course, int $position): void
    {
        PlannedMeal::where([
            'family_id' => $this->familyId(),
            'date' => $date,
            'meal_slot' => $slot,
            'course' => $course,
            'position' => $position,
        ])->delete();
    }

    private function familyId(): int
    {
        return auth()->user()->family_id;
    }

    private function weekStart(): Carbon
    {
        return Carbon::parse($this->week)->startOfWeek(Carbon::MONDAY);
    }

    private function monthStart(): Carbon
    {
        return Carbon::parse($this->month)->startOfMonth();
    }

    public function render()
    {
        $familyId = $this->familyId();
        $start = $this->weekStart();
        $days = collect(range(0, 6))->map(fn ($i) => $start->copy()->addDays($i));

        $planned = PlannedMeal::where('family_id', $familyId)
            ->whereBetween('date', [$start->toDateString(), $start->copy()->addDays(6)->toDateString()])
            ->with('dish')
            ->get()
            ->keyBy(fn ($meal) => $meal->date->toDateString().'-'.$meal->meal_slot.'-'.$meal->course.'-'.$meal->position);

        $savoryQuery = Dish::where('family_id', $familyId)->where(function ($q) {
            $q->where('type', '!=', 'Dessert')->orWhereNull('type');
        });
        $dessertQuery = Dish::where('family_id', $familyId)->where('type', 'Dessert');

        if ($this->search !== '') {
            $savoryQuery->where('name', 'like', '%'.$this->search.'%');
            $dessertQuery->where('name', 'like', '%'.$this->search.'%');
        }

        $monthStart = $this->monthStart();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $weekStarts = collect();
        $cursor = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        while ($cursor->lte($monthEnd)) {
            $weekStarts->push($cursor->copy());
            $cursor->addWeek();
        }

        $filledCounts = PlannedMeal::where('family_id', $familyId)
            ->whereBetween('date', [$weekStarts->first()->toDateString(), $weekStarts->last()->copy()->addDays(6)->toDateString()])
            ->whereNotNull('dish_id')
            ->get()
            ->groupBy(fn ($meal) => $meal->date->copy()->startOfWeek(Carbon::MONDAY)->toDateString())
            ->map->count();

        $weekTabs = $weekStarts->map(fn ($weekStart) => [
            'start' => $weekStart,
            'filled' => $filledCounts->get($weekStart->toDateString(), 0),
        ]);

        return view('livewire.meal-planner', [
            'days' => $days,
            'planned' => $planned,
            'savoryDishes' => $savoryQuery->orderBy('name')->get(),
            'dessertDishes' => $dessertQuery->orderBy('name')->get(),
            'weekStart' => $start,
            'monthStart' => $monthStart,
            'weekTabs' => $weekTabs,
        ])->layout('layouts.app');
    }
}
