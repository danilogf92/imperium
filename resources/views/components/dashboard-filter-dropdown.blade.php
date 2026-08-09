@props(['label', 'model', 'options' => [], 'selected' => [], 'multiple' => false, 'default' => null, 'showSelection' => false])

@php
    $optionList = collect($options)->values();
    $selectedValues = $multiple ? (array) $selected : [$selected];
    $selectedCount = collect($selectedValues)
        ->filter(fn($value) => $value !== null && $value !== '' && $value !== $default)
        ->count();
    $selectedOptionLabel = $showSelection && !$multiple && $selectedCount
        ? data_get($optionList->first(fn($option) => (string) data_get($option, 'value') === (string) $selected), 'label')
        : null;
@endphp

<div x-data="{ open: false, search: '' }" x-on:scroll.window="open = false" class="shrink-0">
    <button x-ref="trigger" type="button"
        @click="open = !open; if (open) { $nextTick(() => { const rect = $refs.trigger.getBoundingClientRect(); $refs.menu.style.left = `${rect.left}px`; $refs.menu.style.top = `${rect.bottom + 8}px`; }); }"
        :class="open ? 'border-blue-500 ring-2 ring-blue-500/25 text-blue-700' : 'border-slate-300'"
        class="inline-flex h-11 min-w-32 cursor-pointer items-center justify-between gap-3 rounded-lg border bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none">
        <span>{{ $selectedOptionLabel ? __($selectedOptionLabel) : __($label) }}</span>
        <span class="flex items-center gap-2">
            @if ($selectedCount > 0)
                <span
                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 text-xs font-semibold text-white">
                    {{ $selectedCount }}
                </span>
            @endif
            <svg class="h-4 w-4 transition" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </span>
    </button>

    <template x-teleport="body">
        <div x-ref="menu" x-show="open" x-cloak @click.outside="open = false"
            class="fixed z-[200] max-h-80 w-72 overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
            <p class="px-2 pb-2 pt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __($label) }}
            </p>

            <div class="relative mb-2">
                <input x-model="search" type="search" placeholder="{{ __('Search...') }}" autocomplete="off"
                    class="h-10 w-full appearance-none rounded-lg border-slate-300 py-2 pl-10 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" />
                    </svg>
                </span>
            </div>

            <div class="space-y-1">
                @foreach ($optionList as $option)
                    @php
                        $value = (string) data_get($option, 'value');
                        $optionLabel = (string) data_get($option, 'label', $value);
                        $translatedOptionLabel = __($optionLabel);
                    @endphp

                    <label x-show="search === '' || @js(mb_strtolower($translatedOptionLabel)).includes(search.toLowerCase())"
                        class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 text-sm text-slate-700 transition hover:bg-blue-50">
                        @if ($multiple)
                            <input wire:model.live="{{ $model }}" data-global-loading type="checkbox" value="{{ $value }}"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        @else
                            <input wire:model.live="{{ $model }}" data-global-loading type="radio" value="{{ $value }}"
                                @change="open = false"
                                class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500">
                        @endif
                        <span>{{ $translatedOptionLabel }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </template>
</div>
