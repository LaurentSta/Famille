<div class="pb-24" x-data>
    <div class="flex items-stretch gap-3 lg:gap-6 max-w-5xl mx-auto px-4 py-6">
        {{-- Barre latérale : catalogue d'ingrédients --}}
        <aside class="w-28 sm:w-56 lg:w-72 shrink-0">
            <div class="bg-gradient-to-b from-brand-orange to-brand-red rounded-xl shadow-lg p-1 sticky top-4 z-10">
                <div class="bg-white rounded-lg p-2.5 sm:p-4">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher un ingrédient…"
                        class="w-full rounded-lg border-2 border-brand-orange/30 text-sm mb-3 focus:border-brand-orange focus:ring-brand-orange"
                    >

                    <h2 class="inline-block text-xs font-bold uppercase tracking-wide text-white bg-brand-mustard rounded-full px-2.5 py-1 mb-2">Ingrédients</h2>
                    <ul class="space-y-1 max-h-72 overflow-y-auto mb-4">
                        @foreach ($catalog as $ingredient)
                            @php $inList = $effectiveIds->contains($ingredient->id); @endphp
                            <li
                                wire:click="{{ $inList ? 'removeIngredient' : 'addIngredient' }}({{ $ingredient->id }})"
                                class="px-2 py-1.5 rounded-lg text-sm cursor-pointer border-l-4 {{ $inList ? 'bg-brand-orange text-white border-brand-orange' : 'border-transparent hover:bg-brand-mustard/10 hover:border-brand-mustard/50' }}"
                            >
                                {{ $ingredient->name }}
                            </li>
                        @endforeach
                        @if ($catalog->isEmpty())
                            <li class="text-sm text-neutral-400">Aucun résultat</li>
                        @endif
                    </ul>

                    <form wire:submit.prevent="addNewIngredient" class="flex gap-1">
                        <input
                            type="text"
                            wire:model="newIngredientName"
                            placeholder="Autre ingrédient…"
                            class="flex-1 min-w-0 rounded-lg border-neutral-200 text-sm"
                        >
                        <button type="submit" class="cursor-pointer shrink-0 px-2.5 rounded-lg bg-brand-red text-white text-sm font-medium">+</button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Liste --}}
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-semibold mb-4">Liste de courses</h1>

            <div class="flex items-center justify-between mb-3">
                <button wire:click="previousYear" class="cursor-pointer px-3 py-2 rounded-lg bg-white shadow-sm text-neutral-600">←</button>
                <span class="text-sm font-medium text-neutral-500">{{ $yearStart->format('Y') }}</span>
                <button wire:click="nextYear" class="cursor-pointer px-3 py-2 rounded-lg bg-white shadow-sm text-neutral-600">→</button>
            </div>

            <div class="flex gap-1.5 overflow-x-auto pb-1 mb-4 -mx-1 px-1">
                @foreach ($monthTabs as $tab)
                    @php $isActive = $tab['start']->toDateString() === $monthStart->toDateString(); @endphp
                    <button
                        wire:click="selectMonth('{{ $tab['start']->toDateString() }}')"
                        @if ($isActive) x-init="$el.scrollIntoView({inline: 'center', block: 'nearest'})" @endif
                        class="cursor-pointer shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs whitespace-nowrap capitalize {{ $isActive ? 'bg-brand-orange text-white' : 'bg-white text-neutral-600 shadow-sm' }}"
                    >
                        <span class="inline-block size-1.5 rounded-full {{ $tab['filled'] > 0 ? ($isActive ? 'bg-white' : 'bg-brand-orange') : ($isActive ? 'bg-white/40' : 'bg-neutral-300') }}"></span>
                        {{ $tab['start']->locale('fr')->translatedFormat('M') }}
                        @if ($tab['filled'] > 0)
                            <span class="{{ $isActive ? 'text-white/80' : 'text-neutral-400' }}">({{ $tab['filled'] }})</span>
                        @endif
                    </button>
                @endforeach
            </div>

            <p class="text-sm text-neutral-500 mb-4 capitalize">
                {{ $monthStart->locale('fr')->translatedFormat('F Y') }}
            </p>

            @if ($ingredients->isEmpty())
                <p class="text-neutral-500">
                    Liste vide pour ce mois.
                    <a href="{{ route('planning', ['week' => $monthStart->toDateString()]) }}" class="text-brand-orange font-medium">
                        Aller au planning →
                    </a>
                    ou ajoute un ingrédient depuis la barre latérale.
                </p>
            @else
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <table class="w-full text-sm">
                        <tbody>
                            @foreach ($ingredients as $category => $items)
                                <tr>
                                    <td colspan="3" class="bg-brand-terracotta/15 text-brand-brick font-semibold text-xs uppercase tracking-wide px-4 py-1.5">
                                        {{ $category ?? 'Autre' }}
                                    </td>
                                </tr>
                                @foreach ($items as $ingredient)
                                    <tr class="border-t border-neutral-100">
                                        <td class="pl-4 pr-2 py-2 w-8">
                                            <input
                                                type="checkbox"
                                                wire:click="toggle({{ $ingredient->id }})"
                                                @checked($ingredient->in_stock)
                                                class="size-5 rounded border-neutral-300 text-brand-orange"
                                            >
                                        </td>
                                        <td class="px-2 py-2 {{ $ingredient->in_stock ? 'line-through text-neutral-400' : '' }}">
                                            {{ $ingredient->name }}
                                        </td>
                                        <td class="pl-2 pr-4 py-2 w-10 text-right">
                                            <button
                                                type="button"
                                                wire:click="removeIngredient({{ $ingredient->id }})"
                                                class="cursor-pointer size-7 inline-flex items-center justify-center rounded-full text-neutral-400 hover:bg-brand-red/10 hover:text-brand-red"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5">
                                                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
