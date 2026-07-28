<?php

namespace Tests\Feature;

use App\Livewire\GestionCuisine;
use App\Models\Famille;
use App\Models\Ingredient;
use App\Models\Plat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestionCuisineTest extends TestCase
{
    use RefreshDatabase;

    private function utilisateurAvecFamille(): User
    {
        $famille = Famille::create(['name' => 'Test', 'code' => 'TEST'.uniqid()]);

        return User::factory()->create(['family_id' => $famille->id]);
    }

    public function test_creation_d_un_plat_avec_ingredients(): void
    {
        $user = $this->utilisateurAvecFamille();
        $ingredient = Ingredient::create(['name' => 'tomates', 'category' => 'Fruits & légumes']);

        Livewire::actingAs($user)
            ->test(GestionCuisine::class)
            ->call('nouveauPlat')
            ->set('platNom', 'Salade de tomates')
            ->set('platType', 'Salade')
            ->set('platIngredientIds', [$ingredient->id])
            ->call('enregistrerPlat');

        $this->assertDatabaseHas('dishes', ['name' => 'Salade de tomates', 'family_id' => $user->family_id]);
        $this->assertTrue(Plat::where('name', 'Salade de tomates')->first()->ingredients->contains($ingredient->id));
    }

    public function test_edition_d_un_plat_resynchronise_ses_ingredients(): void
    {
        $user = $this->utilisateurAvecFamille();
        $tomate = Ingredient::create(['name' => 'tomates']);
        $basilic = Ingredient::create(['name' => 'basilic']);
        $plat = Plat::create(['family_id' => $user->family_id, 'name' => 'Salade']);
        $plat->ingredients()->sync([$tomate->id]);

        Livewire::actingAs($user)
            ->test(GestionCuisine::class)
            ->call('editerPlat', $plat->id)
            ->set('platIngredientIds', [$basilic->id])
            ->call('enregistrerPlat');

        $plat->refresh();
        $this->assertFalse($plat->ingredients->contains($tomate->id));
        $this->assertTrue($plat->ingredients->contains($basilic->id));
    }

    public function test_suppression_d_un_plat_scope_a_la_famille(): void
    {
        $user = $this->utilisateurAvecFamille();
        $autreFamille = Famille::create(['name' => 'Autre', 'code' => 'AUTRE'.uniqid()]);
        $platDeLAutreFamille = Plat::create(['family_id' => $autreFamille->id, 'name' => 'Pas a moi']);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($user)
            ->test(GestionCuisine::class)
            ->call('supprimerPlat', $platDeLAutreFamille->id);
    }

    public function test_creation_edition_suppression_d_un_ingredient(): void
    {
        $user = $this->utilisateurAvecFamille();

        Livewire::actingAs($user)
            ->test(GestionCuisine::class)
            ->call('nouvelIngredient')
            ->set('ingredientNom', 'Courgette')
            ->set('ingredientCategorie', 'Fruits & légumes')
            ->call('enregistrerIngredient');

        $ingredient = Ingredient::where('name', 'Courgette')->firstOrFail();

        Livewire::actingAs($user)
            ->test(GestionCuisine::class)
            ->call('editerIngredient', $ingredient->id)
            ->set('ingredientNom', 'Courgettes')
            ->call('enregistrerIngredient');

        $this->assertDatabaseHas('ingredients', ['id' => $ingredient->id, 'name' => 'Courgettes']);

        Livewire::actingAs($user)
            ->test(GestionCuisine::class)
            ->call('supprimerIngredient', $ingredient->id);

        $this->assertDatabaseMissing('ingredients', ['id' => $ingredient->id]);
    }

    public function test_suppression_d_un_ingredient_utilise_par_un_plat_est_bloquee(): void
    {
        $user = $this->utilisateurAvecFamille();
        $ingredient = Ingredient::create(['name' => 'oeufs']);
        $plat = Plat::create(['family_id' => $user->family_id, 'name' => 'Omelette']);
        $plat->ingredients()->sync([$ingredient->id]);

        Livewire::actingAs($user)
            ->test(GestionCuisine::class)
            ->call('supprimerIngredient', $ingredient->id)
            ->assertHasErrors('suppressionIngredient');

        $this->assertDatabaseHas('ingredients', ['id' => $ingredient->id]);
    }

    public function test_validation_stricte_du_type_de_plat_et_de_la_categorie_d_ingredient(): void
    {
        $user = $this->utilisateurAvecFamille();

        Livewire::actingAs($user)
            ->test(GestionCuisine::class)
            ->call('nouveauPlat')
            ->set('platNom', 'Test')
            ->set('platType', 'TypeInexistant')
            ->call('enregistrerPlat')
            ->assertHasErrors(['platType']);

        Livewire::actingAs($user)
            ->test(GestionCuisine::class)
            ->call('nouvelIngredient')
            ->set('ingredientNom', 'Test')
            ->set('ingredientCategorie', 'CategorieInexistante')
            ->call('enregistrerIngredient')
            ->assertHasErrors(['ingredientCategorie']);
    }
}
