<div class="px-4 pt-6 pb-24 max-w-2xl w-full mx-auto space-y-4">
    <h1 class="text-lg font-semibold">Gestion</h1>

    @if ($messageSucces)
        <div class="bg-brand-mustard/10 text-brand-brick text-sm rounded-xl px-4 py-2.5" wire:key="succes-{{ $messageSucces }}">
            {{ $messageSucces }}
        </div>
    @endif

    @error('suppressionIngredient')
        <div class="bg-brand-red/10 text-brand-red text-sm rounded-xl px-4 py-2.5">{{ $message }}</div>
    @enderror

    <div class="flex gap-2">
        <button
            type="button"
            wire:click="changerOnglet('plats')"
            class="cursor-pointer rounded-full px-4 py-2 text-sm font-medium {{ $onglet === 'plats' ? 'bg-brand-orange text-white' : 'bg-neutral-100 text-neutral-600' }}"
        >
            Recettes
        </button>
        <button
            type="button"
            wire:click="changerOnglet('ingredients')"
            class="cursor-pointer rounded-full px-4 py-2 text-sm font-medium {{ $onglet === 'ingredients' ? 'bg-brand-orange text-white' : 'bg-neutral-100 text-neutral-600' }}"
        >
            Ingrédients
        </button>
    </div>

    @php
        $proseRecette = 'text-sm text-neutral-700 space-y-2 [&_h1]:text-base [&_h1]:font-semibold [&_h1]:text-brand-brick [&_h2]:text-base [&_h2]:font-semibold [&_h2]:text-brand-brick [&_h3]:text-sm [&_h3]:font-semibold [&_ul]:list-disc [&_ul]:list-inside [&_ol]:list-decimal [&_ol]:list-inside [&_li]:mt-0.5 [&_strong]:font-semibold [&_a]:text-brand-orange [&_a]:underline';
    @endphp

    @if ($onglet === 'plats')
        <div class="grid gap-4 md:grid-cols-[260px_1fr] items-start">
            {{-- Colonne de gauche : liste des recettes --}}
            <div class="{{ ($platSelectionneId || $formulairePlatOuvert) ? 'hidden md:block' : '' }} space-y-3">
                <div class="flex gap-2">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="recherchePlat"
                        placeholder="Rechercher une recette…"
                        class="flex-1 rounded-lg border-2 border-brand-orange/30 text-sm focus:border-brand-orange focus:ring-brand-orange"
                    >
                    <button
                        type="button"
                        wire:click="nouveauPlat"
                        class="cursor-pointer shrink-0 bg-brand-orange text-white rounded-full px-4 py-2 text-sm font-medium"
                    >
                        + Nouvelle
                    </button>
                </div>

                <div class="space-y-2">
                    @foreach ($plats as $plat)
                        <div wire:key="plat-{{ $plat->id }}" class="flex items-stretch gap-1">
                            <button
                                type="button"
                                wire:click="selectionnerPlat({{ $plat->id }})"
                                class="cursor-pointer flex-1 text-left bg-white rounded-xl shadow-sm p-3 {{ $platSelectionneId === $plat->id ? 'ring-2 ring-brand-orange' : '' }}"
                            >
                                <p class="font-semibold text-brand-brick text-sm">{{ $plat->emoji }} {{ $plat->name }}</p>
                                <p class="text-xs text-neutral-400 mt-0.5">{{ $plat->ingredients->count() }} ingrédient(s)</p>
                            </button>
                            <button
                                type="button"
                                wire:click.stop="supprimerPlat({{ $plat->id }})"
                                wire:confirm="Supprimer « {{ $plat->name }} » ?"
                                class="cursor-pointer shrink-0 text-xs font-medium text-brand-red hover:underline px-2"
                            >
                                Suppr.
                            </button>
                        </div>
                    @endforeach
                    @if ($plats->isEmpty())
                        <p class="text-sm text-neutral-400">Aucune recette.</p>
                    @endif
                </div>
            </div>

            {{-- Colonne de droite : detail / edition de la recette selectionnee --}}
            <div class="{{ (!$platSelectionneId && !$formulairePlatOuvert) ? 'hidden md:block' : '' }} space-y-3">
                @if ($formulairePlatOuvert)
                    <button type="button" wire:click="fermerDetailPlat" class="cursor-pointer md:hidden text-sm font-medium text-neutral-500 hover:underline">
                        ← Retour
                    </button>

                    <form wire:submit.prevent="enregistrerPlat" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Nom</label>
                            <input type="text" wire:model="platNom" class="w-full rounded-lg border-neutral-200 text-sm">
                            @error('platNom') <p class="text-xs text-brand-red mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Type</label>
                            <select wire:model="platType" class="w-full rounded-lg border-neutral-200 text-sm">
                                <option value="">—</option>
                                @foreach ($typesDisponibles as $type => $emoji)
                                    <option value="{{ $type }}">{{ $emoji }} {{ $type }}</option>
                                @endforeach
                            </select>
                            @error('platType') <p class="text-xs text-brand-red mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Origine / cuisine</label>
                            <select wire:model="platCuisineOrigin" class="w-full rounded-lg border-neutral-200 text-sm">
                                <option value="">—</option>
                                @foreach ($originesDisponibles as $origine => $emoji)
                                    <option value="{{ $origine }}">{{ $emoji }} {{ $origine }}</option>
                                @endforeach
                            </select>
                            @error('platCuisineOrigin') <p class="text-xs text-brand-red mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Régime</label>
                            <select wire:model="platRegime" class="w-full rounded-lg border-neutral-200 text-sm">
                                <option value="">—</option>
                                @foreach ($regimesDisponibles as $regime => $emoji)
                                    <option value="{{ $regime }}">{{ $emoji }} {{ $regime }}</option>
                                @endforeach
                            </select>
                            @error('platRegime') <p class="text-xs text-brand-red mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-neutral-600">
                                <input type="checkbox" wire:model="platLowCarb" class="size-5 rounded border-neutral-300 text-brand-orange">
                                Low carb
                            </label>
                            <label class="flex items-center gap-2 text-sm text-neutral-600">
                                <input type="checkbox" wire:model="platGlutenFree" class="size-5 rounded border-neutral-300 text-brand-orange">
                                Sans gluten
                            </label>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Suggestion de dessert</label>
                            <input type="text" wire:model="platDessertSuggestion" class="w-full rounded-lg border-neutral-200 text-sm">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-medium text-neutral-500">Recette (Markdown)</label>
                                <button type="button" wire:click="$toggle('notesApercu')" class="cursor-pointer text-xs font-medium text-brand-orange hover:underline">
                                    {{ $notesApercu ? 'Éditer' : 'Aperçu' }}
                                </button>
                            </div>
                            @if ($notesApercu)
                                <div class="{{ $proseRecette }} rounded-lg border border-neutral-200 p-3 min-h-32">
                                    @if ($platNotes)
                                        {!! \Illuminate\Support\Str::markdown($platNotes) !!}
                                    @else
                                        <p class="text-neutral-400">Rien à prévisualiser.</p>
                                    @endif
                                </div>
                            @else
                                <textarea wire:model="platNotes" rows="8" placeholder="# Étapes&#10;1. …&#10;2. …" class="w-full rounded-lg border-neutral-200 text-sm font-mono"></textarea>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Ingrédients</label>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="rechercheIngredientDuPlat"
                                placeholder="Filtrer les ingrédients…"
                                class="w-full rounded-lg border-neutral-200 text-sm mb-2"
                            >
                            <div class="max-h-48 overflow-y-auto space-y-1 border border-neutral-100 rounded-lg p-2">
                                @foreach ($ingredientsDisponibles as $ingredient)
                                    <label class="flex items-center gap-2 text-sm py-0.5">
                                        <input
                                            type="checkbox"
                                            wire:model="platIngredientIds"
                                            value="{{ $ingredient->id }}"
                                            class="size-5 rounded border-neutral-300 text-brand-orange"
                                        >
                                        {{ $ingredient->emoji }} {{ $ingredient->name }}
                                    </label>
                                @endforeach
                                @if ($ingredientsDisponibles->isEmpty())
                                    <p class="text-sm text-neutral-400">Aucun ingrédient trouvé.</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex gap-2 pt-1">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="cursor-pointer bg-brand-orange text-white rounded-full px-4 py-2 text-sm font-medium"
                            >
                                Enregistrer
                            </button>
                            <button
                                type="button"
                                wire:click="annulerPlat"
                                class="cursor-pointer bg-neutral-100 text-neutral-600 rounded-full px-4 py-2 text-sm font-medium"
                            >
                                Annuler
                            </button>
                        </div>
                    </form>
                @elseif ($platSelectionne)
                    <button type="button" wire:click="fermerDetailPlat" class="cursor-pointer md:hidden text-sm font-medium text-neutral-500 hover:underline">
                        ← Retour
                    </button>

                    <div class="bg-white rounded-xl shadow-sm p-4 space-y-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-semibold text-brand-brick text-lg">{{ $platSelectionne->emoji }} {{ $platSelectionne->name }}</p>
                                <p class="text-xs text-neutral-400 mt-0.5">
                                    {{ $platSelectionne->type ?? '—' }}
                                    @if ($platSelectionne->cuisine_origin) · {{ $platSelectionne->origin_emoji }} {{ $platSelectionne->cuisine_origin }} @endif
                                    @if ($platSelectionne->regime) · {{ $platSelectionne->regime_emoji }} {{ $platSelectionne->regime }} @endif
                                    @if ($platSelectionne->low_carb) · Low carb @endif
                                    @if ($platSelectionne->gluten_free) · Sans gluten @endif
                                </p>
                            </div>
                            <button type="button" wire:click="editerPlat({{ $platSelectionne->id }})" class="cursor-pointer shrink-0 text-xs font-medium text-brand-orange hover:underline">
                                Modifier
                            </button>
                        </div>

                        @if ($platSelectionne->dessert_suggestion)
                            <p class="text-sm text-neutral-500">🍰 {{ $platSelectionne->dessert_suggestion }}</p>
                        @endif

                        <div>
                            <p class="text-xs font-medium text-neutral-500 mb-1">Ingrédients</p>
                            <ul class="list-disc list-inside text-sm text-neutral-600">
                                @foreach ($platSelectionne->ingredients as $ingredient)
                                    <li>{{ $ingredient->name }}</li>
                                @endforeach
                                @if ($platSelectionne->ingredients->isEmpty())
                                    <li class="list-none text-neutral-400">Aucun ingrédient</li>
                                @endif
                            </ul>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-neutral-500 mb-1">Recette</p>
                            @if ($platSelectionne->notes)
                                <div class="{{ $proseRecette }}">
                                    {!! \Illuminate\Support\Str::markdown($platSelectionne->notes) !!}
                                </div>
                            @else
                                <p class="text-sm text-neutral-400">Aucune recette renseignée.</p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="hidden md:flex items-center justify-center h-40 text-sm text-neutral-400">
                        Sélectionne une recette pour l'afficher.
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="flex gap-2">
            <input
                type="text"
                wire:model.live.debounce.300ms="rechercheIngredient"
                placeholder="Rechercher un ingrédient…"
                class="flex-1 rounded-lg border-2 border-brand-orange/30 text-sm focus:border-brand-orange focus:ring-brand-orange"
            >
            <button
                type="button"
                wire:click="nouvelIngredient"
                class="cursor-pointer shrink-0 bg-brand-orange text-white rounded-full px-4 py-2 text-sm font-medium"
            >
                + Nouveau
            </button>
        </div>

        @if ($formulaireIngredientOuvert)
            <form wire:submit.prevent="enregistrerIngredient" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-neutral-500 mb-1">Nom</label>
                    <input type="text" wire:model="ingredientNom" class="w-full rounded-lg border-neutral-200 text-sm">
                    @error('ingredientNom') <p class="text-xs text-brand-red mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-500 mb-1">Catégorie</label>
                    <select wire:model="ingredientCategorie" class="w-full rounded-lg border-neutral-200 text-sm">
                        <option value="">—</option>
                        @foreach ($categoriesDisponibles as $categorie => $emoji)
                            <option value="{{ $categorie }}">{{ $emoji }} {{ $categorie }}</option>
                        @endforeach
                    </select>
                    @error('ingredientCategorie') <p class="text-xs text-brand-red mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-2 pt-1">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="cursor-pointer bg-brand-orange text-white rounded-full px-4 py-2 text-sm font-medium"
                    >
                        Enregistrer
                    </button>
                    <button
                        type="button"
                        wire:click="annulerIngredient"
                        class="cursor-pointer bg-neutral-100 text-neutral-600 rounded-full px-4 py-2 text-sm font-medium"
                    >
                        Annuler
                    </button>
                </div>
            </form>
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-neutral-400 uppercase tracking-wide">
                        <th class="px-4 py-2 text-left font-medium">Ingrédient</th>
                        <th class="px-2 py-2 text-left font-medium">Catégorie</th>
                        <th class="px-2 py-2 w-24"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ingredients as $ingredient)
                        <tr wire:key="ingredient-{{ $ingredient->id }}" class="border-t border-neutral-100">
                            <td class="px-4 py-2">{{ $ingredient->emoji }} {{ $ingredient->name }}</td>
                            <td class="px-2 py-2 text-neutral-500">{{ $ingredient->category ?? '—' }}</td>
                            <td class="px-2 py-2 text-right whitespace-nowrap">
                                <button type="button" wire:click="editerIngredient({{ $ingredient->id }})" class="cursor-pointer text-xs font-medium text-brand-orange hover:underline">
                                    Modifier
                                </button>
                                <button
                                    type="button"
                                    wire:click="supprimerIngredient({{ $ingredient->id }})"
                                    wire:confirm="Supprimer « {{ $ingredient->name }} » ?"
                                    class="cursor-pointer text-xs font-medium text-brand-red hover:underline ml-2"
                                >
                                    Supprimer
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    @if ($ingredients->isEmpty())
                        <tr><td colspan="3" class="px-4 py-3 text-neutral-400">Aucun ingrédient.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endif
</div>
