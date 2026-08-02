@props(['label', 'model', 'options' => [], 'selected' => [], 'multiple' => false, 'default' => null])

@php
    $optionList = collect($options)->values();
    $selectedValues = $multiple ? (array) $selected : [$selected];
    $selectedCount = collect($selectedValues)
        ->filter(fn($value) => $value !== null && $value !== '' && $value !== $default)
        ->count();
@endphp

<div x-data="{ open: false }" x-on:scroll.window="open = false" class="shrink-0">
    <button x-ref="trigger" type="button"
        @click="open = !open; if (open) { $nextTick(() => { const rect = $refs.trigger.getBoundingClientRect(); $refs.menu.style.left = `${rect.left}px`; $refs.menu.style.top = `${rect.bottom + 8}px`; }); }"
        :class="open ? 'border-blue-500 ring-2 ring-blue-500/25 text-blue-700' : 'border-slate-300'"
        class="inline-flex h-11 min-w-32 cursor-pointer items-center justify-between gap-3 rounded-lg border bg-white px-3 text-sm font-medium text-slate-700 shadow-sm transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none">
        <span>{{ $label }}</span>
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
            <p class="px-2 pb-2 pt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}
            </p>

            <div class="space-y-1">
                @foreach ($optionList as $option)
                    @php
                        $value = (string) data_get($option, 'value');
                        $optionLabel = (string) data_get($option, 'label', $value);
                    @endphp

                    <label
                        class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 text-sm text-slate-700 transition hover:bg-blue-50">
                        @if ($multiple)
                            <input wire:model.live="{{ $model }}" data-global-loading type="checkbox" value="{{ $value }}"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        @else
                            <input wire:model.live="{{ $model }}" data-global-loading type="radio" value="{{ $value }}"
                                @change="open = false"
                                class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500">
                        @endif
                        <span>{{ $optionLabel }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </template>
</div>
