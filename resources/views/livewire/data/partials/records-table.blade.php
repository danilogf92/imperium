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
                                    {{ __($columnOptions[$column]) }}
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
                            @if ($canEditData) x-on:click="if (!$event.target.closest('a, button, input, select, textarea, [role=button]')) $wire.openEditModal({{ $item->id }})" @endif
                            @class([
                                'bg-white transition hover:bg-blue-50/70',
                                'cursor-pointer' => $canEditData,
                            ])>
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
