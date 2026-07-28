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

    @if ($onglet === 'plats')
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

        @if ($formulairePlatOuvert)
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

                <label class="flex items-center gap-2 text-sm text-neutral-600">
                    <input type="checkbox" wire:model="platLowCarb" class="size-5 rounded border-neutral-300 text-brand-orange">
                    Low carb
                </label>

                <div>
                    <label class="block text-xs font-medium text-neutral-500 mb-1">Suggestion de dessert</label>
                    <input type="text" wire:model="platDessertSuggestion" class="w-full rounded-lg border-neutral-200 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-500 mb-1">Notes</label>
                    <textarea wire:model="platNotes" rows="2" class="w-full rounded-lg border-neutral-200 text-sm"></textarea>
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
        @endif

        <div class="space-y-2">
            @foreach ($plats as $plat)
                <div wire:key="plat-{{ $plat->id }}" class="bg-white rounded-xl shadow-sm p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold text-brand-brick">{{ $plat->emoji }} {{ $plat->name }}</p>
                            <ul class="list-disc list-inside text-xs text-neutral-500 mt-1">
                                @foreach ($plat->ingredients as $ingredient)
                                    <li>{{ $ingredient->name }}</li>
                                @endforeach
                                @if ($plat->ingredients->isEmpty())
                                    <li class="list-none text-neutral-400">Aucun ingrédient</li>
                                @endif
                            </ul>
                        </div>
                        <div class="flex gap-1 shrink-0">
                            <button type="button" wire:click="editerPlat({{ $plat->id }})" class="cursor-pointer text-xs font-medium text-brand-orange hover:underline">
                                Modifier
                            </button>
                            <button
                                type="button"
                                wire:click="supprimerPlat({{ $plat->id }})"
                                wire:confirm="Supprimer « {{ $plat->name }} » ?"
                                class="cursor-pointer text-xs font-medium text-brand-red hover:underline"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
            @if ($plats->isEmpty())
                <p class="text-sm text-neutral-400">Aucune recette.</p>
            @endif
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
