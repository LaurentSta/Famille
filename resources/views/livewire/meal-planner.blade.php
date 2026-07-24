<div class="pb-24" x-data="{ selected: null }">
    <div class="flex items-stretch gap-3 lg:gap-6 max-w-5xl mx-auto px-4 py-6">
        {{-- Barre latérale --}}
        <aside class="w-28 sm:w-56 lg:w-72 shrink-0">
            <div class="bg-white rounded-xl shadow-sm p-2.5 sm:p-4 sticky top-4 z-10">
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
            <div class="flex items-center justify-between mb-3">
                <button wire:click="previousMonth" class="px-3 py-2 rounded-lg bg-white shadow-sm text-neutral-600">←</button>
                <h1 class="text-lg font-semibold capitalize">{{ $monthStart->locale('fr')->translatedFormat('F Y') }}</h1>
                <button wire:click="nextMonth" class="px-3 py-2 rounded-lg bg-white shadow-sm text-neutral-600">→</button>
            </div>

            <div class="flex gap-1.5 overflow-x-auto pb-1 mb-4 -mx-1 px-1">
                @foreach ($weekTabs as $tab)
                    <button
                        wire:click="selectWeek('{{ $tab->toDateString() }}')"
                        class="shrink-0 px-3 py-1.5 rounded-full text-xs whitespace-nowrap {{ $tab->toDateString() === $weekStart->toDateString() ? 'bg-teal-700 text-white' : 'bg-white text-neutral-600 shadow-sm' }}"
                    >
                        {{ $tab->translatedFormat('d') }}–{{ $tab->copy()->addDays(6)->locale('fr')->translatedFormat('d M') }}
                    </button>
                @endforeach
            </div>

            <div class="space-y-2">
                @foreach ($days as $day)
                    <div class="bg-white rounded-xl shadow-sm p-2.5">
                        <h2 class="font-medium mb-1.5 text-sm capitalize">{{ $day->locale('fr')->translatedFormat('l d/m') }}</h2>

                        <div class="space-y-1.5">
                            @foreach ([
                                'midi' => ['label' => 'Midi', 'panel' => 'bg-amber-50', 'text' => 'text-amber-700'],
                                'soir' => ['label' => 'Soir', 'panel' => 'bg-indigo-50', 'text' => 'text-indigo-700'],
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
                                                class="h-11 bg-white rounded-md border border-dashed border-neutral-300 flex items-center justify-center text-center px-0.5 text-[10px] leading-tight overflow-hidden relative"
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
                                                class="h-11 bg-white rounded-md border border-dashed border-rose-300 flex items-center justify-center text-center px-0.5 text-[10px] leading-tight overflow-hidden relative"
                                            >
                                                @if ($meal?->dish)
                                                    <span>{{ $meal->dish->name }}</span>
                                                    <button
                                                        type="button"
                                                        wire:click.stop="removeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'dessert', {{ $position }})"
                                                        class="absolute top-0 right-0.5 text-neutral-400"
                                                    >×</button>
                                                @else
                                                    <span class="text-rose-300">+</span>
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
                <a href="{{ route('courses', ['week' => $weekStart->toDateString()]) }}" class="text-teal-700 font-medium">
                    Voir les courses de cette semaine →
                </a>
            </div>
        </div>
    </div>
</div>
