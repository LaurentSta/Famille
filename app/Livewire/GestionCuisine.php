<?php

namespace App\Livewire;

use App\Models\Ingredient;
use App\Models\ModificationListeCourses;
use App\Models\Plat;
use Illuminate\Validation\Rule;
use Livewire\Component;

class GestionCuisine extends Component
{
    public string $onglet = 'plats';

    // --- Plats ---
    public string $recherchePlat = '';
    public bool $formulairePlatOuvert = false;
    public ?int $platEnEdition = null;
    public string $platNom = '';
    public ?string $platType = null;
    public bool $platLowCarb = false;
    public ?string $platDessertSuggestion = null;
    public ?string $platNotes = null;
    public array $platIngredientIds = [];
    public string $rechercheIngredientDuPlat = '';

    // --- Ingredients ---
    public string $rechercheIngredient = '';
    public bool $formulaireIngredientOuvert = false;
    public ?int $ingredientEnEdition = null;
    public string $ingredientNom = '';
    public ?string $ingredientCategorie = null;

    public ?string $messageSucces = null;

    public function changerOnglet(string $onglet): void
    {
        $this->onglet = $onglet;
        $this->annulerPlat();
        $this->annulerIngredient();
    }

    // ---------- Plats ----------

    public function nouveauPlat(): void
    {
        $this->resetValidation();
        $this->platEnEdition = null;
        $this->platNom = '';
        $this->platType = null;
        $this->platLowCarb = false;
        $this->platDessertSuggestion = null;
        $this->platNotes = null;
        $this->platIngredientIds = [];
        $this->rechercheIngredientDuPlat = '';
        $this->formulairePlatOuvert = true;
    }

    public function editerPlat(int $id): void
    {
        $plat = Plat::where('family_id', $this->familyId())->with('ingredients')->findOrFail($id);

        $this->resetValidation();
        $this->platEnEdition = $plat->id;
        $this->platNom = $plat->name;
        $this->platType = $plat->type;
        $this->platLowCarb = (bool) $plat->low_carb;
        $this->platDessertSuggestion = $plat->dessert_suggestion;
        $this->platNotes = $plat->notes;
        $this->platIngredientIds = $plat->ingredients->pluck('id')->all();
        $this->rechercheIngredientDuPlat = '';
        $this->formulairePlatOuvert = true;
    }

    public function annulerPlat(): void
    {
        $this->formulairePlatOuvert = false;
        $this->platEnEdition = null;
    }

    public function enregistrerPlat(): void
    {
        $data = $this->validate([
            'platNom' => ['required', 'string', 'max:255'],
            'platType' => ['nullable', Rule::in(array_keys(config('emoji.dish_types')))],
            'platLowCarb' => ['boolean'],
            'platDessertSuggestion' => ['nullable', 'string', 'max:255'],
            'platNotes' => ['nullable', 'string'],
            'platIngredientIds' => ['array'],
            'platIngredientIds.*' => ['integer', Rule::exists('ingredients', 'id')],
        ]);

        $familyId = $this->familyId();

        if ($this->platEnEdition) {
            $plat = Plat::where('family_id', $familyId)->findOrFail($this->platEnEdition);
            $plat->update([
                'name' => $data['platNom'],
                'type' => $data['platType'],
                'low_carb' => $data['platLowCarb'],
                'dessert_suggestion' => $data['platDessertSuggestion'],
                'notes' => $data['platNotes'],
            ]);
        } else {
            $plat = Plat::create([
                'family_id' => $familyId,
                'name' => $data['platNom'],
                'type' => $data['platType'],
                'low_carb' => $data['platLowCarb'],
                'dessert_suggestion' => $data['platDessertSuggestion'],
                'notes' => $data['platNotes'],
            ]);
        }

        // sync() ajoute/retire les liens sans dupliquer et sans toucher aux
        // lignes Ingredient elles-memes : safe a re-executer sur une edition.
        $plat->ingredients()->sync($data['platIngredientIds']);

        $this->messageSucces = "Plat « {$plat->name} » enregistré.";
        $this->annulerPlat();
    }

    public function supprimerPlat(int $id): void
    {
        $plat = Plat::where('family_id', $this->familyId())->findOrFail($id);
        $nom = $plat->name;
        $plat->delete();

        $this->messageSucces = "Plat « {$nom} » supprimé.";
    }

