@props(['filters' => [], 'clear' => null])
@php
    $chips = collect($filters)->flatMap(function ($filter) {
        $values = is_array($filter['value']) ? $filter['value'] : [$filter['value']];
        return collect($values)->filter(fn ($value) => $value !== null && $value !== '' && $value !== false && $value !== ($filter['default'] ?? null))
            ->map(fn ($value) => [...$filter, 'item' => $value, 'caption' => ($filter['options'][$value] ?? $value)]);
    });
@endphp
@if ($chips->isNotEmpty())
    <div class="app-filter-chips flex flex-wrap items-center gap-1 border-t border-slate-200 px-3 py-2" aria-label="{{ __('Active filters') }}">
        @foreach ($chips as $chip)
            @php($next = is_array($chip['value']) ? array_values(array_filter($chip['value'], fn ($value) => (string) $value !== (string) $chip['item'])) : ($chip['default'] ?? ''))
            <button type="button"
                @if (isset($chip['action'])) wire:click="{{ $chip['action'] }}"
                @else wire:click="$set('{{ $chip['model'] }}', {{ Illuminate\Support\Js::from($next) }})" @endif
                aria-label="{{ __('Remove filter') }}: {{ __($chip['label']) }} {{ __((string) $chip['caption']) }}"
                class="inline-flex max-w-full items-center gap-1 rounded-full border border-sky-200 bg-sky-50 px-2 py-1 text-xs text-sky-800">
                <span class="break-words">{{ __($chip['label']) }}: {{ __((string) $chip['caption']) }}</span>
                <span class="text-base font-bold text-red-600" aria-hidden="true">×</span>
            </button>
        @endforeach
        @if ($clear)
            <button type="button" wire:click="{{ $clear }}" class="rounded-lg px-2 py-1 text-xs font-semibold text-red-600">{{ __('Clear filters') }}</button>
        @endif
    </div>
@endif
