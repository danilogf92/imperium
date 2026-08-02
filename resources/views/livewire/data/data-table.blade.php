<section class="flex flex-col" style="gap: 1.25rem;">
    <x-unified-table-theme />
    <style>
        .data-action-button {
            transition: transform 150ms ease, box-shadow 150ms ease, filter 150ms ease;
        }

        .data-action-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 16px rgba(15, 23, 42, 0.16);
            filter: brightness(1.06);
        }

        .data-default-columns {
            background-color: #475569;
            border: 1px solid #334155;
            color: #fff;
        }

        .data-default-columns:hover {
            background-color: #64748b;
        }

        .data-back-to-projects {
            background-color: #eab308;
            border-color: #ca8a04;
            color: #422006;
        }

        .data-back-to-projects:hover {
            background-color: #facc15;
            border-color: #eab308;
            color: #422006;
        }

        .data-back-to-projects:active {
            background-color: #ca8a04;
        }

        .data-orders-disabled {
            cursor: not-allowed;
            opacity: 0.55;
        }

        .data-orders-disabled:hover {
            background-color: #eab308;
            border-color: #ca8a04;
            box-shadow: none;
            filter: none;
            transform: none;
        }

        .data-orders-tooltip-wrapper {
            position: relative;
        }

        .data-orders-tooltip {
            position: absolute;
            bottom: calc(100% + 10px);
            left: 50%;
            z-index: 40;
            width: max-content;
            max-width: 220px;
            padding: 8px 11px;
            border-radius: 8px;
            background-color: #0f172a;
            color: #f8fafc;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.35;
            text-align: center;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.24);
            opacity: 0;
            pointer-events: none;
            transform: translate(-50%, 5px);
            transition: opacity 150ms ease, transform 150ms ease;
        }

        .data-orders-tooltip::after {
            position: absolute;
            top: 100%;
            left: 50%;
            width: 8px;
            height: 8px;
            background-color: #0f172a;
            content: '';
            transform: translate(-50%, -4px) rotate(45deg);
        }

        .data-orders-tooltip-wrapper:hover .data-orders-tooltip,
        .data-orders-tooltip-wrapper:focus-within .data-orders-tooltip {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        .data-modal-cancel {
            background-color: #475569;
            border: 1px solid #334155;
            color: #fff;
            transition: transform 150ms ease, background-color 150ms ease, box-shadow 150ms ease;
        }

        .data-modal-cancel:hover {
            background-color: #64748b;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.18);
        }

        .data-edit-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            column-gap: 1rem;
            row-gap: 0.8rem;
        }

        .data-edit-grid-wide {
            grid-column: 1 / -1;
        }

        @media (max-width: 1023px) {
            .data-edit-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 639px) {
            .data-edit-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>

    <header x-data="{ open: true }" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div
            class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-blue-50 via-white to-white px-5 py-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Project data actions</h2>
                <p class="text-xs text-slate-500">Project details and available actions</p>
            </div>
            <button type="button" x-on:click="open = !open"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="h-5 w-5 transition-transform" x-bind:class="{ 'rotate-180': !open }" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
                </svg>
            </button>
        </div>
        <div x-show="open" x-collapse>
            <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h1 class="truncate text-2xl font-bold tracking-tight text-slate-900">
                        {{ Str::limit($project->name, 65) }}
                    </h1>
                    <div class="flex flex-wrap items-center text-sm text-slate-600"
                        style="margin-top: 0.85rem; gap: 0.65rem;">
                        <span
                            class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-800">
                            {{ $project->pda_code }}
                        </span>
                        <span
                            class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 font-medium text-slate-600">
                            {{ $project->company?->company_name }}
                        </span>
                        @if ($project->state)
                            <span class="rounded-full px-2.5 py-1 font-medium"
                                style="background-color: {{ $project->state->softColor() }}; color: {{ $project->state->textColor() }};">
                                {{ $project->state->value }}
                            </span>
                        @endif
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            {{ number_format($data->total()) }} records
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($canExportData)
                        <x-excel-export-button method="exportExcel" label="Export data" />
                    @endif
                    @if ($canEditData)
                        <button wire:click="openCreateModal" data-no-global-loading type="button"
                            class="data-action-button inline-flex h-10 items-center justify-center gap-2 rounded-lg px-4 text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2"
                            style="background-color: #7c3aed;" onmouseenter="this.style.backgroundColor='#6d28d9'"
                            onmouseleave="this.style.backgroundColor='#7c3aed'">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" d="M10 4v12M4 10h12" />
                            </svg>
                            New row
                        </button>
                    @endif
                    <a href="{{ route('projects.dashboard', ['project' => $project]) }}" wire:navigate
                        class="data-action-button inline-flex h-10 items-center justify-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                        <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.5 16.5h13M5.5 14V9.5m4.5 4.5V5.5m4.5 8.5V7.5" />
                        </svg>
                        Dashboard
                    </a>
                    @if ($hasOrders)
                        <a href="{{ route('projects.orders', ['project' => $project]) }}" wire:navigate
                            title="View project orders"
                            class="data-action-button data-back-to-projects inline-flex h-10 items-center justify-center gap-2 rounded-lg border px-4 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                            <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 3.5h12v13H4v-13Zm3 3h6m-6 3h6m-6 3h4" />
                            </svg>
                            Orders
                        </a>
                    @else
                        <span class="data-orders-tooltip-wrapper inline-flex" tabindex="0"
                            aria-describedby="orders-disabled-tooltip">
                            <button type="button" disabled aria-disabled="true"
                                class="data-action-button data-back-to-projects data-orders-disabled inline-flex h-10 items-center justify-center gap-2 rounded-lg border px-4 text-sm font-semibold shadow-sm">
                                <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 3.5h12v13H4v-13Zm3 3h6m-6 3h6m-6 3h4" />
                                </svg>
                                Orders
                            </button>
                            <span id="orders-disabled-tooltip" role="tooltip" class="data-orders-tooltip">
                                This project has no orders
                            </span>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <div x-data="{ open: true }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div
            class="flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-blue-50 via-white to-white px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-sm"
                    style="background-color: #dbeafe; color: #2563eb;">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 5.25h16.5l-6.375 7.125v5.25l-3.75 1.875v-7.125L3.75 5.25Z" />
                    </svg>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-bold text-slate-900">Data filters</h2>
                        @if ($hasActiveFilters)
                            <span
                                class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-white">
                                Active
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-sm text-slate-500">
                        Refine the records displayed in this project
                    </p>
                </div>
            </div>
            <button type="button" x-on:click="open = !open"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="h-5 w-5 transition-transform" x-bind:class="{ 'rotate-180': !open }" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
                </svg>
            </button>
        </div>

        <div x-show="open" x-collapse>
            <div class="flex items-end gap-3 overflow-x-auto px-4 py-4">
                <div class="relative min-w-64 flex-1">
                    <svg class="pointer-events-none absolute h-4 w-4 text-slate-400 transition-colors"
                        style="left: 0.75rem; top: 50%; transform: translateY(-50%);" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="m16 16 4 4" />
                    </svg>
                    <input wire:model.live.debounce.400ms="search" data-global-loading type="search"
                        aria-label="Search project data" placeholder="Search project data..."
                        style="padding-left: 2.75rem;"
                        class="h-11 w-full rounded-lg border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 shadow-sm transition duration-150 placeholder:text-slate-400 hover:-translate-y-px hover:border-blue-400 hover:bg-blue-50 hover:shadow-md focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/25">
                </div>

                <x-dashboard-filter-dropdown label="Area" model="areaFilter" :options="$filterOptions['areaFilter']" :selected="$areaFilter"
                    multiple />
                <x-dashboard-filter-dropdown label="Classification" model="classificationFilter" :options="$filterOptions['classificationFilter']"
                    :selected="$classificationFilter" multiple />
                <x-dashboard-filter-dropdown label="Item type" model="itemTypeFilter" :options="$filterOptions['itemTypeFilter']"
                    :selected="$itemTypeFilter" multiple />
                <x-dashboard-filter-dropdown label="Stage" model="stageFilter" :options="$filterOptions['stageFilter']" :selected="$stageFilter"
                    multiple />
                <x-dashboard-filter-dropdown label="Supplier" model="supplierFilter" :options="$filterOptions['supplierFilter']"
                    :selected="$supplierFilter" multiple />

                <x-per-page-select id="data-per-page" />

                <x-clear-filters-button method="resetFilters" :active="$hasActiveFilters" />
            </div>
        </div>
    </div>

    <div x-data="{ columnsOpen: true }" class="unified-table-shell">
        <div class="unified-table-toolbar">
            <div>
                <p class="text-sm font-semibold text-slate-700">Table columns</p>
                <p class="text-xs text-slate-500">
                    {{ count($visibleColumns) }} of {{ count($columnOptions) }} visible
                </p>
            </div>
            <div class="flex items-center gap-2">
                <div x-show="columnsOpen" x-transition class="flex items-center gap-2">
                    <x-dashboard-filter-dropdown label="Columns" model="visibleColumns" :options="collect($columnOptions)
                        ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
                        ->values()"
                        :selected="$visibleColumns" multiple />
                    <button wire:click="resetColumns" type="button"
                        class="data-action-button data-default-columns inline-flex h-11 items-center justify-center rounded-lg px-3 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Default columns
                    </button>
                </div>
                <button type="button" x-on:click="columnsOpen = !columnsOpen"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    title="Collapse column controls">
                    <svg class="h-5 w-5 transition-transform" x-bind:class="{ 'rotate-180': !columnsOpen }"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="unified-table-scroll">
            <table class="unified-data-table"
                style="min-width: max(100%, {{ max(count($visibleColumns), 1) * 145 }}px)">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-700">
                    <tr>
                        @foreach ($visibleColumns as $column)
                            <th @if ($column !== 'actions') wire:click="setSortBy('{{ $column }}')" data-global-loading @endif
                                @class([
                                    'whitespace-nowrap px-3 py-3 transition',
                                    'cursor-pointer hover:bg-blue-100 hover:text-blue-700' =>
                                        $column !== 'actions',
                                    'text-center' => $column === 'actions',
                                    'text-right' => in_array($column, $numericColumns, true),
                                ])>
                                <span class="inline-flex items-center gap-1">
                                    {{ $columnOptions[$column] }}
                                    @if ($column !== 'actions' && $sortBy === $column)
                                        <span>{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                                    @endif
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($data as $item)
                        <tr wire:key="project-data-{{ $item->id }}"
                            class="bg-white transition hover:bg-blue-50/70">
                            @foreach ($visibleColumns as $column)
                                @if ($column === 'actions')
                                    <td class="whitespace-nowrap px-3 py-2">
                                        <div class="flex items-center justify-center gap-2">
                                            @if ($canEditData)
                                                <button wire:click="openEditModal({{ $item->id }})"
                                                    data-no-global-loading type="button" title="Edit data"
                                                    aria-label="Edit data"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-blue-600 text-white shadow-sm transition duration-150 hover:-translate-y-px hover:bg-blue-500 hover:shadow-md active:translate-y-0 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                                                    <span class="sr-only">Edit data</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m16.862 3.487 1.651-1.65a2.121 2.121 0 1 1 3 3L10.582 15.768 6.75 16.5l.732-3.832L18.413 1.737M15.75 5.25l3 3M5.25 5.25H4.5A2.25 2.25 0 0 0 2.25 7.5v12A2.25 2.25 0 0 0 4.5 21.75h12a2.25 2.25 0 0 0 2.25-2.25v-.75" />
                                                    </svg>
                                                </button>
                                            @endif
                                            @if ($canDeleteData)
                                                <button wire:click="openDeleteModal({{ $item->id }})"
                                                    data-no-global-loading type="button" title="Delete data"
                                                    aria-label="Delete data"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-600 text-white shadow-sm transition duration-150 hover:-translate-y-px hover:bg-red-500 hover:shadow-md active:translate-y-0 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
                                                    <span class="sr-only">Delete data</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 7.5h12m-10.5 0 .75 11.25A2.25 2.25 0 0 0 10.5 21h3a2.25 2.25 0 0 0 2.25-2.25L16.5 7.5M9.75 7.5V4.875A1.875 1.875 0 0 1 11.625 3h.75a1.875 1.875 0 0 1 1.875 1.875V7.5M10 11v6m4-6v6" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @else
                                    <td @class([
                                        'px-3 py-2',
                                        'whitespace-nowrap' => !in_array(
                                            $column,
                                            ['description', 'observations'],
                                            true),
                                        'max-w-md' => in_array($column, ['description', 'observations'], true),
                                        'text-right tabular-nums' => in_array($column, $numericColumns, true),
                                        'font-medium text-slate-900' => $column === 'area',
                                    ])>
                                        @switch($column)
                                            @case('global_price')
                                            @case('real_value')

                                            @case('executed_dollars')
                                            @case('booked')
                                                $ {{ number_format((float) $item->{$column}, 2) }}
                                            @break

                                            @case('global_price_euros')
                                            @case('real_value_euros')

                                            @case('executed_euros')
                                            @case('booked_euros')
                                                € {{ number_format((float) $item->{$column}, 2) }}
                                            @break

                                            @case('percentage')
                                                {{ number_format((float) $item->{$column}, 2) }}%
                                            @break

                                            @case('qty')
                                            @case('unit_price')

                                            @case('committed')
                                                {{ number_format((float) $item->{$column}, 2) }}
                                            @break

                                            @default
                                                {{ $item->{$column} }}
                                        @endswitch
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                        @empty
                            <tr class="unified-empty-row">
                                <td colspan="{{ max(count($visibleColumns), 1) }}"
                                    class="px-6 py-16 text-center text-sm text-slate-500">
                                    {{ $hasActiveFilters ? 'No records match the selected filters.' : 'No data found for this project.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($data->hasPages())
                <div class="unified-table-pagination">
                    {{ $data->links() }}
                </div>
            @endif
        </div>

        @php
            $dataSaveMethod = $creatingData ? 'createData' : 'updateData';
            $linkedCurrencyColumns = [
                'global_price',
                'global_price_euros',
                'real_value',
                'real_value_euros',
                'executed_dollars',
                'executed_euros',
                'booked',
                'booked_euros',
            ];
        @endphp

        <x-dialog-modal name="edit-project-data" maxWidth="6xl">
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
                                @continue($column === 'actions')
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
                                        <input
                                            @if (in_array($column, $linkedCurrencyColumns, true)) wire:model.live.debounce.200ms="editData.{{ $column }}"
                                    data-no-global-loading
                                @else
                                    wire:model="editData.{{ $column }}" @endif
                                            type="{{ in_array($column, $numericColumns, true) ? 'number' : 'text' }}"
                                            @if (in_array($column, $numericColumns, true)) step="0.01" @endif
                                            class="block h-10 w-full rounded-lg border-gray-300 bg-white px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @endif
                                    @error("editData.{$column}")
                                        <span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span>
                                    @enderror
                                </label>
                            @endforeach
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
                                <input wire:model.live.debounce.250ms="bookedBase" data-no-global-loading type="number"
                                    min="0" step="0.01"
                                    class="block h-9 w-full rounded-md border-slate-300 bg-white px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </label>
                            <span class="hidden pb-2 text-slate-400 sm:block">×</span>
                            <label class="min-w-0 flex-1">
                                <span class="mb-1 block text-xs font-medium text-slate-600">Multiplier</span>
                                <input wire:model.live.debounce.250ms="bookedMultiplier" data-no-global-loading
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

                    <x-button wire:click="{{ $dataSaveMethod }}" data-global-loading wire:loading.attr="disabled"
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

        <x-modal name="delete-project-data" maxWidth="md" focusable>
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                        style="background-color: #fee2e2; color: #dc2626;">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 7.5h12m-10.5 0 .75 12h7.5l.75-12M9.75 7.5V5.25h4.5V7.5" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Delete data record</h2>
                        <p class="mt-1 text-sm text-slate-500">This action cannot be undone.</p>
                    </div>
                </div>

                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ \Illuminate\Support\Str::limit($deletingDataLabel, 180) }}
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button x-on:click="$dispatch('close-modal', 'delete-project-data')" wire:click="closeDeleteModal"
                        data-no-global-loading type="button"
                        class="data-modal-cancel inline-flex h-10 items-center rounded-lg px-4 text-sm font-semibold">
                        Cancel
                    </button>
                    <button wire:click="deleteData" data-global-loading wire:loading.attr="disabled"
                        wire:target="deleteData" type="button"
                        class="inline-flex h-10 items-center rounded-lg px-4 text-sm font-semibold text-white transition hover:brightness-110 disabled:opacity-60"
                        style="background-color: #dc2626;">
                        <span wire:loading.remove wire:target="deleteData">Delete record</span>
                        <span wire:loading wire:target="deleteData">Deleting...</span>
                    </button>
                </div>
            </div>
        </x-modal>
    </section>
