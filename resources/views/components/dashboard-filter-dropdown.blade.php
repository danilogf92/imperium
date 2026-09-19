@props([
    'label',
    'model',
    'options' => [],
    'selected' => [],
    'multiple' => false,
    'default' => null,
    'showSelection' => false,
    'compact' => false,
    'truncateSelection' => false,
    'globalLoading' => true,
    'closeOnSelect' => true,
    'createAction' => null,
    'createLabel' => null,
])

@php
    $optionList = collect($options)->values();
    $selectedValues = $multiple ? (array) $selected : [$selected];
    $selectedCount = collect($selectedValues)
        ->filter(fn($value) => $value !== null && $value !== '' && $value !== $default)
        ->count();
    $selectedOptionLabel =
        $showSelection && !$multiple && $selectedCount
            ? data_get(
                $optionList->first(fn($option) => (string) data_get($option, 'value') === (string) $selected),
                'label',
            )
            : null;
@endphp

<div wire:key="dashboard-filter-{{ $model }}-{{ md5(json_encode($selectedValues)) }}" x-data="{ open: false, search: '' }"
    x-init="
        const close = () => {
            open = false;
            search = '';
        };
        const closeOnExternalScroll = (event) => {
            if ($refs.menu?.contains(event.target)) return;
            close();
        };
        const closeOnOutsideClick = (event) => {
            if (!open || $refs.trigger?.contains(event.target) || $refs.menu?.contains(event.target)) return;
            close();
        };
        window.addEventListener('scroll', closeOnExternalScroll, true);
        document.addEventListener('pointerdown', closeOnOutsideClick, true);
        $cleanup(() => {
            window.removeEventListener('scroll', closeOnExternalScroll, true);
            document.removeEventListener('pointerdown', closeOnOutsideClick, true);
        });
    "
    x-on:keydown.escape.window="open = false; search = ''; $refs.trigger?.focus()"
    @class(['w-full', 'min-w-0 max-w-full' => $truncateSelection, 'sm:w-auto sm:shrink-0' => ! $truncateSelection])>
    <button x-ref="trigger" type="button"
        @click="open = !open; if (open) { $nextTick(() => { const rect = $refs.trigger.getBoundingClientRect(); const width = Math.min(288, window.innerWidth - 16); $refs.menu.style.width = `${width}px`; $refs.menu.style.left = `${Math.max(8, Math.min(rect.left, window.innerWidth - width - 8))}px`; $refs.menu.style.top = `${rect.bottom + 8}px`; }); }"
        :class="open ? 'border-blue-500 ring-2 ring-blue-500/25 text-blue-700' : 'border-slate-300'"
        @if ($truncateSelection) title="{{ $selectedOptionLabel ?: __($label) }}" @endif
        @class([
            'inline-flex h-12 w-full cursor-pointer items-center justify-between rounded-xl border bg-white text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/25 sm:h-11 sm:rounded-lg sm:w-auto',
            'gap-3 px-4 sm:min-w-32 sm:px-3' => !$compact,
            'gap-1.5 px-3 sm:w-28 sm:px-2.5' => $compact,
            'min-w-0 max-w-full sm:!w-full sm:!min-w-0' => $truncateSelection,
        ])>
        <span @class(['truncate' => $compact || $truncateSelection, 'min-w-0 text-left' => $truncateSelection])>{{ $selectedOptionLabel ? __($selectedOptionLabel) : __($label) }}</span>
        <span class="flex shrink-0 items-center gap-2">
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
        <div x-ref="menu" x-show="open" x-cloak data-dashboard-filter-menu
            class="fixed z-[200] max-h-[70vh] max-w-[calc(100vw-1rem)] overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 shadow-2xl sm:max-h-80 sm:shadow-xl">
            <p class="px-2 pb-2 pt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __($label) }}
            </p>

            <div class="relative mb-2">
                <input x-model="search" type="search" placeholder="{{ __('Search...') }}" autocomplete="off"
                    class="h-10 w-full appearance-none rounded-lg border-slate-300 py-2 pl-10 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">
                <span
                    class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-slate-400">
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
                            <input wire:model.live="{{ $model }}"
                                @if ($globalLoading) data-global-loading @else data-no-global-loading @endif
                                @if ($closeOnSelect) @change="open = false; search = ''" @endif
                                type="checkbox" value="{{ $value }}" autocomplete="off"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        @else
                            <input wire:model.live="{{ $model }}"
                                @if ($globalLoading) data-global-loading @else data-no-global-loading @endif
                                type="radio" value="{{ $value }}" autocomplete="off"
                                @change="open = false; search = ''"
                                class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500">
                        @endif
                        <span>{{ $translatedOptionLabel }}</span>
                    </label>
                @endforeach
            </div>

            @if ($createAction)
                <div class="mt-2 border-t border-slate-200 pt-2">
                    <button type="button" wire:click="{{ $createAction }}" @click="open = false; search = ''"
                        class="flex w-full items-center gap-3 rounded-lg px-2 py-2 text-left text-sm font-semibold text-blue-600 transition hover:bg-blue-50">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded border border-blue-300 text-base leading-none">+</span>
                        <span>{{ __($createLabel ?: 'Create new') }}</span>
                    </button>
                </div>
            @endif
        </div>
    </template>
</div>
