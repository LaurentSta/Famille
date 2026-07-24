<div class="max-w-2xl mx-auto px-4 py-6 pb-24">
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
                    @php
                        $key = $day->toDateString().'-'.$slotKey;
                        $current = $planned->get($key)?->dish_id;
                    @endphp
                    <div class="flex items-center justify-between py-2 border-t first:border-t-0 border-neutral-100">
                        <span class="text-sm text-neutral-500 w-14">{{ $slotLabel }}</span>
                        <select
                            wire:change="updateSlot('{{ $day->toDateString() }}', '{{ $slotKey }}', $event.target.value)"
                            class="flex-1 ml-3 rounded-lg border-neutral-200 text-sm"
                        >
                            <option value="">— Aucun —</option>
                            @foreach ($dishesByType as $type => $dishes)
                                <optgroup label="{{ $type }}">
                                    @foreach ($dishes as $dish)
                                        <option value="{{ $dish->id }}" @selected($current === $dish->id)>{{ $dish->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
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
