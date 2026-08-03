@props([
    'method',
    'label' => 'Export Excel',
    'loadingLabel' => 'Generating...',
])

<button type="button"
    wire:click="{{ $method }}"
    wire:loading.attr="disabled"
    wire:target="{{ $method }}"
    data-no-global-loading
    {{ $attributes->class([
        'group inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-lg border border-emerald-700 bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm transition duration-150',
        'hover:-translate-y-px hover:border-emerald-800 hover:bg-emerald-700 hover:text-white hover:shadow-md',
        'active:translate-y-0 active:bg-emerald-800',
        'focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2',
        'disabled:cursor-wait disabled:opacity-60',
    ]) }}>
    <svg wire:loading.remove wire:target="{{ $method }}"
        class="h-5 w-5 transition-transform duration-150 group-hover:scale-105"
        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
        aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M19.5 14.25v-2.625A3.375 3.375 0 0 0 16.125 8.25h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-5.25Z" />
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M13.5 2.25V7.5a.75.75 0 0 0 .75.75h5.25M8.25 13.5l2.25 3m0-3-2.25 3m4.5-3v3m3-3v3" />
    </svg>

    <svg wire:loading wire:target="{{ $method }}" class="h-5 w-5 animate-spin"
        viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10"
            stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"></path>
    </svg>

    <span wire:loading.remove wire:target="{{ $method }}">{{ __($label) }}</span>
    <span wire:loading wire:target="{{ $method }}">{{ __($loadingLabel) }}</span>
</button>
