<?php

namespace App\Livewire;

use App\Models\Dish;
use App\Models\Ingredient;
use App\Services\DeepSeekClient;
use Livewire\Component;
use Throwable;

class AiChat extends Component
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

    public array $messages = [];

    public string $input = '';

    public function send(): void
    {
        $text = trim($this->input);

        if ($text === '') {
            return;
        }

        $this->messages[] = ['role' => 'user', 'content' => $text, 'dish' => null, 'added' => false];
        $this->input = '';

        $history = collect($this->messages)
            ->slice(-12)
            ->map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']])
            ->prepend(['role' => 'system', 'content' => self::SYSTEM_PROMPT])
            ->values()
            ->all();

        try {
            $reply = app(DeepSeekClient::class)->chat($history);
            [$displayText, $dish] = $this->extractDish($reply);

            $this->messages[] = [
                'role' => 'assistant',
                'content' => $displayText,
                'dish' => $dish,
                'added' => false,
            ];
        } catch (Throwable $e) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => "Désolé, une erreur est survenue : {$e->getMessage()}",
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

        $dish = Dish::create([
            'family_id' => auth()->user()->family_id,
            'name' => $dishData['name'],
            'type' => $dishData['type'] ?? null,
        ]);

        $ingredientIds = collect($dishData['ingredients'])
            ->map(function ($ingredient) {
                $name = is_array($ingredient) ? ($ingredient['name'] ?? null) : $ingredient;
                $category = is_array($ingredient) ? ($ingredient['category'] ?? null) : null;

                if (blank($name)) {
                    return null;
                }

                return Ingredient::firstOrCreate(['name' => $name], ['category' => $this->normalizeCategory($category)])->id;
            })
            ->filter()
            ->all();

        $dish->ingredients()->sync($ingredientIds);

        $this->messages[$index]['added'] = true;
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
        return view('livewire.ai-chat', [
            'configured' => filled(config('services.deepseek.key')),
        ])->layout('layouts.app');
    }
}
