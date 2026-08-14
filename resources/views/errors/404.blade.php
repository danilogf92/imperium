<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Page not found') }} · {{ config('app.name', 'DaImperium') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    @php
        $fallbackUrl = auth()->check() ? route('dashboard') : route('login');
        $fallbackLabel = auth()->check() ? __('Go to dashboard') : __('Go to sign in');
    @endphp

    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-50 px-5 py-12">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-600 via-sky-400 to-cyan-400"></div>
        <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-blue-200/40 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-cyan-200/40 blur-3xl"></div>

        <section class="relative w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-7 text-center shadow-xl shadow-slate-200/60 sm:p-10">
            <a href="{{ $fallbackUrl }}" class="mx-auto inline-flex" aria-label="{{ config('app.name', 'DaImperium') }}">
                <x-application-logo class="h-16 w-16 object-contain" />
            </a>

            <div class="mx-auto mt-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.2 9.2a3.1 3.1 0 1 1 5.1 2.4c-1.2.9-2.3 1.4-2.3 3M12 18h.01" />
                    <circle cx="12" cy="12" r="9" />
                </svg>
            </div>

            <p class="mt-6 text-sm font-bold uppercase tracking-[0.22em] text-blue-600">{{ __('Error 404') }}</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ __('Page not found') }}</h1>
            <p class="mx-auto mt-4 max-w-md text-sm leading-6 text-slate-600 sm:text-base">
                {{ __('The page you requested does not exist, may have moved, or the address may be incorrect.') }}
            </p>

            <div class="mt-8 flex flex-col-reverse justify-center gap-3 sm:flex-row">
                <a href="{{ $fallbackUrl }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    {{ $fallbackLabel }}
                </a>
                <button type="button"
                    onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = @js($fallbackUrl); }"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                    </svg>
                    {{ __('Go back') }}
                </button>
            </div>
        </section>
    </main>
</body>

</html>
