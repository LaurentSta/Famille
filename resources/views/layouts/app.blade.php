<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#F28705">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="/icons/icon-192.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="antialiased bg-neutral-50 text-neutral-900">
        {{ $slot }}

        @auth
            <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-neutral-200 grid grid-cols-6 pb-[env(safe-area-inset-bottom)] shadow-lg z-50">
                @foreach ([
                    ['route' => 'accueil', 'label' => 'Accueil'],
                    ['route' => 'planning', 'label' => 'Planning'],
                    ['route' => 'courses', 'label' => 'Courses'],
                    ['route' => 'assistant-cuisine', 'label' => 'IA'],
                    ['route' => 'gestion-cuisine', 'label' => 'Gestion'],
                ] as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="flex flex-col items-center justify-center gap-1 py-3 px-0.5 text-center text-[12px] sm:text-sm whitespace-nowrap {{ request()->routeIs($item['route']) ? 'text-brand-orange font-semibold bg-brand-orange/10 rounded-t-md' : 'text-neutral-500' }}"
                    >
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
                <form method="POST" action="{{ route('deconnexion') }}">
                    @csrf
                    <button type="submit" class="flex flex-col items-center justify-center gap-1 cursor-pointer w-full py-3 px-0.5 text-center text-[12px] sm:text-sm text-neutral-700 whitespace-nowrap hover:text-neutral-900">
                        <span class="truncate">Déconnexion</span>
                    </button>
                </form>
            </nav>
        @endauth

        @livewireScripts

        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js');
                });
            }
        </script>
    </body>
</html>
