<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0f766e">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="/icons/icon-192.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="antialiased bg-neutral-50 text-neutral-900">
        {{ $slot }}

        <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-neutral-200 grid grid-cols-4 pb-[env(safe-area-inset-bottom)]">
            @foreach ([
                ['route' => 'home', 'label' => 'Accueil'],
                ['route' => 'planning', 'label' => 'Planning'],
                ['route' => 'courses', 'label' => 'Courses'],
                ['route' => 'reserves', 'label' => 'Réserves'],
            ] as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="py-3 text-center text-sm {{ request()->routeIs($item['route']) ? 'text-teal-700 font-medium' : 'text-neutral-500' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

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
