<?php

namespace App\Livewire;

use App\Models\ModificationListeCourses;
use App\Models\Plat;
use App\Models\RepasPlanifie;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;

class PlanificateurRepas extends Component
{
    #[Url]
    public ?string $semaine = null;

    #[Url]
    public ?string $mois = null;

    public string $recherche = '';

    public string $filtre = 'all';

    public bool $filtresOuverts = false;
    public ?string $filtreOrigine = null;
    public ?string $filtreRegime = null;
    public bool $filtreSansGluten = false;
    public bool $filtreLowCarb = false;

    public function filtrerParOrigine(string $origine): void
    {
        $this->filtreOrigine = $this->filtreOrigine === $origine ? null : $origine;
    }

    public function filtrerParRegime(string $regime): void
    {
        $this->filtreRegime = $this->filtreRegime === $regime ? null : $regime;
    }

    public function mount(): void
    {
        $this->semaine ??= Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $this->mois ??= Carbon::now()->startOfMonth()->toDateString();
    }

    public function moisPrecedent(): void
    {
        $this->mois = $this->debutMois()->subMonthNoOverflow()->toDateString();
        $this->semaine = Carbon::parse($this->mois)->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    public function moisSuivant(): void
    {
        $this->mois = $this->debutMois()->addMonthNoOverflow()->toDateString();
        $this->semaine = Carbon::parse($this->mois)->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    public function selectionnerSemaine(string $debutSemaine): void
    {
        $this->semaine = $debutSemaine;
    }

    public function placerPlat(string $date, string $creneau, string $service, int $position, $identifiantPlat): void
    {
        if ($identifiantPlat === '' || $identifiantPlat === null) {
            $this->retirerPlat($date, $creneau, $service, $position);

            return;
        }

        $plat = Plat::where('family_id', $this->identifiantFamille())->with('ingredients')->find((int) $identifiantPlat);

        if (! $plat) {
            return;
        }

        RepasPlanifie::updateOrCreate(
            ['family_id' => $this->identifiantFamille(), 'date' => $date, 'meal_slot' => $creneau, 'course' => $service, 'position' => $position],
            ['dish_id' => $plat->id],
        );

        // Placer une recette rend ses ingredients de nouveau necessaires ce
        // mois-ci : on leve une eventuelle exclusion manuelle anterieure
        // (croix dans la liste de courses) pour ne pas qu'un ingredient
        // retire pour une autre raison reste grise/masque alors qu'une
        // nouvelle recette en a besoin.
        ModificationListeCourses::where('family_id', $this->identifiantFamille())
            ->where('month', Carbon::parse($date)->startOfMonth()->toDateString())
            ->where('included', false)
            ->whereIn('ingredient_id', $plat->ingredients->pluck('id'))
            ->delete();
    }

    public function retirerPlat(string $date, string $creneau, string $service, int $position): void
    {
        RepasPlanifie::where([
            'family_id' => $this->identifiantFamille(),
            'date' => $date,
            'meal_slot' => $creneau,
            'course' => $service,
            'position' => $position,
        ])->delete();
    }

    private function identifiantFamille(): int
    {
        return auth()->user()->family_id;
    }

    private function debutSemaine(): Carbon
    {
        return Carbon::parse($this->semaine)->startOfWeek(Carbon::MONDAY);
    }

    private function debutMois(): Carbon
    {
        return Carbon::parse($this->mois)->startOfMonth();
    }

    public function render()
    {
        $identifiantFamille = $this->identifiantFamille();
        $debut = $this->debutSemaine();
        $jours = collect(range(0, 6))->map(fn ($i) => $debut->copy()->addDays($i));

        $repasPlanifies = RepasPlanifie::where('family_id', $identifiantFamille)
            ->whereBetween('date', [$debut->toDateString(), $debut->copy()->addDays(6)->toDateString()])
            ->with('plat')
            ->get()
            ->keyBy(fn ($meal) => $meal->date->toDateString().'-'.$meal->meal_slot.'-'.$meal->course.'-'.$meal->position);

        $requetePlats = Plat::where('family_id', $identifiantFamille);

        if ($this->filtre === 'plat') {
            $requetePlats->where(function ($q) {
                $q->where('type', '!=', 'Dessert')->orWhereNull('type');
            });
        } elseif ($this->filtre === 'dessert') {
            $requetePlats->where('type', 'Dessert');
        }

        if ($this->recherche !== '') {
            $requetePlats->where('name', 'like', '%'.$this->recherche.'%');
        }

        if ($this->filtreOrigine) {
            $requetePlats->where('cuisine_origin', $this->filtreOrigine);
        }

        if ($this->filtreRegime) {
            $requetePlats->where('regime', $this->filtreRegime);
        }

        if ($this->filtreSansGluten) {
            $requetePlats->where('gluten_free', true);
        }

        if ($this->filtreLowCarb) {
            $requetePlats->where('low_carb', true);
        }

        $debutMois = $this->debutMois();
        $finMois = $debutMois->copy()->endOfMonth();
        $debutSemaines = collect();
        $curseur = $debutMois->copy()->startOfWeek(Carbon::MONDAY);
        while ($curseur->lte($finMois)) {
            $debutSemaines->push($curseur->copy());
            $curseur->addWeek();
        }

        $nombresRemplis = RepasPlanifie::where('family_id', $identifiantFamille)
            ->whereBetween('date', [$debutSemaines->first()->toDateString(), $debutSemaines->last()->copy()->addDays(6)->toDateString()])
            ->whereNotNull('dish_id')
            ->get()
            ->groupBy(fn ($meal) => $meal->date->copy()->startOfWeek(Carbon::MONDAY)->toDateString())
            ->map->count();

        $ongletsSemaines = $debutSemaines->map(fn ($debutSemaine) => [
            'start' => $debutSemaine,
            'filled' => $nombresRemplis->get($debutSemaine->toDateString(), 0),
        ]);

        return view('livewire.planificateur-repas', [
            'jours' => $jours,
            'repasPlanifies' => $repasPlanifies,
            'plats' => $requetePlats->with('ingredients')->orderBy('name')->get(),
            'originesDisponibles' => config('emoji.dish_origins'),
            'regimesDisponibles' => config('emoji.dish_regimes'),
            'debutSemaine' => $debut,
            'debutMois' => $debutMois,
            'ongletsSemaines' => $ongletsSemaines,
        ])->layout('layouts.app');
    }
}
