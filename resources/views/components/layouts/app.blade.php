<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gray-50">
        @auth
            <header class="border-b border-gray-200 bg-white">
                <div class="mx-auto max-w-7xl px-4">
                    @include('components.navigation-tabs')
                </div>
            </header>
        @endauth
        {{ $slot }}
    </div>
</body>
</html>