<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <meta name="theme-color" content="#0ea5e9">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=3">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}?v=3">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <x-global-loading-overlay />

        <div class="app-page-frame min-h-screen">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="border-b border-sky-100 bg-white shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="min-w-0 max-w-full overflow-x-hidden">
                {{ $slot }}
            </main>
        </div>

        <script
            src="{{ asset('vendor/livewire-charts/app.js') }}?v={{ filemtime(public_path('vendor/livewire-charts/app.js')) }}"></script>
    </body>
</html>
