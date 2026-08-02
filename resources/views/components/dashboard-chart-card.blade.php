@props([
    'title',
    'filename',
    'subtitle' => null,
    'height' => '38rem',
])

<article data-chart-card
    {{ $attributes->class('dashboard-chart-card flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:border-slate-300 hover:shadow-md') }}
    style="height: {{ $height }};">
    <header class="flex min-h-16 shrink-0 items-center justify-between gap-4 border-b border-slate-100 px-5 py-3.5">
        <div class="min-w-0">
            <h3 class="truncate text-sm font-bold tracking-tight text-slate-800">{{ $title }}</h3>
            @if ($subtitle)
                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
        <x-chart-download-button :filename="$filename" />
    </header>

    <div class="min-h-0 flex-1 p-4">
        {{ $slot }}
    </div>

    @isset($footer)
        <footer class="shrink-0 border-t border-slate-100 px-5 py-3 text-center text-xs text-slate-500">
            {{ $footer }}
        </footer>
    @endisset
</article>

@once
    <style>
        .dashboard-chart-card .apexcharts-title-text {
            display: none;
        }

        .dashboard-chart-card > .min-h-0 > div,
        .dashboard-chart-card > .min-h-0 > div > div {
            height: 100%;
        }
    </style>
@endonce
