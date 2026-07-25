<?php

namespace App\Livewire;

use App\Models\Ingredient;
use App\Models\StockIngredient;
use App\Models\RepasPlanifie;
use App\Models\ModificationListeCourses;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class ListeCourses extends Component
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
        $stock = StockIngredient::firstOrNew([
            'family_id' => $this->familyId(),
            'ingredient_id' => $ingredientId,
        ]);
        $stock->in_stock = ! $stock->in_stock;
        $stock->save();
    }

    public function addIngredient(int $ingredientId): void
    {
        ModificationListeCourses::updateOrCreate(
            ['family_id' => $this->familyId(), 'month' => $this->monthStart()->toDateString(), 'ingredient_id' => $ingredientId],
            ['included' => true],
        );
    }

    public function removeIngredient(int $ingredientId): void
    {
        ModificationListeCourses::updateOrCreate(
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

    /**
     * Ingrédients de la liste de courses du mois, groupés par catégorie, avec
     * les indicateurs `in_stock` (déjà à la maison) et `removed` (retiré alors
     * qu'un plat planifié en a toujours besoin).
     *
     * @return array{0: Collection, 1: Collection} [ingrédients groupés, ids effectivement dans la liste]
     */
    private function ingredientsDuMois(Carbon $start): array
    {
        $familyId = $this->familyId();
        $end = $start->copy()->endOfMonth();

        $derivedIds = RepasPlanifie::where('family_id', $familyId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('dish_id')
            ->with('plat.ingredients')
            ->get()
            ->pluck('plat.ingredients')
            ->flatten()
            ->pluck('id')
            ->unique();

        $overrides = ModificationListeCourses::where('family_id', $familyId)
            ->where('month', $start->toDateString())
            ->pluck('included', 'ingredient_id');

        $excludedIds = $overrides->filter(fn ($included) => ! $included)->keys();
        $addedIds = $overrides->filter(fn ($included) => $included)->keys();

        $effectiveIds = $derivedIds->diff($excludedIds)->merge($addedIds)->unique();

        // Un ingrédient retiré (croix) alors qu'un plat planifié en a toujours besoin
        // reste affiché, mais estompé, plutôt que de disparaître silencieusement :
        // l'utilisateur peut le remettre en un clic au lieu de devoir deviner
        // pourquoi son plat n'apporte plus ses ingrédients à la liste.
        $removedIds = $derivedIds->intersect($excludedIds);
        $visibleIds = $derivedIds->merge($addedIds)->unique();

        $stockMap = StockIngredient::where('family_id', $familyId)->pluck('in_stock', 'ingredient_id');

        $ingredients = Ingredient::whereIn('id', $visibleIds)
            ->get()
            ->each(function (Ingredient $ingredient) use ($stockMap, $removedIds) {
                $ingredient->in_stock = (bool) $stockMap->get($ingredient->id, false);
                $ingredient->removed = $removedIds->contains($ingredient->id);
            })
            ->sortBy([['removed', 'asc'], ['in_stock', 'asc'], ['category', 'asc'], ['name', 'asc']])
            ->groupBy('category');

        return [$ingredients, $effectiveIds];
    }

    public function telecharger()
    {
        $start = $this->monthStart();
        [$ingredients] = $this->ingredientsDuMois($start);

        $lignes = ['Liste de courses – '.$start->locale('fr')->translatedFormat('F Y'), ''];

        // Liste à plat, sans en-têtes de catégorie ni tri par "déjà en stock" :
        // le fichier téléchargé sert de mémo complet à emporter, pas d'écran de suivi.
        foreach ($ingredients->flatten(1)->reject(fn (Ingredient $i) => $i->removed)->sortBy('name') as $ingredient) {
            $lignes[] = '- '.$ingredient->name;
        }

        $contenu = implode("\n", $lignes);

        return response()->streamDownload(
            fn () => print($contenu),
            'Liste de courses - '.$start->locale('fr')->translatedFormat('F Y').'.txt',
        );
    }

    public function render()
    {
        $familyId = $this->familyId();
        $start = $this->monthStart();
        [$ingredients, $effectiveIds] = $this->ingredientsDuMois($start);

        $yearStart = $this->yearStart();

        $filledCounts = RepasPlanifie::where('family_id', $familyId)
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

        return view('livewire.liste-courses', [
            'ingredients' => $ingredients,
            'catalog' => $catalogQuery->get(),
            'effectiveIds' => $effectiveIds,
            'monthStart' => $start,
            'yearStart' => $yearStart,
            'monthTabs' => $monthTabs,
        ])->layout('layouts.app');
    }
}
