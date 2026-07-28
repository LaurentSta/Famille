<?php

namespace App\Livewire;

use App\Models\MessageIa;
use App\Models\Plat;
use App\Models\Ingredient;
use App\Services\ClientDeepSeek;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;
use Throwable;

class AssistantCuisine extends Component
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
Tu es un assistant culinaire pour une application familiale de planning de repas. Tu ne dois parler QUE de cuisine : recettes, ingrédients, techniques, idées de plats, suggestions de repas. Si on te pose une question qui n'a aucun rapport avec la cuisine, réponds poliment que tu ne peux parler que de cuisine et invite à reformuler une question culinaire.

Réponds de façon concise (quelques phrases).

Quand tu proposes un plat complet et clairement défini (nom + liste d'ingrédients), termine ta réponse par un bloc au format suivant, et rien d'autre après ce bloc :

```dish
{"name": "Nom du plat", "type": "Viande", "ingredients": [{"name": "tomates", "category": "Fruits & légumes"}]}
```

Valeurs possibles pour "type" : Œufs, Viande, Poisson, Légumes, Salade, Plat complet, Pâtes, Dessert.
Valeurs possibles pour "category" (par ingrédient) : Frais (laitages, œufs, fromage), Fruits & légumes, Viande & poisson, Épicerie (sec, conserves, pain).

N'inclus ce bloc que lorsque le plat est clairement défini. Pour une simple discussion ou suggestion ouverte, ne l'inclus pas.
PROMPT;

    private const LIMITE_MESSAGES_PAR_MINUTE = 10;

    public array $messages = [];

    public string $input = '';

    public function mount(): void
    {
        $this->messages = MessageIa::where('family_id', auth()->user()->family_id)
            ->orderBy('id')
            ->get()
            ->map(fn (MessageIa $m) => $this->toArray($m))
            ->all();
    }

    public function send(): void
    {
        $text = trim($this->input);

        if ($text === '') {
            return;
        }

        $this->input = '';
        $this->ask($text);
    }

    public function suggestFromStock(): void
    {
        $familyId = auth()->user()->family_id;

        $stockedNames = Ingredient::whereHas(
            'stocks',
            fn ($q) => $q->where('family_id', $familyId)->where('in_stock', true)
        )->orderBy('name')->pluck('name');

        if ($stockedNames->isEmpty()) {
            // Placards vides : pas d'appel à l'IA (coût inutile), on répond directement.
            $this->messages[] = [
                'id' => null,
                'role' => 'user',
                'content' => 'Je ne sais pas quoi manger !',
                'dish' => null,
                'added' => false,
            ];
            $this->messages[] = [
                'id' => null,
                'role' => 'assistant',
                'content' => 'Tes placards sont vides ! Coche ce que tu as déjà chez toi dans la liste de courses, puis reviens me demander une idée.',
                'dish' => null,
                'added' => false,
            ];

            return;
        }

        $text = "Je ne sais pas quoi manger ! Voici ce que j'ai en stock : {$stockedNames->implode(', ')}. Propose-moi 2 ou 3 idées de plats qui utilisent en priorité ces ingrédients.";

        $this->ask($text);
    }

    private function ask(string $text): void
    {
        $familyId = auth()->user()->family_id;
        $throttleKey = 'assistant-cuisine|'.$familyId;

        if (RateLimiter::tooManyAttempts($throttleKey, self::LIMITE_MESSAGES_PAR_MINUTE)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->messages[] = [
                'id' => null,
                'role' => 'assistant',
                'content' => "Trop de messages envoyés d'affilée. Réessaie dans {$seconds} secondes.",
                'dish' => null,
                'added' => false,
            ];

            return;
        }

        RateLimiter::hit($throttleKey, 60);

        $userMessage = MessageIa::create([
            'family_id' => $familyId,
            'role' => 'user',
            'content' => $text,
        ]);
        $this->messages[] = $this->toArray($userMessage);

        $history = collect($this->messages)
            ->slice(-12)
            ->map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']])
            ->prepend(['role' => 'system', 'content' => self::SYSTEM_PROMPT])
            ->values()
            ->all();

        try {
            $reply = app(ClientDeepSeek::class)->chat($history);
            [$displayText, $dish] = $this->extractDish($reply);

            $assistantMessage = MessageIa::create([
                'family_id' => $familyId,
                'role' => 'assistant',
                'content' => $displayText,
                'dish' => $dish,
            ]);
            $this->messages[] = $this->toArray($assistantMessage);
        } catch (Throwable $e) {
            Log::error('assistant_cuisine.echec_appel_ia', [
                'family_id' => $familyId,
                'message' => $e->getMessage(),
                'classe_exception' => $e::class,
            ]);

            $this->messages[] = [
                'id' => null,
                'role' => 'assistant',
                'content' => 'Désolé, une erreur est survenue. Réessaie dans un instant.',
                'dish' => null,
                'added' => false,
            ];
        }
    }

    public function addDish(int $index): void
    {
        $entry = $this->messages[$index] ?? null;

        if (! $entry || empty($entry['dish']) || $entry['added']) {
            return;
        }

        $dishData = $entry['dish'];

        $dish = Plat::create([
            'family_id' => auth()->user()->family_id,
            'name' => $dishData['name'],
            'type' => $this->normalizeType($dishData['type'] ?? null),
        ]);

        $ingredientIds = collect($dishData['ingredients'])
            ->map(function ($ingredient) {
                $name = is_array($ingredient) ? ($ingredient['name'] ?? null) : $ingredient;
                $category = is_array($ingredient) ? ($ingredient['category'] ?? null) : null;

                if (blank($name)) {
                    return null;
                }

                return $this->resolveIngredient($name, $category)->id;
            })
            ->filter()
            ->all();

        $dish->ingredients()->sync($ingredientIds);

        $this->messages[$index]['added'] = true;

        if (! empty($entry['id'])) {
            MessageIa::where('id', $entry['id'])->update(['added' => true]);
        }
    }

    private function toArray(MessageIa $message): array
    {
        return [
            'id' => $message->id,
            'role' => $message->role,
            'content' => $message->content,
            'dish' => $message->dish,
            'added' => $message->added,
        ];
    }

    /**
     * L'IA a tendance à recréer un ingrédient déjà existant sous une forme
     * légèrement différente (singulier/pluriel, casse, accents) au lieu de
     * réutiliser l'existant — ex. "aubergine" alors que "aubergines" existe
     * déjà. On rapproche via une clé normalisée (sans accent, 's' final
     * retiré) plutôt que de charger tous les ingrédients en modèles complets.
     *
     * Limite connue : les pluriels irréguliers français (ex. "chou"/"choux")
     * ne sont pas couverts par un simple rtrim('s') — risque résiduel faible
     * vu le périmètre (catalogue familial, pas industriel).
     */
    private function resolveIngredient(string $name, ?string $category): Ingredient
    {
        $cle = $this->cleNormalisationIngredient($name);

        $idExistant = Ingredient::query()
            ->pluck('name', 'id')
            ->search(fn (string $nom) => $this->cleNormalisationIngredient($nom) === $cle);

        if ($idExistant !== false) {
            return Ingredient::find($idExistant);
        }

        return Ingredient::create(['name' => $name, 'category' => $this->normalizeCategory($category)]);
    }

    private function cleNormalisationIngredient(string $name): string
    {
        return rtrim(Str::of($name)->trim()->ascii()->lower()->toString(), 's');
    }

    /**
     * Le type retourné par l'IA doit correspondre exactement à l'une des
     * valeurs canoniques de config('emoji.dish_types'), sinon on stocke null
     * plutôt que de polluer la colonne type (utilisée pour filtrer les plats
     * dans le planning) — l'écart est loggé pour ajuster le prompt si besoin.
     */
    private function normalizeType(?string $type): ?string
    {
        if (blank($type)) {
            return null;
        }

        if (array_key_exists($type, config('emoji.dish_types'))) {
            return $type;
        }

        Log::warning('assistant_cuisine.type_hors_liste', [
            'family_id' => auth()->user()->family_id,
            'type_recu' => $type,
        ]);

        return null;
    }

    /**
     * L'IA ne respecte pas toujours à la lettre les libellés exacts des
     * catégories (ex. "Épicerie" au lieu de "Épicerie (sec, conserves,
     * pain)") : on rapproche du libellé canonique le plus proche.
     */
    private function normalizeCategory(?string $category): ?string
    {
        if (blank($category)) {
            return null;
        }

        $canonical = array_keys(config('emoji.ingredient_category_default'));
        $needle = mb_strtolower(trim($category));

        foreach ($canonical as $label) {
            if (str_contains(mb_strtolower($label), $needle)) {
                return $label;
            }
        }

        Log::warning('assistant_cuisine.categorie_hors_liste', [
            'family_id' => auth()->user()->family_id,
            'categorie_recue' => $category,
        ]);

        return null;
    }

    /**
     * @return array{0: string, 1: array|null}
     */
    private function extractDish(string $reply): array
    {
        if (! preg_match('/```dish\s*(\{.*?\})\s*```/s', $reply, $matches)) {
            return [$reply, null];
        }

        $displayText = trim(str_replace($matches[0], '', $reply));
        $data = json_decode($matches[1], true);

        if (! is_array($data) || empty($data['name']) || empty($data['ingredients'])) {
            return [$displayText !== '' ? $displayText : $reply, null];
        }

        return [$displayText, $data];
    }

    public function render()
    {
        return view('livewire.assistant-cuisine', [
            'configured' => filled(config('services.deepseek.key')),
        ])->layout('layouts.app');
    }
}
