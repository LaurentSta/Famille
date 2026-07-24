<div class="max-w-2xl mx-auto px-4 py-6 pb-24">
    <h1 class="text-lg font-semibold mb-1">Liste de courses</h1>
    <p class="text-sm text-neutral-500 mb-6">
        Semaine du {{ $weekStart->locale('fr')->translatedFormat('d F Y') }}
    </p>

    @if ($ingredients->isEmpty())
        <p class="text-neutral-500">
            Aucun plat planifié cette semaine.
            <a href="{{ route('planning', ['week' => $weekStart->toDateString()]) }}" class="text-teal-700 font-medium">
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
                                    class="size-5 rounded border-neutral-300 text-teal-700"
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
