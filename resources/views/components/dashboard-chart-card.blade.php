@props([
    'title',
    'filename',
    'subtitle' => null,
    'height' => '38rem',
])

<article data-chart-card
    {{ $attributes->class('dashboard-chart-card flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:border-sky-200 hover:shadow-md') }}
    style="height: {{ $height }};">
    <header class="soft-title-surface flex min-h-16 shrink-0 items-center justify-between gap-4 border-b px-5 py-3.5">
        <div class="min-w-0">
            <h3 class="truncate text-sm font-bold tracking-tight text-slate-800">{{ __($title) }}</h3>
            @if ($subtitle)
                <p class="mt-0.5 truncate text-xs text-slate-500">{{ __($subtitle) }}</p>
            @endif
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <x-chart-download-button :filename="$filename" />
            <x-chart-excel-button :filename="$filename" :title="$title" />
        </div>
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
