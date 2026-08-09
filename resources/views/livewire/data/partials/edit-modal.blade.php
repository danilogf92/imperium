@php
    $dataSaveMethod = $creatingData ? 'createData' : 'updateData';
@endphp

<x-dialog-modal name="edit-project-data" maxWidth="6xl" close-method="closeEditModal">
            <x-slot name="title">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ $creatingData ? 'Create data row' : 'Edit data record' }}
                        </h2>
                        <span wire:dirty
                            class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                            Unsaved changes
                        </span>
                    </div>
                    <p class="mt-1 text-sm font-normal text-gray-500">
                        {{ $creatingData ? 'Add information to' : 'Update the information for' }}
                        <span class="font-semibold text-gray-700">{{ $project->pda_code }}</span>
                        &middot; {{ $project->name }}.
                    </p>
                </div>
            </x-slot>

            <x-slot name="content">
                <div class="max-h-[70vh] space-y-6 overflow-y-auto py-2 pr-2">
                    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
                            <h3 class="font-semibold text-gray-900">Record information</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Update classification, financial values and procurement details.
                            </p>
                        </div>

                        <div class="data-edit-grid p-5">
                            @foreach ($columnOptions as $column => $label)
                                @continue($column === 'actions' || in_array($column, $linkedCurrencyColumns, true))
                                <label @class([
                                    'block min-w-0',
                                    'data-edit-grid-wide' => in_array(
                                        $column,
                                        ['description', 'observations'],
                                        true),
                                ])>
                                    <span class="mb-1.5 flex items-center gap-2 text-sm font-medium text-gray-700">
                                        {{ $label }}
                                        @if (in_array($column, $linkedCurrencyColumns, true))
                                            <span
                                                class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-blue-700">
                                                Linked
                                            </span>
                                        @endif
                                    </span>
                                    @if (in_array($column, ['description', 'observations'], true))
                                        <textarea wire:model="editData.{{ $column }}" rows="2" placeholder="Enter {{ strtolower($label) }}..."
                                            class="w-full resize-y rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    @else
                                        <input wire:model="editData.{{ $column }}"
                                            type="{{ in_array($column, $numericColumns, true) ? 'number' : 'text' }}"
                                            @if (in_array($column, $numericColumns, true)) step="0.01" @endif
                                            @readonly(in_array($column, $derivedEuroColumns, true))
                                            @class([
                                                'block h-10 w-full rounded-lg border-gray-300 px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500',
                                                'bg-white' => ! in_array($column, $derivedEuroColumns, true),
                                                'cursor-not-allowed bg-gray-100 text-gray-600' => in_array($column, $derivedEuroColumns, true),
                                            ])>
                                    @endif
                                    @error("editData.{$column}")
                                        <span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>
                                    @enderror
                                </label>
                            @endforeach

                            <section class="data-edit-grid-wide rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                                <div class="mb-3">
                                    <h4 class="text-sm font-bold text-slate-800">Financial values</h4>
                                    <p class="text-xs text-slate-500">
                                        Enter the dollar value. Its euro equivalent is calculated when you leave the field.
                                    </p>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    @foreach ([
                                        ['global_price', 'global_price_euros', 'Budgeted'],
                                        ['real_value', 'real_value_euros', 'Real'],
                                        ['executed_dollars', 'executed_euros', 'Executed'],
                                        ['booked', 'booked_euros', 'Booked'],
                                    ] as [$dollarColumn, $euroColumn, $financialLabel])
                                        <label class="block min-w-0">
                                            <span class="mb-1.5 block text-sm font-semibold text-slate-700">
                                                {{ $financialLabel }} <span class="text-emerald-700">$</span>
                                            </span>
                                            <input wire:model.blur="editData.{{ $dollarColumn }}" data-no-global-loading
                                                type="number" step="0.01" inputmode="decimal"
                                                class="block h-10 w-full rounded-lg border-gray-300 bg-white px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            @error("editData.{$dollarColumn}")
                                                <span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>
                                            @enderror
                                        </label>
                                        <label class="block min-w-0">
                                            <span class="mb-1.5 flex items-center justify-between gap-2 text-sm font-semibold text-slate-700">
                                                <span>{{ $financialLabel }} <span class="text-blue-700">€</span></span>
                                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-blue-700">
                                                    Auto-calculated
                                                </span>
                                            </span>
                                            <input wire:model="editData.{{ $euroColumn }}" type="number" step="0.01"
                                                readonly tabindex="-1"
                                                class="block h-10 w-full cursor-not-allowed rounded-lg border-gray-300 bg-gray-100 px-3 text-sm text-gray-600 shadow-sm">
                                            @error("editData.{$euroColumn}")
                                                <span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>
                                            @enderror
                                        </label>
                                    @endforeach
                                </div>
                            </section>
                        </div>
                    </section>

                    @php
                        $bookedCalculatorResult = round(
                            max((float) $bookedBase, 0) * max((float) $bookedMultiplier, 0),
                            2,
                        );
                    @endphp
                    <section class="rounded-lg border border-slate-200 bg-slate-50 p-3" x-data="{ copied: false }">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <label class="min-w-0 flex-1">
                                <span class="mb-1 block text-xs font-medium text-slate-600">Base</span>
                                <input wire:model.blur="bookedBase" data-no-global-loading type="number"
                                    min="0" step="0.01"
                                    class="block h-9 w-full rounded-md border-slate-300 bg-white px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </label>
                            <span class="hidden pb-2 text-slate-400 sm:block">×</span>
                            <label class="min-w-0 flex-1">
                                <span class="mb-1 block text-xs font-medium text-slate-600">Multiplier</span>
                                <input wire:model.blur="bookedMultiplier" data-no-global-loading
                                    type="number" min="0" step="0.000001"
                                    class="block h-9 w-full rounded-md border-slate-300 bg-white px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </label>
                            <span class="hidden pb-2 text-slate-400 sm:block">=</span>
                            <div class="min-w-0 flex-1">
                                <span class="mb-1 block text-xs font-medium text-slate-600">Result</span>
                                <div class="relative">
                                    <input x-ref="result" readonly type="text" inputmode="decimal"
                                        value="{{ number_format($bookedCalculatorResult, 2, '.', '') }}"
                                        x-on:click="$el.select()"
                                        class="block h-9 w-full rounded-md border-slate-300 bg-white py-2 pl-3 pr-20 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <button type="button" data-no-global-loading
                                        x-on:click="$refs.result.select(); $refs.result.setSelectionRange(0, $refs.result.value.length); document.execCommand('copy'); if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText($refs.result.value).catch(() => {}); copied = true; setTimeout(() => copied = false, 1500)"
                                        class="absolute inset-y-0 right-0 inline-flex items-center px-3 text-xs font-medium text-blue-600 hover:text-blue-800"
                                        title="Copy result">
                                    <span class="text-xs font-medium text-blue-600"
                                        x-text="copied ? 'Copied' : 'Copy'">Copy</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </x-slot>

            <x-slot name="footer">
                <div class="flex w-full items-center justify-end gap-4">
                    <x-secondary-button wire:click="closeEditModal" data-no-global-loading wire:loading.attr="disabled"
                        wire:target="{{ $dataSaveMethod }}"
                        style="background-color: #ef4444; border-color: #dc2626; color: #ffffff;"
                        onmouseenter="this.style.backgroundColor='#dc2626'"
                        onmouseleave="this.style.backgroundColor='#ef4444'">
                        Cancel
                    </x-secondary-button>

                    <x-button wire:click="{{ $dataSaveMethod }}"
                        data-no-global-loading
                        wire:loading.attr="disabled"
                        wire:target="{{ $dataSaveMethod }}"
                        style="background-color: #2563eb; border-color: #1d4ed8; color: #ffffff;"
                        onmouseenter="this.style.backgroundColor='#1d4ed8'"
                        onmouseleave="this.style.backgroundColor='#2563eb'">
                        <span wire:loading.remove wire:target="{{ $dataSaveMethod }}">
                            {{ $creatingData ? 'Create row' : 'Save changes' }}
                        </span>
                        <span wire:loading wire:target="{{ $dataSaveMethod }}">
                            {{ $creatingData ? 'Creating...' : 'Saving...' }}
                        </span>
                    </x-button>
                </div>
            </x-slot>
        </x-dialog-modal>
