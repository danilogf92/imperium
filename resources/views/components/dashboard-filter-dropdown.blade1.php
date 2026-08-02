@props([
'label',
'model',
'options' => [],
'selected' => null,
'multiple' => false,
'default' => 'all',
])

@php
$selectedCount = $multiple ? count((array) $selected) : 0;
$singleActive = !$multiple && (string) $selected !== (string) $default;
@endphp

<div x-data="{ open: false, dropdownId: null }" x-init="dropdownId = $id('dashboard-filter')"
    x-on:dashboard-filters-synced.window="open = false"
    x-on:dashboard-filter-opening.window="if ($event.detail !== dropdownId) open = false"
    class="shrink-0">
    <button x-ref="trigger" type="button"
        @click.prevent.stop="
                            const menuIsVisible = $refs.menu
                                && window.getComputedStyle($refs.menu).display !== 'none';
                            open = false;
                            if (!menuIsVisible) {
                                window.dispatchEvent(
                                    new CustomEvent('dashboard-filter-opening', { detail: dropdownId })
                                );
                                $nextTick(() => {
                                    open = true;
                                    $nextTick(() => {
                                        const rect = $refs.trigger.getBoundingClientRect();
                                        const menu = $refs.menu;
                                        const margin = 8;
                                        const menuWidth = 256;
                                        const availableBelow = window.innerHeight - rect.bottom - margin;
                                        const availableAbove = rect.top - margin;
                                        const openAbove = availableBelow < 220 && availableAbove > availableBelow;
                                        const availableHeight = Math.max(
                                            140,
                                            Math.min(320, openAbove ? availableAbove : availableBelow)
                                        );
                                        const left = Math.min(
                                            rect.left,
                                            window.innerWidth - menuWidth - margin
                                        );

                                        menu.style.left = `${Math.max(margin, left)}px`;
                                        menu.style.maxHeight = `${availableHeight}px`;
                                        menu.style.top = openAbove
                                            ? `${Math.max(margin, rect.top - Math.min(menu.scrollHeight, availableHeight) - margin)}px`
                                            : `${rect.bottom + margin}px`;
                                    });
                                });
                            }
                        "
        :class="open ? 'border-blue-500 ring-2 ring-blue-500/25 text-blue-700' : 'border-slate-300'"
        class="inline-flex h-11 min-w-32 cursor-pointer items-center justify-between gap-3 rounded-lg border bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition duration-150 hover:-translate-y-px hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 hover:shadow-md focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
        <span>{{ $label }}</span>
        <span class="flex items-center gap-2">
            @if ($selectedCount > 0)
            <span
                class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 text-xs font-semibold text-white">
                {{ $selectedCount }}
            </span>
            @elseif ($singleActive)
            <span class="h-2 w-2 rounded-full bg-blue-600"></span>
            @endif
            <svg class="h-4 w-4 transition" :class="{ 'rotate-180': open }"
                xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </span>
    </button>

    <template x-teleport="body">
        <div x-ref="menu" x-show="open" x-cloak
            @click.outside="if (!$refs.trigger.contains($event.target)) open = false"
            @keydown.escape.window="open = false"
            @scroll.window="open = false"
            @wheel.stop
            class="fixed z-[200] w-64 overflow-y-auto overscroll-contain rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
            <p class="px-2 pb-2 pt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                {{ $label }}
            </p>
            <div class="space-y-1">
                @foreach ($options as $option)
                @php
                $optionSelected = $multiple
                ? in_array((string) $option['value'], array_map('strval', (array) $selected), true)
                : (string) $selected === (string) $option['value'];
                @endphp
                <label @class([ 'flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 text-sm transition duration-150 hover:bg-blue-100' , 'bg-blue-50 font-medium text-blue-700'=> $optionSelected,
                    'text-slate-700' => !$optionSelected,
                    ])
                    onmouseenter="this.style.backgroundColor='#dbeafe'"
                    onmouseleave="this.style.backgroundColor='{{ $optionSelected ? '#eff6ff' : 'transparent' }}'">
                    <input wire:model.live="{{ $model }}"
                        type="{{ $multiple ? 'checkbox' : 'radio' }}"
                        value="{{ $option['value'] }}"
                        @if (!$multiple) @change="open = false" @endif
                        @class([ 'h-4 w-4 border-slate-300 text-blue-600 transition focus:ring-2 focus:ring-blue-500 focus:ring-offset-1' , 'rounded'=> $multiple,
                    'rounded-full' => !$multiple,
                    ])>
                    <span>{{ $option['label'] }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </template>
</div>

@once
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({
            succeed,
            fail
        }) => {
            const syncDropdowns = () => {
                window.setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('dashboard-filters-synced'));
                }, 0);
            };

            succeed(syncDropdowns);
            fail(syncDropdowns);
        });
    });
</script>
@endonce