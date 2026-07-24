<div class="pb-24" x-data="{ selected: null }">
    <div class="lg:flex lg:items-start lg:gap-6 max-w-5xl mx-auto px-4 py-6">
        {{-- Barre latérale --}}
        <aside class="lg:w-72 lg:shrink-0 mb-6 lg:mb-0">
            <div class="bg-white rounded-xl shadow-sm p-4 lg:sticky lg:top-4">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Rechercher un plat…"
                    class="w-full rounded-lg border-neutral-200 text-sm mb-3"
                >

                <template x-if="selected">
                    <p class="text-xs text-teal-700 mb-3">
                        <span x-text="selected.name"></span> sélectionné — touche une case pour le placer.
                        <button type="button" class="underline" x-on:click="selected = null">annuler</button>
                    </p>
                </template>

                <h2 class="text-sm font-medium text-neutral-500 mb-2">Plats</h2>
                <ul class="space-y-1 max-h-56 overflow-y-auto mb-4">
                    @foreach ($savoryDishes as $dish)
                        <li
                            draggable="true"
                            x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $dish->id }}')"
                            x-on:click="selected = { id: {{ $dish->id }}, name: '{{ addslashes($dish->name) }}', course: 'plat' }"
                            class="px-2 py-1.5 rounded-lg text-sm cursor-grab"
                            :class="selected && selected.id === {{ $dish->id }} && selected.course === 'plat' ? 'bg-teal-50 text-teal-700' : 'hover:bg-neutral-50'"
                        >
                            {{ $dish->name }}
                        </li>
                    @endforeach
                    @if ($savoryDishes->isEmpty())
                        <li class="text-sm text-neutral-400">Aucun résultat</li>
                    @endif
                </ul>

                <h2 class="text-sm font-medium text-neutral-500 mb-2">Desserts</h2>
                <ul class="space-y-1 max-h-40 overflow-y-auto">
                    @foreach ($dessertDishes as $dish)
                        <li
                            draggable="true"
                            x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $dish->id }}')"
                            x-on:click="selected = { id: {{ $dish->id }}, name: '{{ addslashes($dish->name) }}', course: 'dessert' }"
                            class="px-2 py-1.5 rounded-lg text-sm cursor-grab"
                            :class="selected && selected.id === {{ $dish->id }} && selected.course === 'dessert' ? 'bg-amber-50 text-amber-700' : 'hover:bg-neutral-50'"
                        >
                            {{ $dish->name }}
                        </li>
                    @endforeach
                    @if ($dessertDishes->isEmpty())
                        <li class="text-sm text-neutral-400">Aucun résultat</li>
                    @endif
                </ul>
            </div>
        </aside>

        {{-- Grille de la semaine --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-6">
                <button wire:click="previousWeek" class="px-3 py-2 rounded-lg bg-white shadow-sm text-neutral-600">←</button>
                <h1 class="text-lg font-semibold capitalize">
                    Semaine du {{ $weekStart->locale('fr')->translatedFormat('d F Y') }}
                </h1>
                <button wire:click="nextWeek" class="px-3 py-2 rounded-lg bg-white shadow-sm text-neutral-600">→</button>
            </div>

            <div class="space-y-4">
                @foreach ($days as $day)
                    <div class="bg-white rounded-xl shadow-sm p-4">
                        <h2 class="font-medium mb-3 capitalize">{{ $day->locale('fr')->translatedFormat('l d/m') }}</h2>

                        @foreach (['midi' => 'Midi', 'soir' => 'Soir'] as $slotKey => $slotLabel)
                            <div class="py-2 border-t first:border-t-0 border-neutral-100">
                                <span class="text-sm text-neutral-500">{{ $slotLabel }}</span>

                                <div class="grid grid-cols-5 gap-1.5 mt-2">
                                    @for ($position = 1; $position <= 3; $position++)
                                        @php
                                            $meal = $planned->get($day->toDateString().'-'.$slotKey.'-plat-'.$position);
                                        @endphp
                                        <div
                                            x-on:dragover.prevent
                                            x-on:drop.prevent="$wire.placeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'plat', {{ $position }}, $event.dataTransfer.getData('text/plain'))"
                                            x-on:click="if (selected && selected.course === 'plat') { $wire.placeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'plat', {{ $position }}, selected.id); selected = null }"
                                            class="aspect-square rounded-lg border-2 border-dashed border-neutral-200 flex items-center justify-center text-center p-1 text-[11px] leading-tight overflow-hidden relative"
                                        >
                                            @if ($meal?->dish)
                                                <span>{{ $meal->dish->name }}</span>
                                                <button
                                                    type="button"
                                                    wire:click.stop="removeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'plat', {{ $position }})"
                                                    class="absolute top-0 right-0.5 text-neutral-400"
                                                >×</button>
                                            @else
                                                <span class="text-neutral-300">+</span>
                                            @endif
                                        </div>
                                    @endfor

                                    @for ($position = 1; $position <= 2; $position++)
                                        @php
                                            $meal = $planned->get($day->toDateString().'-'.$slotKey.'-dessert-'.$position);
                                        @endphp
                                        <div
                                            x-on:dragover.prevent
                                            x-on:drop.prevent="$wire.placeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'dessert', {{ $position }}, $event.dataTransfer.getData('text/plain'))"
                                            x-on:click="if (selected && selected.course === 'dessert') { $wire.placeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'dessert', {{ $position }}, selected.id); selected = null }"
                                            class="aspect-square rounded-lg border-2 border-dashed border-amber-200 flex items-center justify-center text-center p-1 text-[11px] leading-tight overflow-hidden relative"
                                        >
                                            @if ($meal?->dish)
                                                <span>{{ $meal->dish->name }}</span>
                                                <button
                                                    type="button"
                                                    wire:click.stop="removeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'dessert', {{ $position }})"
                                                    class="absolute top-0 right-0.5 text-neutral-400"
                                                >×</button>
                                            @else
                                                <span class="text-amber-300">+</span>
                                            @endif
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('courses', ['week' => $weekStart->toDateString()]) }}" class="text-teal-700 font-medium">
                    Voir les courses de cette semaine →
                </a>
            </div>
        </div>
    </div>
</div>
