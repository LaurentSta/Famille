<?php

namespace Tests\Feature;

use App\Livewire\PlanificateurRepas;
use App\Models\Famille;
use App\Models\Ingredient;
use App\Models\ModificationListeCourses;
use App\Models\Plat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlanificateurRepasTest extends TestCase
{
    use RefreshDatabase;

    private function utilisateurAvecFamille(): User
    {
        $famille = Famille::create(['name' => 'Test', 'code' => 'TEST'.uniqid()]);

        return User::factory()->create(['family_id' => $famille->id]);
    }

    public function test_placer_un_plat_leve_une_exclusion_existante_de_ses_ingredients(): void
    {
        $user = $this->utilisateurAvecFamille();
        $tomates = Ingredient::create(['name' => 'tomates']);
        $plat = Plat::create(['family_id' => $user->family_id, 'name' => 'Ratatouille']);
        $plat->ingredients()->sync([$tomates->id]);

        // L'utilisateur avait explicitement exclu "tomates" de la liste de courses de ce mois.
        ModificationListeCourses::create([
            'family_id' => $user->family_id,
            'month' => '2026-07-01',
            'ingredient_id' => $tomates->id,
            'included' => false,
        ]);

        Livewire::actingAs($user)
            ->test(PlanificateurRepas::class)
            ->call('placerPlat', '2026-07-10', 'midi', 'plat', 1, (string) $plat->id);

        $this->assertDatabaseMissing('shopping_list_overrides', [
            'family_id' => $user->family_id,
            'month' => '2026-07-01',
            'ingredient_id' => $tomates->id,
        ]);
    }

    public function test_placer_un_plat_ne_touche_pas_aux_exclusions_d_autres_mois(): void
    {
        $user = $this->utilisateurAvecFamille();
        $tomates = Ingredient::create(['name' => 'tomates']);
        $plat = Plat::create(['family_id' => $user->family_id, 'name' => 'Ratatouille']);
        $plat->ingredients()->sync([$tomates->id]);

        ModificationListeCourses::create([
            'family_id' => $user->family_id,
            'month' => '2026-08-01',
            'ingredient_id' => $tomates->id,
            'included' => false,
        ]);

        Livewire::actingAs($user)
            ->test(PlanificateurRepas::class)
            ->call('placerPlat', '2026-07-10', 'midi', 'plat', 1, (string) $plat->id);

        $this->assertDatabaseHas('shopping_list_overrides', [
            'family_id' => $user->family_id,
            'month' => '2026-08-01',
            'ingredient_id' => $tomates->id,
            'included' => false,
        ]);
    }

    public function test_le_filtre_origine_ne_montre_que_les_plats_de_cette_origine(): void
    {
        $user = $this->utilisateurAvecFamille();
        Plat::create(['family_id' => $user->family_id, 'name' => 'Curry', 'cuisine_origin' => 'Indien']);
        Plat::create(['family_id' => $user->family_id, 'name' => 'Blanquette', 'cuisine_origin' => 'Européen']);

        Livewire::actingAs($user)
            ->test(PlanificateurRepas::class)
            ->call('filtrerParOrigine', 'Indien')
            ->assertSee('Curry')
            ->assertDontSee('Blanquette');
    }

    public function test_recliquer_sur_la_meme_origine_desactive_le_filtre(): void
    {
        $user = $this->utilisateurAvecFamille();
        Plat::create(['family_id' => $user->family_id, 'name' => 'Curry', 'cuisine_origin' => 'Indien']);
        Plat::create(['family_id' => $user->family_id, 'name' => 'Blanquette', 'cuisine_origin' => 'Européen']);

        Livewire::actingAs($user)
            ->test(PlanificateurRepas::class)
            ->call('filtrerParOrigine', 'Indien')
            ->call('filtrerParOrigine', 'Indien')
            ->assertSet('filtreOrigine', null)
            ->assertSee('Curry')
            ->assertSee('Blanquette');
    }

    public function test_le_filtre_regime_et_sans_gluten_se_combinent(): void
    {
        $user = $this->utilisateurAvecFamille();
        Plat::create(['family_id' => $user->family_id, 'name' => 'Salade vegan sans gluten', 'regime' => 'Vegan', 'gluten_free' => true]);
        Plat::create(['family_id' => $user->family_id, 'name' => 'Salade vegan avec gluten', 'regime' => 'Vegan', 'gluten_free' => false]);
        Plat::create(['family_id' => $user->family_id, 'name' => 'Poulet', 'regime' => null]);

        Livewire::actingAs($user)
            ->test(PlanificateurRepas::class)
            ->call('filtrerParRegime', 'Vegan')
            ->set('filtreSansGluten', true)
            ->assertSee('Salade vegan sans gluten')
            ->assertDontSee('Salade vegan avec gluten')
            ->assertDontSee('Poulet');
    }
}