    // ---------- Ingredients ----------

    public function nouvelIngredient(): void
    {
        $this->resetValidation();
        $this->ingredientEnEdition = null;
        $this->ingredientNom = '';
        $this->ingredientCategorie = null;
        $this->formulaireIngredientOuvert = true;
    }

    public function editerIngredient(int $id): void
    {
        $ingredient = Ingredient::findOrFail($id);

        $this->resetValidation();
        $this->ingredientEnEdition = $ingredient->id;
        $this->ingredientNom = $ingredient->name;
        $this->ingredientCategorie = $ingredient->category;
        $this->formulaireIngredientOuvert = true;
    }

    public function annulerIngredient(): void
    {
        $this->formulaireIngredientOuvert = false;
        $this->ingredientEnEdition = null;
    }

    public function enregistrerIngredient(): void
    {
        $data = $this->validate([
            'ingredientNom' => [
                'required', 'string', 'max:255',
                Rule::unique('ingredients', 'name')->ignore($this->ingredientEnEdition),
            ],
            'ingredientCategorie' => ['nullable', Rule::in(array_keys(config('emoji.ingredient_category_default')))],
        ]);

        if ($this->ingredientEnEdition) {
            $ingredient = Ingredient::findOrFail($this->ingredientEnEdition);
            $ingredient->update(['name' => $data['ingredientNom'], 'category' => $data['ingredientCategorie']]);
        } else {
            $ingredient = Ingredient::create(['name' => $data['ingredientNom'], 'category' => $data['ingredientCategorie']]);
        }

        $this->messageSucces = "Ingrédient « {$ingredient->name} » enregistré.";
        $this->annulerIngredient();
    }

    public function supprimerIngredient(int $id): void
    {
        $ingredient = Ingredient::findOrFail($id);

        // Catalogue GLOBAL (pas de family_id) : un ingredient peut etre
        // reference par des plats/stocks/listes de courses d'AUTRES
        // familles que la notre. On bloque la suppression tant qu'il est
        // utilise quelque part, pour ne jamais casser silencieusement les
        // donnees d'une autre famille via un cascadeOnDelete invisible.
        $utilise = $ingredient->plats()->exists()
            || $ingredient->stocks()->exists()
            || ModificationListeCourses::where('ingredient_id', $id)->exists();

        if ($utilise) {
            $this->addError('suppressionIngredient', "Impossible de supprimer « {$ingredient->name} » : il est encore utilisé (plat, stock ou liste de courses, éventuellement d'une autre famille).");
            return;
        }

        $ingredient->delete();
        $this->messageSucces = "Ingrédient « {$ingredient->name} » supprimé.";
    }

    private function familyId(): int
    {
        return auth()->user()->family_id;
    }

    public function render()
    {
        $familyId = $this->familyId();

        $requetePlats = Plat::where('family_id', $familyId)->with('ingredients');
        if ($this->recherchePlat !== '') {
            $requetePlats->where('name', 'like', '%'.$this->recherchePlat.'%');
        }

        $requeteIngredients = Ingredient::query()->orderBy('name');
        if ($this->rechercheIngredient !== '') {
            $requeteIngredients->where('name', 'like', '%'.$this->rechercheIngredient.'%');
        }

        $ingredientsDisponibles = Ingredient::orderBy('name')->get();
        if ($this->rechercheIngredientDuPlat !== '') {
            $ingredientsDisponibles = $ingredientsDisponibles
                ->filter(fn (Ingredient $i) => str_contains(mb_strtolower($i->name), mb_strtolower($this->rechercheIngredientDuPlat)))
                ->values();
        }

        return view('livewire.gestion-cuisine', [
            'plats' => $requetePlats->orderBy('name')->get(),
            'ingredients' => $requeteIngredients->withCount([
                'plats as plats_famille_count' => fn ($q) => $q->where('dishes.family_id', $familyId),
            ])->get(),
            'ingredientsDisponibles' => $ingredientsDisponibles,
            'typesDisponibles' => config('emoji.dish_types'),
            'categoriesDisponibles' => config('emoji.ingredient_category_default'),
        ])->layout('layouts.app');
    }
}
