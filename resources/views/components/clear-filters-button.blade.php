@props([
    'method',
    'active' => false,
])

<button type="button"
    data-global-loading
    wire:click="{{ $method }}"
    wire:loading.attr="disabled"
    wire:target="{{ $method }}"
    @disabled(! $active)
    title="{{ $active ? __('Clear all filters') : __('No filters applied') }}"
    aria-label="{{ $active ? __('Clear all filters') : __('No filters applied') }}"
    {{ $attributes->class([
        'inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg border px-4 text-sm font-semibold shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2',
        'cursor-pointer border-red-600 bg-red-500 text-white hover:-translate-y-px hover:bg-red-400 hover:shadow-md active:translate-y-0 active:bg-red-600' => $active,
        'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400 shadow-none' => ! $active,
    ]) }}>
    <svg wire:loading.remove wire:target="{{ $method }}" class="h-4 w-4" viewBox="0 0 20 20"
        fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
    </svg>
    <svg wire:loading wire:target="{{ $method }}" class="h-4 w-4 animate-spin" viewBox="0 0 24 24"
        fill="none" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" />
        <path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z" />
    </svg>
    <span wire:loading.remove wire:target="{{ $method }}">{{ __('Clear filters') }}</span>
    <span wire:loading wire:target="{{ $method }}">{{ __('Clearing...') }}</span>
</button>
