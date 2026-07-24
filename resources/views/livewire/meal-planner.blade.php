<div class="pb-24" x-data="{ selected: null }">
    <div class="flex items-stretch gap-3 lg:gap-6 max-w-5xl mx-auto px-4 py-6">
        {{-- Barre latérale --}}
        <aside class="w-28 sm:w-56 lg:w-72 shrink-0">
            <div class="bg-gradient-to-b from-brand-orange to-brand-red rounded-xl shadow-lg p-1 sticky top-4 z-10">
                <div class="bg-white rounded-lg p-2.5 sm:p-4">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher un plat…"
                        class="w-full rounded-lg border-2 border-brand-orange/30 text-sm mb-3 focus:border-brand-orange focus:ring-brand-orange"
                    >

                    <template x-if="selected">
                        <p class="text-xs text-white bg-brand-orange rounded-lg px-2 py-1.5 mb-3">
                            <span class="font-semibold" x-text="selected.name"></span>
                            <template x-if="selected.ingredients"><span x-text="' — ' + selected.ingredients"></span></template>
                            <button type="button" class="cursor-pointer underline block mt-1" x-on:click="selected = null">annuler</button>
                        </p>
                    </template>

                    <h2 class="inline-block text-xs font-bold uppercase tracking-wide text-white bg-brand-mustard rounded-full px-2.5 py-1 mb-2">Plats</h2>
                    <ul class="space-y-1 max-h-56 overflow-y-auto mb-4">
                        @foreach ($savoryDishes as $dish)
                            <li
                                draggable="true"
                                x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $dish->id }}')"
                                x-on:click="selected = { id: {{ $dish->id }}, name: '{{ addslashes($dish->name) }}', course: 'plat', ingredients: '{{ addslashes($dish->ingredients->pluck('name')->join(', ')) }}' }"
                                class="px-2 py-1.5 rounded-lg text-sm cursor-grab border-l-4"
                                :class="selected && selected.id === {{ $dish->id }} && selected.course === 'plat' ? 'bg-brand-mustard text-white border-brand-mustard' : 'border-transparent hover:bg-brand-mustard/10 hover:border-brand-mustard/50'"
                            >
                                {{ $dish->name }}
                            </li>
                        @endforeach
                        @if ($savoryDishes->isEmpty())
                            <li class="text-sm text-neutral-400">Aucun résultat</li>
                        @endif
                    </ul>

                    <h2 class="inline-block text-xs font-bold uppercase tracking-wide text-white bg-brand-red rounded-full px-2.5 py-1 mb-2">Desserts</h2>
                    <ul class="space-y-1 max-h-40 overflow-y-auto">
                        @foreach ($dessertDishes as $dish)
                            <li
                                draggable="true"
                                x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $dish->id }}')"
                                x-on:click="selected = { id: {{ $dish->id }}, name: '{{ addslashes($dish->name) }}', course: 'dessert', ingredients: '{{ addslashes($dish->ingredients->pluck('name')->join(', ')) }}' }"
                                class="px-2 py-1.5 rounded-lg text-sm cursor-grab border-l-4"
                                :class="selected && selected.id === {{ $dish->id }} && selected.course === 'dessert' ? 'bg-brand-red text-white border-brand-red' : 'border-transparent hover:bg-brand-red/10 hover:border-brand-red/50'"
                            >
                                {{ $dish->name }}
                            </li>
                        @endforeach
                        @if ($dessertDishes->isEmpty())
                            <li class="text-sm text-neutral-400">Aucun résultat</li>
                        @endif
                    </ul>
                </div>
            </div>
        </aside>

        {{-- Grille de la semaine --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-3">
                <button wire:click="previousMonth" class="cursor-pointer px-3 py-2 rounded-lg bg-white shadow-sm text-neutral-600">←</button>
                <h1 class="text-lg font-semibold capitalize">{{ $monthStart->locale('fr')->translatedFormat('F Y') }}</h1>
                <button wire:click="nextMonth" class="cursor-pointer px-3 py-2 rounded-lg bg-white shadow-sm text-neutral-600">→</button>
            </div>

            <div class="flex gap-1.5 overflow-x-auto pb-1 mb-4 -mx-1 px-1">
                @foreach ($weekTabs as $tab)
                    @php $isActive = $tab['start']->toDateString() === $weekStart->toDateString(); @endphp
                    <button
                        wire:click="selectWeek('{{ $tab['start']->toDateString() }}')"
                        @if ($isActive) x-init="$el.scrollIntoView({inline: 'center', block: 'nearest'})" @endif
                        class="cursor-pointer shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs whitespace-nowrap {{ $isActive ? 'bg-brand-orange text-white' : 'bg-white text-neutral-600 shadow-sm' }}"
                    >
                        <span class="inline-block size-1.5 rounded-full {{ $tab['filled'] > 0 ? ($isActive ? 'bg-white' : 'bg-brand-orange') : ($isActive ? 'bg-white/40' : 'bg-neutral-300') }}"></span>
                        {{ $tab['start']->translatedFormat('d') }}–{{ $tab['start']->copy()->addDays(6)->locale('fr')->translatedFormat('d M') }}
                        @if ($tab['filled'] > 0)
                            <span class="{{ $isActive ? 'text-white/80' : 'text-neutral-400' }}">({{ $tab['filled'] }})</span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="space-y-2">
                @foreach ($days as $day)
                    <div class="bg-white rounded-xl shadow-sm p-2.5">
                        <h2 class="font-medium mb-1.5 text-sm capitalize">{{ $day->locale('fr')->translatedFormat('l d/m') }}</h2>

                        <div class="space-y-1.5">
                            @foreach ([
                                'midi' => ['label' => 'Midi', 'panel' => 'bg-brand-terracotta/15', 'text' => 'text-brand-brick'],
                                'soir' => ['label' => 'Soir', 'panel' => 'bg-brand-brick/15', 'text' => 'text-brand-red'],
                            ] as $slotKey => $slotMeta)
                                <div class="rounded-lg {{ $slotMeta['panel'] }} p-1.5">
                                    <span class="text-xs font-medium {{ $slotMeta['text'] }}">{{ $slotMeta['label'] }}</span>

                                    <div class="grid grid-cols-5 gap-1 mt-1">
                                        @for ($position = 1; $position <= 3; $position++)
                                            @php
                                                $meal = $planned->get($day->toDateString().'-'.$slotKey.'-plat-'.$position);
                                            @endphp
                                            <div
                                                x-on:dragover.prevent
                                                x-on:drop.prevent="$wire.placeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'plat', {{ $position }}, $event.dataTransfer.getData('text/plain'))"
                                                x-on:click="if (selected && selected.course === 'plat') { $wire.placeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'plat', {{ $position }}, selected.id); selected = null }"
                                                class="group cursor-pointer h-11 bg-white rounded-md border border-dashed border-brand-mustard/50 flex items-center justify-center text-center px-0.5 text-[10px] leading-tight overflow-hidden relative"
                                            >
                                                @if ($meal?->dish)
                                                    <span>{{ $meal->dish->name }}</span>
                                                    <button
                                                        type="button"
                                                        wire:click.stop="removeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'plat', {{ $position }})"
                                                        class="cursor-pointer absolute -top-1 -right-1 size-4 flex items-center justify-center rounded-full bg-white shadow text-neutral-500 transition-transform group-hover:scale-150 group-hover:text-brand-red"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-2.5">
                                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span class="text-brand-mustard/50">+</span>
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
                                                class="group cursor-pointer h-11 bg-white rounded-md border border-dashed border-brand-red/40 flex items-center justify-center text-center px-0.5 text-[10px] leading-tight overflow-hidden relative"
                                            >
                                                @if ($meal?->dish)
                                                    <span>{{ $meal->dish->name }}</span>
                                                    <button
                                                        type="button"
                                                        wire:click.stop="removeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'dessert', {{ $position }})"
                                                        class="cursor-pointer absolute -top-1 -right-1 size-4 flex items-center justify-center rounded-full bg-white shadow text-neutral-500 transition-transform group-hover:scale-150 group-hover:text-brand-red"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-2.5">
                                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span class="text-brand-red/40">+</span>
                                                @endif
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('courses', ['month' => $monthStart->toDateString()]) }}" class="text-brand-orange font-medium">
                    Voir les courses de ce mois →
                </a>
            </div>
        </div>
    </div>
</div>
