<div class="max-w-2xl mx-auto px-4 py-6 pb-24">
    <h1 class="text-lg font-semibold mb-4">Liste de courses</h1>

    <div class="flex items-center justify-between mb-3">
        <button wire:click="previousYear" class="px-3 py-2 rounded-lg bg-white shadow-sm text-neutral-600">←</button>
        <span class="text-sm font-medium text-neutral-500">{{ $yearStart->format('Y') }}</span>
        <button wire:click="nextYear" class="px-3 py-2 rounded-lg bg-white shadow-sm text-neutral-600">→</button>
    </div>

    <div class="flex gap-1.5 overflow-x-auto pb-1 mb-6 -mx-1 px-1">
        @foreach ($monthTabs as $tab)
            @php $isActive = $tab['start']->toDateString() === $monthStart->toDateString(); @endphp
            <button
                wire:click="selectMonth('{{ $tab['start']->toDateString() }}')"
                class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs whitespace-nowrap capitalize {{ $isActive ? 'bg-brand-orange text-white' : 'bg-white text-neutral-600 shadow-sm' }}"
            >
                <span class="inline-block size-1.5 rounded-full {{ $tab['filled'] > 0 ? ($isActive ? 'bg-white' : 'bg-brand-orange') : ($isActive ? 'bg-white/40' : 'bg-neutral-300') }}"></span>
                {{ $tab['start']->locale('fr')->translatedFormat('MMM') }}
                @if ($tab['filled'] > 0)
                    <span class="{{ $isActive ? 'text-white/80' : 'text-neutral-400' }}">({{ $tab['filled'] }})</span>
                @endif
            </button>
        @endforeach
    </div>

    <p class="text-sm text-neutral-500 mb-6 capitalize">
        {{ $monthStart->locale('fr')->translatedFormat('F Y') }}
    </p>

    @if ($ingredients->isEmpty())
        <p class="text-neutral-500">
            Aucun plat planifié ce mois-ci.
            <a href="{{ route('planning', ['week' => $monthStart->toDateString()]) }}" class="text-brand-orange font-medium">
                Aller au planning →
            </a>
        </p>
    @else
        <div class="space-y-4">
            @foreach ($ingredients as $category => $items)
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <h2 class="font-medium mb-2">{{ $category ?? 'Autre' }}</h2>
                    <ul class="divide-y divide-neutral-100">
                        @foreach ($items as $ingredient)
                            <li class="flex items-center gap-3 py-2">
                                <input
                                    type="checkbox"
                                    wire:click="toggle({{ $ingredient->id }})"
                                    @checked($ingredient->in_stock)
                                    class="size-5 rounded border-neutral-300 text-brand-orange"
                                >
                                <span class="{{ $ingredient->in_stock ? 'line-through text-neutral-400' : '' }}">
                                    {{ $ingredient->name }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif
</div>
