<div class="pb-24" x-data="{ selected: null }">
    <div class="flex items-stretch gap-3 lg:gap-6 w-full px-4 lg:px-8 py-6 min-h-screen">
        {{-- Barre latérale --}}
        <aside class="w-28 sm:w-56 lg:w-72 shrink-0 flex flex-col">
            <template x-if="!selected">
                <div class="text-sm text-white bg-brand-orange rounded-lg px-4 py-3 mb-3 space-y-2">
                    <p class="flex items-center gap-2">
                        <span class="shrink-0 size-5 flex items-center justify-center rounded-full bg-white/25 text-xs font-bold">1</span>
                        <span>Sélectionne un plat ci-dessous.</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <span class="shrink-0 size-5 flex items-center justify-center rounded-full bg-white/25 text-xs font-bold">2</span>
                        <span>Glisse-le (ou touche une case) pour le placer dans le planning.</span>
                    </p>
                </div>
            </template>

            <template x-if="selected">
                <div class="text-sm text-white bg-brand-orange rounded-lg px-4 py-3 mb-3">
                    <p class="font-semibold" x-text="selected.name"></p>
                    <template x-if="selected.ingredients && selected.ingredients.length">
                        <ul class="list-disc list-inside mt-1 space-y-0.5">
                            <template x-for="ingredient in selected.ingredients" :key="ingredient">
                                <li x-text="ingredient"></li>
                            </template>
                        </ul>
                    </template>
                    <button type="button" class="cursor-pointer underline mt-2" x-on:click="selected = null">annuler</button>
                </div>
            </template>

            <div class="flex-1 flex flex-col min-h-0 bg-gradient-to-b from-brand-orange to-brand-red rounded-xl shadow-lg p-1 sticky top-4 z-10 max-h-[80vh]">
                <div class="flex-1 flex flex-col min-h-0 bg-white rounded-lg p-2.5 sm:p-4">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher un plat…"
                        class="w-full rounded-lg border-2 border-brand-orange/30 text-sm mb-3 focus:border-brand-orange focus:ring-brand-orange"
                    >

                    <div class="flex gap-1.5 mb-3">
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

                    <ul class="flex-1 min-h-0 space-y-1 overflow-y-auto">
                        @foreach ($dishes as $dish)
                            @php
                                $course = $dish->type === 'Dessert' ? 'dessert' : 'plat';
                                $activeClass = $course === 'dessert' ? 'bg-brand-red text-white border-brand-red' : 'bg-brand-mustard text-white border-brand-mustard';
                            @endphp
                            <li
                                draggable="true"
                                x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $dish->id }}')"
                                x-on:click="selected = { id: {{ $dish->id }}, name: @js($dish->emoji.' '.$dish->name), course: '{{ $course }}', ingredients: @js($dish->ingredients->map(fn ($i) => $i->emoji.' '.$i->name)) }"
                                class="px-2 py-1.5 rounded-lg text-sm cursor-grab border-l-4"
                                :class="selected && selected.id === {{ $dish->id }} ? '{{ $activeClass }}' : 'border-transparent hover:bg-neutral-50'"
                            >
                                {{ $dish->emoji }} {{ $dish->name }}
                            </li>
                        @endforeach
                        @if ($dishes->isEmpty())
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

            <div class="bg-white rounded-xl shadow-sm p-3 mb-3">
                <div class="flex gap-2 justify-between overflow-x-auto">
                    @foreach ($weekTabs as $tab)
                        @php $isActive = $tab['start']->toDateString() === $weekStart->toDateString(); @endphp
                        <button
                            wire:click="selectWeek('{{ $tab['start']->toDateString() }}')"
                            @if ($isActive) x-init="$el.scrollIntoView({inline: 'center', block: 'nearest'})" @endif
                            class="cursor-pointer shrink-0 flex items-center gap-2 px-4 py-2.5 rounded-full text-sm whitespace-nowrap {{ $isActive ? 'bg-brand-orange text-white' : 'bg-neutral-100 text-neutral-600' }}"
                        >
                            <span class="inline-block size-1.5 rounded-full {{ $tab['filled'] > 0 ? ($isActive ? 'bg-white' : 'bg-brand-orange') : ($isActive ? 'bg-white/40' : 'bg-neutral-300') }}"></span>
                            {{ $tab['start']->translatedFormat('d') }}–{{ $tab['start']->copy()->addDays(6)->locale('fr')->translatedFormat('d M') }}
                            @if ($tab['filled'] > 0)
                                <span class="{{ $isActive ? 'text-white/80' : 'text-neutral-400' }}">({{ $tab['filled'] }})</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <p class="text-sm text-neutral-500 mb-3">
                Repas du {{ $weekStart->locale('fr')->translatedFormat('d') }} au {{ $weekStart->copy()->addDays(6)->locale('fr')->translatedFormat('d F Y') }}
            </p>

            <div class="space-y-2">
                @foreach ($days as $day)
                    @php
                        $dayCount = $planned->keys()->filter(fn ($k) => str_starts_with($k, $day->toDateString().'-'))->count();
                        $hasContent = $dayCount > 0;
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm p-3" x-data="{ open: {{ $hasContent ? 'true' : 'false' }} }">
                        <button type="button" x-on:click="open = !open" class="cursor-pointer w-full flex items-center justify-between">
                            <h2 class="font-medium text-lg capitalize">{{ $day->locale('fr')->translatedFormat('l d/m') }}</h2>
                            <span class="flex items-center gap-2 text-sm text-neutral-400">
                                @if ($hasContent)
                                    <span>{{ $dayCount }} recette{{ $dayCount > 1 ? 's' : '' }}</span>
                                @else
                                    <span>Rien de prévu</span>
                                @endif
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4 transition-transform" :class="open ? 'rotate-180' : ''">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>

                        <div x-show="open" class="mt-2 space-y-2">
                            @foreach ([
                                'midi' => ['label' => 'Midi', 'panel' => 'bg-brand-terracotta/15', 'text' => 'text-brand-brick'],
                                'soir' => ['label' => 'Soir', 'panel' => 'bg-brand-brick/15', 'text' => 'text-brand-red'],
                            ] as $slotKey => $slotMeta)
                                <div class="rounded-lg {{ $slotMeta['panel'] }} p-2">
                                    <span class="text-sm font-medium {{ $slotMeta['text'] }}">{{ $slotMeta['label'] }}</span>

                                    <div class="grid grid-cols-5 gap-1 mt-1">
                                        @for ($position = 1; $position <= 3; $position++)
                                            @php
                                                $meal = $planned->get($day->toDateString().'-'.$slotKey.'-plat-'.$position);
                                            @endphp
                                            <div
                                                x-on:dragover.prevent
                                                x-on:drop.prevent="$wire.placeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'plat', {{ $position }}, $event.dataTransfer.getData('text/plain'))"
                                                @if ($meal?->dish)
                                                    x-on:click="if (selected && selected.course === 'plat') { $wire.placeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'plat', {{ $position }}, selected.id); selected = null } else { selected = { id: {{ $meal->dish->id }}, name: @js($meal->dish->emoji.' '.$meal->dish->name), course: 'plat', ingredients: @js($meal->dish->ingredients->map(fn ($i) => $i->emoji.' '.$i->name)) } }"
                                                @else
                                                    x-on:click="if (selected && selected.course === 'plat') { $wire.placeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'plat', {{ $position }}, selected.id); selected = null }"
                                                @endif
                                                class="group cursor-pointer h-16 bg-white rounded-md border border-dashed border-brand-mustard/50 relative"
                                            >
                                                <div class="absolute inset-0 flex items-center justify-center text-center px-1 text-sm leading-snug overflow-hidden">
                                                    @if ($meal?->dish)
                                                        <span>{{ $meal->dish->emoji }} {{ $meal->dish->name }}</span>
                                                    @else
                                                        <span class="text-brand-mustard/50 text-2xl leading-none font-light">+</span>
                                                    @endif
                                                </div>

                                                @if ($meal?->dish)
                                                    <button
                                                        type="button"
                                                        wire:click.stop="removeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'plat', {{ $position }})"
                                                        class="cursor-pointer absolute -top-2 -right-2 z-10 size-6 flex items-center justify-center rounded-full bg-white shadow ring-1 ring-black/5 text-neutral-500 text-lg leading-none opacity-0 group-hover:opacity-100 [@media(hover:none)]:opacity-100 hover:text-brand-red transition-opacity"
                                                    >×</button>
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
                                                @if ($meal?->dish)
                                                    x-on:click="if (selected && selected.course === 'dessert') { $wire.placeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'dessert', {{ $position }}, selected.id); selected = null } else { selected = { id: {{ $meal->dish->id }}, name: @js($meal->dish->emoji.' '.$meal->dish->name), course: 'dessert', ingredients: @js($meal->dish->ingredients->map(fn ($i) => $i->emoji.' '.$i->name)) } }"
                                                @else
                                                    x-on:click="if (selected && selected.course === 'dessert') { $wire.placeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'dessert', {{ $position }}, selected.id); selected = null }"
                                                @endif
                                                class="group cursor-pointer h-16 bg-white rounded-md border border-dashed border-brand-red/40 relative"
                                            >
                                                <div class="absolute inset-0 flex items-center justify-center text-center px-1 text-sm leading-snug overflow-hidden">
                                                    @if ($meal?->dish)
                                                        <span>{{ $meal->dish->emoji }} {{ $meal->dish->name }}</span>
                                                    @else
                                                        <span class="text-brand-red/40 text-2xl leading-none font-light">+</span>
                                                    @endif
                                                </div>

                                                @if ($meal?->dish)
                                                    <button
                                                        type="button"
                                                        wire:click.stop="removeDish('{{ $day->toDateString() }}', '{{ $slotKey }}', 'dessert', {{ $position }})"
                                                        class="cursor-pointer absolute -top-2 -right-2 z-10 size-6 flex items-center justify-center rounded-full bg-white shadow ring-1 ring-black/5 text-neutral-500 text-lg leading-none opacity-0 group-hover:opacity-100 [@media(hover:none)]:opacity-100 hover:text-brand-red transition-opacity"
                                                    >×</button>
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
