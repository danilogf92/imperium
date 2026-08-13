<div class="dashboard-page-shell">
    <x-unified-table-theme />
    <div class="dashboard-page-content flex flex-col" style="gap: 1.25rem;">
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Task management</p>
            <div class="mt-1 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ __('Tasks') }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Track purchasing and financial progress across your accessible projects.
                    </p>
                </div>
                <span class="inline-flex w-fit rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700">
                    {{ number_format($data->total()) }} records
                </span>
            </div>
        </section>

        <section x-data="{ open: true }" class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div
                class="soft-title-surface flex flex-col items-start gap-3 border-b px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-sm"
                        style="background-color: #dbeafe; color: #2563eb;">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 5.25h16.5l-6.375 7.125v5.25l-3.75 1.875v-7.125L3.75 5.25Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-bold text-slate-900">{{ __('Task filters') }}</h2>
                            @if (
                                $search !== '' ||
                                    $yearSearch !== [] ||
                                    $supplierFilter !== [] ||
                                    $order_numberFilter !== [] ||
                                    $pda_codeFilter !== [] ||
                                    $statusFilter !== []
                            )
                                <span
                                    class="rounded-full bg-blue-600 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-white">Active</span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-sm text-slate-500">Refine the records displayed in the table</p>
                    </div>
                </div>
                <button type="button" x-on:click="open = !open"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700">
                    <svg class="h-5 w-5 transition-transform" x-bind:class="{ 'rotate-180': !open }" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
                    </svg>
                </button>
            </div>

            <div x-show="open" x-collapse>
                <div class="flex flex-wrap items-center gap-3 px-4 py-4">
                    <div class="relative w-full min-w-0 flex-1 sm:min-w-64">
                        <svg class="pointer-events-none absolute h-4 w-4 text-slate-400"
                            style="left: .75rem; top: 50%; transform: translateY(-50%);" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m16 16 4 4" />
                        </svg>
                        <input wire:model.live.debounce.500ms="search" data-global-loading type="search"
                            placeholder="{{ __('Search tasks...') }}" style="padding-left: 2.75rem;"
                            class="h-11 w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm transition hover:-translate-y-px hover:border-blue-400 hover:bg-blue-50 hover:shadow-md focus:border-blue-500 focus:bg-white focus:ring-blue-500">
                    </div>

                    <x-dashboard-filter-dropdown label="Year" model="yearSearch" :options="collect($years)->map(
                        fn($year) => ['value' => (string) $year, 'label' => (string) $year],
                    )" :selected="$yearSearch"
                        multiple />
                    <x-dashboard-filter-dropdown label="Supplier" model="supplierFilter" :options="collect($supplierOptions)->map(fn($value) => ['value' => $value, 'label' => $value])"
                        :selected="$supplierFilter" multiple />
                    <x-dashboard-filter-dropdown label="Order no." model="order_numberFilter" :options="collect($orderNumberOptions)->map(fn($value) => ['value' => $value, 'label' => $value])"
                        :selected="$order_numberFilter" multiple />
                    <x-dashboard-filter-dropdown label="PDA code" model="pda_codeFilter" :options="collect($pda_Options)->map(fn($value) => ['value' => $value, 'label' => $value])"
                        :selected="$pda_codeFilter" multiple />
                    <x-dashboard-filter-dropdown label="Status" model="statusFilter" :options="collect($statusOptions)->map(
                        fn($value) => ['value' => $value, 'label' => ucfirst($value)],
                    )" :selected="$statusFilter"
                        multiple />

                    <x-per-page-select id="tasks-per-page" />

                    <x-clear-filters-button method="resetFilters" :active="$search !== '' || $yearSearch !== [] || $supplierFilter !== [] || $order_numberFilter !== [] || $pda_codeFilter !== [] || $statusFilter !== []" />
                </div>
            </div>
        </section>

        <section class="unified-table-shell">
            <div class="unified-table-toolbar">
                <div>
                    <p class="text-sm font-semibold text-slate-700">{{ __('Table columns') }}</p>
                    <p class="text-xs text-slate-500">
                        {{ count($visibleColumns) }} of {{ count($columnOptions) }} visible
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <x-dashboard-filter-dropdown label="Columns" model="visibleColumns" :options="collect($columnOptions)
                        ->except('actions')
                        ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
                        ->values()"
                        :selected="$visibleColumns" multiple />
                    <button wire:click="resetColumns" data-global-loading type="button"
                        class="inline-flex h-11 items-center rounded-lg px-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-px hover:brightness-110 hover:shadow-md"
                        style="background-color: #475569;">
                        Default columns
                    </button>
                </div>
            </div>

            <div class="unified-table-scroll">
                <table class="unified-data-table"
                    style="min-width: max(100%, {{ max(count($visibleColumns), 1) * 145 }}px)">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-700">
                        <tr>
                            @foreach ($visibleColumns as $column)
                                <th @if (!in_array($column, ['pda_code', 'actions'], true)) wire:click="setSortBy('{{ $column }}')" data-global-loading @endif
                                    @class([
                                        'whitespace-nowrap px-3 py-3',
                                        'cursor-pointer transition hover:bg-blue-100 hover:text-blue-700' => !in_array(
                                            $column,
                                            ['pda_code', 'actions'],
                                            true),
                                        'text-center' => $column === 'actions',
                                        'text-right' => in_array(
                                            $column,
                                            ['qty', 'real_value', 'global_price', 'booked', 'percentage'],
                                            true),
                                    ])>
                                    {{ __($columnOptions[$column]) }}
                                    @if ($sortBy === $column)
                                        {{ strtoupper($sortDir) === 'ASC' ? '↑' : '↓' }}
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($data as $item)
                            <tr wire:key="task-{{ $item->id }}" class="bg-white transition hover:bg-blue-50/70">
                                @foreach ($visibleColumns as $column)
                                    @if ($column === 'actions')
                                        <td class="whitespace-nowrap px-3 py-2">
                                            <div class="flex items-center justify-center gap-2">
                                                @if (in_array((int) $item->project->company_id, $updateCompanyIds, true))
                                                    <button wire:click="openEditModal({{ $item->id }})"
                                                        data-no-global-loading type="button" title="Edit task"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-blue-600 text-white shadow-sm transition hover:-translate-y-px hover:bg-blue-500 hover:shadow-md">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="m16.862 3.487 1.651-1.65a2.121 2.121 0 1 1 3 3L10.582 15.768 6.75 16.5l.732-3.832L18.413 1.737M15.75 5.25l3 3M5.25 5.25H4.5A2.25 2.25 0 0 0 2.25 7.5v12A2.25 2.25 0 0 0 4.5 21.75h12a2.25 2.25 0 0 0 2.25-2.25v-.75" />
                                                        </svg>
                                                    </button>
                                                @endif
                                                @if (in_array((int) $item->project->company_id, $deleteCompanyIds, true))
                                                    <button wire:click="openDeleteModal({{ $item->id }})"
                                                        data-no-global-loading type="button" title="Delete task"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-600 text-white shadow-sm transition hover:-translate-y-px hover:bg-red-500 hover:shadow-md">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-[18px] w-[18px]" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M4 7h16M9 7V4.5h6V7M7 7l.75 12h8.5L17 7M10 11v5M14 11v5" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    @else
                                        <td @class([
                                            'px-3 py-2',
                                            'max-w-lg' => $column === 'description',
                                            'whitespace-nowrap' => $column !== 'description',
                                            'text-right tabular-nums' => in_array(
                                                $column,
                                                ['qty', 'real_value', 'global_price', 'booked', 'percentage'],
                                                true),
                                        ])>
                                            @if (in_array($column, ['real_value', 'global_price', 'booked'], true))
                                                $ {{ number_format((float) $item->{$column}, 2) }}
                                            @elseif ($column === 'percentage')
                                                {{ number_format((int) $item->percentage) }}%
                                            @else
                                                {{ $item->{$column} }}
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @empty
                            <tr class="unified-empty-row">
                                <td colspan="{{ max(count($visibleColumns), 1) }}"
                                    class="px-6 py-16 text-center text-sm text-slate-500">
                                    {{ __('No tasks match the selected filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($data->hasPages())
                <div class="unified-table-pagination">{{ $data->links() }}</div>
            @endif
        </section>
    </div>

    <x-dialog-modal name="edit-task-data" maxWidth="3xl" close-method="closeEditModal">
        <x-slot name="title">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ __('Update task progress') }}</h2>
                <p class="mt-1 text-sm font-normal text-gray-500">Change the completion percentage for this task.</p>
            </div>
        </x-slot>
        <x-slot name="content">
            <label class="block py-2">
                <span class="mb-1.5 block text-sm font-medium text-gray-700">Percentage</span>
                <div class="relative">
                    <input wire:model="editData.percentage" type="number" min="0" max="100"
                        step="1" inputmode="numeric"
                        x-on:keydown="if (['.', ',', 'e', 'E', '+', '-'].includes($event.key)) $event.preventDefault()"
                        x-on:input="$el.value = $el.value.split(/[.,]/)[0].replace(/[^0-9]/g, '')"
                        style="padding-right: 2.75rem;"
                        class="h-11 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <span
                        class="pointer-events-none absolute inline-flex items-center justify-center font-semibold text-gray-400"
                        style="right: .9rem; top: 50%; transform: translateY(-50%); line-height: 1;">%</span>
                </div>
                @error('editData.percentage')
                    <span class="mt-1.5 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </label>
        </x-slot>
        <x-slot name="footer">
            <div class="flex w-full flex-col-reverse gap-3 sm:flex-row sm:justify-end sm:gap-4">
                <x-secondary-button wire:click="closeEditModal" data-no-global-loading
                    style="background-color:#ef4444;border-color:#dc2626;color:#fff;">Cancel</x-secondary-button>
                <x-button wire:click="updateData" data-global-loading wire:loading.attr="disabled"
                    wire:target="updateData" style="background-color:#2563eb;color:#fff;">
                    <span wire:loading.remove wire:target="updateData">Save changes</span>
                    <span wire:loading wire:target="updateData">Saving...</span>
                </x-button>
            </div>
        </x-slot>
    </x-dialog-modal>

    <x-dialog-modal name="delete-task-data" maxWidth="md" close-method="closeDeleteModal">
        <x-slot name="title">Delete task</x-slot>
        <x-slot name="content">
            <p class="text-sm text-gray-600">Are you sure you want to delete this task?</p>
            <p class="mt-3 rounded-lg bg-red-50 p-3 text-sm font-medium text-red-700">
                {{ \Illuminate\Support\Str::limit($deletingDataLabel, 180) }}
            </p>
        </x-slot>
        <x-slot name="footer">
            <div class="flex w-full flex-col-reverse gap-3 sm:flex-row sm:justify-end sm:gap-4">
                <x-secondary-button wire:click="closeDeleteModal" data-no-global-loading
                    style="background-color:#ef4444;border-color:#dc2626;color:#fff;">Cancel</x-secondary-button>
                <x-danger-button wire:click="deleteData" data-global-loading wire:loading.attr="disabled"
                    wire:target="deleteData">
                    <span wire:loading.remove wire:target="deleteData">Delete task</span>
                    <span wire:loading wire:target="deleteData">Deleting...</span>
                </x-danger-button>
            </div>
        </x-slot>
    </x-dialog-modal>
</div>
