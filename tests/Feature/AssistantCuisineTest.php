<?php

namespace Tests\Feature;

use App\Livewire\AssistantCuisine;
use App\Models\Famille;
use App\Models\Ingredient;
use App\Models\MessageIa;
use App\Models\Plat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class AssistantCuisineTest extends TestCase
{
    use RefreshDatabase;

    private function utilisateurAvecFamille(): User
    {
        $famille = Famille::create(['name' => 'Test', 'code' => 'TEST'.uniqid()]);

        return User::factory()->create(['family_id' => $famille->id]);
    }

    public function test_un_plat_detecte_par_l_ia_s_affiche_avec_le_bouton_ajouter(): void
    {
        $user = $this->utilisateurAvecFamille();

        MessageIa::create([
            'family_id' => $user->family_id,
            'role' => 'assistant',
            'content' => 'Voici une idée !',
            'dish' => [
                'name' => 'Poulet basquaise',
                'type' => 'Viande',
                'ingredients' => [['name' => 'poulet', 'category' => 'Viande & poisson']],
            ],
            'added' => false,
        ]);

        Livewire::actingAs($user)
            ->test(AssistantCuisine::class)
            ->assertSee('Poulet basquaise')
            ->assertSee('Ajouter à mes plats');
    }

    public function test_addDish_cree_le_plat_et_ses_ingredients_scopes_a_la_famille(): void
    {
        $user = $this->utilisateurAvecFamille();

        MessageIa::create([
            'family_id' => $user->family_id,
            'role' => 'assistant',
            'content' => 'Voici une idée !',
            'dish' => [
                'name' => 'Poulet basquaise',
                'type' => 'Viande',
                'ingredients' => [['name' => 'poulet', 'category' => 'Viande & poisson']],
            ],
            'added' => false,
        ]);

        Livewire::actingAs($user)
            ->test(AssistantCuisine::class)
            ->call('addDish', 0);

        $this->assertDatabaseHas('dishes', ['name' => 'Poulet basquaise', 'family_id' => $user->family_id]);
        $this->assertTrue(Plat::where('name', 'Poulet basquaise')->first()->ingredients->contains('name', 'poulet'));
    }

    public function test_addDish_reprend_l_origine_le_regime_et_le_sans_gluten_proposes_par_l_ia(): void
    {
        $user = $this->utilisateurAvecFamille();

        MessageIa::create([
            'family_id' => $user->family_id,
            'role' => 'assistant',
            'content' => 'Voici une idée !',
            'dish' => [
                'name' => 'Curry de légumes',
                'type' => 'Légumes',
                'cuisine_origin' => 'Indien',
                'regime' => 'Végétarien',
                'gluten_free' => true,
                'ingredients' => [['name' => 'curry', 'category' => 'Épicerie (sec, conserves, pain)']],
            ],
            'added' => false,
        ]);

        Livewire::actingAs($user)->test(AssistantCuisine::class)->call('addDish', 0);

        $this->assertDatabaseHas('dishes', [
            'name' => 'Curry de légumes',
            'cuisine_origin' => 'Indien',
            'regime' => 'Végétarien',
            'gluten_free' => 1,
        ]);
    }

    public function test_une_origine_et_un_regime_hors_liste_canonique_sont_neutralises(): void
    {
        $user = $this->utilisateurAvecFamille();

        MessageIa::create([
            'family_id' => $user->family_id,
            'role' => 'assistant',
            'content' => 'Voici une idée !',
            'dish' => [
                'name' => 'Truc bizarre 2',
                'type' => 'Légumes',
                'cuisine_origin' => 'OrigineInventee',
                'regime' => 'RegimeInvente',
                'ingredients' => [['name' => 'mystere2', 'category' => 'Fruits & légumes']],
            ],
            'added' => false,
        ]);

        Livewire::actingAs($user)->test(AssistantCuisine::class)->call('addDish', 0);

        $plat = Plat::where('name', 'Truc bizarre 2')->first();
        $this->assertNull($plat->cuisine_origin);
        $this->assertNull($plat->regime);
    }

    public function test_un_type_et_une_categorie_hors_liste_canonique_sont_neutralises(): void
    {
        $user = $this->utilisateurAvecFamille();

        MessageIa::create([
            'family_id' => $user->family_id,
            'role' => 'assistant',
            'content' => 'Voici une idée !',
            'dish' => [
                'name' => 'Truc bizarre',
                'type' => 'TypeInvente',
                'ingredients' => [['name' => 'mystere', 'category' => 'CategorieInventee']],
            ],
            'added' => false,
        ]);

        Livewire::actingAs($user)->test(AssistantCuisine::class)->call('addDish', 0);

        $plat = Plat::where('name', 'Truc bizarre')->first();
        $this->assertNull($plat->type);
        $this->assertNull(Ingredient::where('name', 'mystere')->first()->category);
    }

    public function test_l_anti_doublon_d_ingredients_ignore_les_accents_et_le_pluriel(): void
    {
        $user = $this->utilisateurAvecFamille();
        Ingredient::create(['name' => 'aubergines', 'category' => 'Fruits & légumes']);

        MessageIa::create([
            'family_id' => $user->family_id,
            'role' => 'assistant',
            'content' => 'Voici une idée !',
            'dish' => [
                'name' => 'Gratin',
                'type' => 'Légumes',
                'ingredients' => [['name' => 'Aubergine', 'category' => 'Fruits & légumes']],
            ],
            'added' => false,
        ]);

        Livewire::actingAs($user)->test(AssistantCuisine::class)->call('addDish', 0);

        $this->assertSame(1, Ingredient::where('name', 'aubergines')->count());
        $this->assertSame(1, Plat::where('name', 'Gratin')->first()->ingredients->count());
    }

    public function test_le_rate_limiting_bloque_apres_trop_de_messages(): void
    {
        $user = $this->utilisateurAvecFamille();
        $throttleKey = 'assistant-cuisine|'.$user->family_id;
        RateLimiter::clear($throttleKey);

        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit($throttleKey, 60);
        }

        Livewire::actingAs($user)
            ->test(AssistantCuisine::class)
            ->set('input', 'Une idée de plat ?')
            ->call('send')
            ->assertSee('Trop de messages');
    }
}
