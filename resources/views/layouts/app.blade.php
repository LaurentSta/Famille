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
