<div class="min-h-screen flex items-center justify-center px-6 pb-16" wire:poll.30s>
    <div class="max-w-md w-full text-center space-y-8">
        <div>
            <h1 class="text-3xl font-semibold text-brand-red">{{ $greeting }} {{ auth()->user()->name }} !</h1>
            <p class="text-sm text-neutral-400 mt-1">Famille {{ auth()->user()->famille?->name ?? 'non définie' }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 text-left">
            <ul class="space-y-2">
                @foreach ($members as $member)
                    <li class="flex items-center gap-3 text-sm">
                        <span class="relative inline-flex shrink-0">
                            <span class="text-2xl leading-none {{ $member->isOnline() ? '' : 'grayscale opacity-50' }}">{{ $member->avatar }}</span>
                            <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full ring-2 ring-white {{ $member->isOnline() ? 'bg-green-500' : 'bg-neutral-300' }}"></span>
                        </span>
                        <span class="{{ $member->id === auth()->id() ? 'font-medium' : '' }}">{{ $member->name }}</span>
                        <span class="text-xs text-neutral-400 ml-auto">
                            @if ($member->isOnline())
                                En ligne
                            @elseif ($member->last_seen_at)
                                Vu {{ $member->last_seen_at->diffForHumans() }}
                            @else
                                Jamais connecté
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="grid gap-4">
            <a href="{{ route('planning') }}" class="block bg-white rounded-xl shadow-sm p-6 text-left border-l-4 border-brand-orange">
                <span class="block text-lg font-medium">Planning</span>
                <span class="block text-sm text-neutral-500">Organiser les repas de la semaine</span>
            </a>
            <a href="{{ route('courses') }}" class="block bg-white rounded-xl shadow-sm p-6 text-left border-l-4 border-brand-mustard">
                <span class="block text-lg font-medium">Courses</span>
                <span class="block text-sm text-neutral-500">La liste générée à partir du planning</span>
            </a>
        </div>
    </div>
</div>
