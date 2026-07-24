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
            <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-neutral-200 grid grid-cols-6 pb-[env(safe-area-inset-bottom)]">
                @foreach ([
                    ['route' => 'home', 'label' => 'Accueil'],
                    ['route' => 'planning', 'label' => 'Planning'],
                    ['route' => 'courses', 'label' => 'Courses'],
                    ['route' => 'ai', 'label' => 'IA'],
                    ['route' => 'exchanges', 'label' => 'Échanges'],
                ] as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="py-3 px-0.5 text-center text-[11px] sm:text-sm whitespace-nowrap {{ request()->routeIs($item['route']) ? 'text-brand-orange font-medium' : 'text-neutral-500' }}"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="cursor-pointer w-full py-3 px-0.5 text-center text-[11px] sm:text-sm text-neutral-500 whitespace-nowrap">
                        Déconnexion
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
