<?php

namespace App\Livewire;

use App\Models\Dish;
use Livewire\Component;

class Exchange extends Component
{
    public string $search = '';

    public string $filter = 'all';

    public function addDish(int $dishId): void
    {
        $familyId = $this->familyId();

        $source = Dish::with('ingredients')->find($dishId);

        if (! $source || $source->family_id === $familyId) {
            return;
        }

        $alreadyExists = Dish::where('family_id', $familyId)
            ->get()
            ->contains(fn (Dish $dish) => mb_strtolower($dish->name) === mb_strtolower($source->name));

        if ($alreadyExists) {
            return;
        }

        $copy = Dish::create([
            'family_id' => $familyId,
            'name' => $source->name,
            'type' => $source->type,
            'low_carb' => $source->low_carb,
            'dessert_suggestion' => $source->dessert_suggestion,
            'notes' => $source->notes,
        ]);

        $copy->ingredients()->sync($source->ingredients->pluck('id'));
    }

    private function familyId(): int
    {
        return auth()->user()->family_id;
    }

    public function render()
    {
        $familyId = $this->familyId();

        $dishesQuery = Dish::with(['ingredients', 'family'])
            ->where('family_id', '!=', $familyId);

        if ($this->filter === 'plat') {
            $dishesQuery->where(function ($q) {
                $q->where('type', '!=', 'Dessert')->orWhereNull('type');
            });
        } elseif ($this->filter === 'dessert') {
            $dishesQuery->where('type', 'Dessert');
        }

        if ($this->search !== '') {
            $dishesQuery->where('name', 'like', '%'.$this->search.'%');
        }

        $ownNames = Dish::where('family_id', $familyId)
            ->get()
            ->map(fn (Dish $dish) => mb_strtolower($dish->name));

        return view('livewire.exchange', [
            'dishes' => $dishesQuery->orderBy('name')->get(),
            'ownNames' => $ownNames,
        ])->layout('layouts.app');
    }
}
