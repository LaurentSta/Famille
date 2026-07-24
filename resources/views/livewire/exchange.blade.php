<div class="pb-24">
    <div class="max-w-3xl mx-auto px-4 py-6">
        <h1 class="text-lg font-semibold mb-1">Échanges entre familles</h1>
        <p class="text-sm text-neutral-500 mb-4">Découvre les plats des autres familles et ajoute-les à ta banque en un clic.</p>

        <div class="bg-white rounded-xl shadow-sm p-3 mb-4">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher un plat…"
                class="w-full rounded-lg border-2 border-brand-orange/30 text-sm mb-3 focus:border-brand-orange focus:ring-brand-orange"
            >

            <div class="flex gap-1.5">
                @foreach (['all' => 'Tous', 'plat' => 'Plats', 'dessert' => 'Desserts'] as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('filter', '{{ $value }}')"
                        class="cursor-pointer flex-1 text-xs font-medium rounded-full px-2 py-1.5 {{ $filter === $value ? 'bg-brand-mustard text-white' : 'bg-neutral-100 text-neutral-500' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="space-y-2">
            @foreach ($dishes as $dish)
                @php $alreadyAdded = $ownNames->contains(mb_strtolower($dish->name)); @endphp
                <div class="bg-white rounded-xl shadow-sm p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium">{{ $dish->emoji }} {{ $dish->name }}</p>
                            <p class="text-xs text-neutral-400 mt-0.5">Famille {{ $dish->family->name }}</p>
                            @if ($dish->ingredients->isNotEmpty())
                                <p class="text-xs text-neutral-500 mt-1.5">
                                    {{ $dish->ingredients->map(fn ($i) => $i->emoji.' '.$i->name)->join(', ') }}
                                </p>
                            @endif
                        </div>

                        @if ($alreadyAdded)
                            <span class="shrink-0 text-xs text-brand-mustard font-medium whitespace-nowrap">✓ Ajouté</span>
                        @else
                            <button
                                type="button"
                                wire:click="addDish({{ $dish->id }})"
                                class="cursor-pointer shrink-0 text-xs bg-brand-mustard text-white font-medium rounded-full px-3 py-1.5 whitespace-nowrap"
                            >
                                Ajouter
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach

            @if ($dishes->isEmpty())
                <p class="text-sm text-neutral-400 text-center py-6">
                    Aucun plat partagé par d'autres familles pour le moment.
                </p>
            @endif
        </div>
    </div>
</div>
