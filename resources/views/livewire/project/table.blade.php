<div>
    <x-unified-table-theme />
    @php
        $columnKeys = array_keys($columnOptions);
        $columnCount = count($visibleColumns);
        $physicalColumns = [
            'id', 'name', 'links', 'pda_code', 'upload_pda', 'project_ideas', 'rate', 'state', 'investments',
            'classification', 'justification', 'forecast_start_date', 'forecast_end_date', 'order', 'plant',
            'creator', 'responsible', 'data_uploaded', 'quartile_date', 'approve_date', 'close_date',
            'file_name', 'created_at', 'updated_at', 'budgeted_euros', 'real_euros',
            'budgeted_dollars', 'real_dollars', 'actions',
        ];
    @endphp

    @if ($active)
        <div class="unified-table-shell relative">
            <style>
                .default-columns-button {
                    background-color: #475569;
                    border: 1px solid #334155;
                    color: #ffffff;
                    transition: transform 150ms ease, background-color 150ms ease, box-shadow 150ms ease;
                }

                .default-columns-button:hover {
                    background-color: #64748b;
                    transform: translateY(-1px);
                    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.2);
                }

                .default-columns-button:active {
                    background-color: #334155;
                    transform: translateY(0);
                }

                .upload-document-cancel {
                    background-color: #475569;
                    border: 1px solid #334155;
                    color: #ffffff;
                    transition: transform 150ms ease, background-color 150ms ease, box-shadow 150ms ease;
                }

                .upload-document-cancel:hover {
                    background-color: #64748b;
                    transform: translateY(-1px);
                    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.18);
                }

                .upload-document-cancel:active {
                    background-color: #334155;
                    transform: translateY(0);
                }

                .project-table>thead>tr>th {
                    min-width: 0;
                    overflow: hidden;
                    padding: 0.3rem 0.3rem !important;
                    text-overflow: ellipsis;
                    font-size: 0.68rem !important;
                    line-height: 0.95rem !important;
                }

                .project-table>tbody>tr:not(.project-empty-row)>th,
                .project-table>tbody>tr:not(.project-empty-row)>td {
                    min-width: 0;
                    overflow: hidden;
                    padding: 0.25rem 0.3rem !important;
                    text-overflow: ellipsis;
                    font-size: 0.75rem !important;
                    line-height: 1rem !important;
                }

                .project-table>tbody>tr:not(.project-empty-row) {
                    min-height: 2.75rem;
                }

                .project-table>tbody>tr:not(.project-empty-row) .rounded-full {
                    padding: 0.2rem 0.5rem !important;
                    font-size: 0.68rem !important;
                    line-height: 0.9rem !important;
                }

                .project-table>tbody>tr:not(.project-empty-row)>td:last-child {
                    overflow: visible;
                }

                .project-table {
                    display: block;
                    min-width: max(100%, {{ max($columnCount, 1) * 116 }}px);
                }

                .project-table > thead,
                .project-table > tbody {
                    display: block;
                }

                .project-table > thead > tr,
                .project-table > tbody > tr:not(.project-empty-row) {
                    display: grid;
                    grid-template-columns: repeat({{ max($columnCount, 1) }}, minmax(116px, 1fr));
                }

                @foreach ($physicalColumns as $physicalIndex => $columnKey)
                    @if (!in_array($columnKey, $visibleColumns, true))
                        .project-table>thead>tr> :nth-child({{ $physicalIndex + 1 }}),
                        .project-table>tbody>tr:not(.project-empty-row)> :nth-child({{ $physicalIndex + 1 }}) {
                            display: none;
                        }
                    @else
                        .project-table>thead>tr> :nth-child({{ $physicalIndex + 1 }}),
                        .project-table>tbody>tr:not(.project-empty-row)> :nth-child({{ $physicalIndex + 1 }}) {
                            order: {{ array_search($columnKey, $columnKeys, true) }};
                        }
                    @endif
                @endforeach
            </style>

            <div class="unified-table-toolbar">
                <div>
                    <p class="text-sm font-semibold text-gray-700">{{ __('Table columns') }}</p>
                    <p class="text-xs text-gray-500">
                        Showing {{ $projects->firstItem() ?? 0 }}–{{ $projects->lastItem() ?? 0 }} of
                        {{ number_format($projects->total()) }} projects · {{ count($visibleColumns) }} of
                        {{ count($columnOptions) }} columns visible
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <x-dashboard-filter-dropdown label="Columns" model="visibleColumns" :options="collect($columnOptions)
                        ->except('actions')
                        ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
                        ->values()"
                        :selected="$visibleColumns" multiple />

                    <button wire:click="resetColumns" type="button"
                        class="default-columns-button inline-flex h-11 items-center justify-center rounded-lg px-3 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Default columns
                    </button>
                </div>
            </div>

            <div class="unified-table-scroll">
                <table class="project-table unified-data-table">

                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-700">
                        <tr>

                            {{-- ID --}}
                            <th wire:click="setSortBy('id')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                <div class="flex items-center gap-1">
                                    ID

                                    @if ($sortBy === 'id')
                                        <span>
                                            {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </div>
                            </th>

                            {{-- Nombre --}}
                            <th wire:click="setSortBy('name')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                <div class="flex items-center gap-1">
                                    Name

                                    @if ($sortBy === 'name')
                                        <span>
                                            {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </div>
                            </th>

                            {{-- Enlaces --}}
                            <th scope="col" class="whitespace-nowrap px-2 py-2">
                                Links
                            </th>

                            {{-- PDA --}}
                            <th wire:click="setSortBy('pda_code')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                <div class="flex items-center gap-1">
                                    PDA code

                                    @if ($sortBy === 'pda_code')
                                        <span>
                                            {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </div>
                            </th>

                            {{-- Upload PDA --}}
                            <th scope="col" class="whitespace-nowrap px-2 py-2">
                                Upload PDA
                            </th>

                            <th scope="col" class="whitespace-nowrap px-2 py-2">
                                Project ideas
                            </th>

                            {{-- Rate --}}
                            <th wire:click="setSortBy('rate')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                <div class="flex items-center gap-1">
                                    Rate

                                    @if ($sortBy === 'rate')
                                        <span>
                                            {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </div>
                            </th>

                            {{-- Estado --}}
                            <th wire:click="setSortBy('state')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                <div class="flex items-center gap-1">
                                    State

                                    @if ($sortBy === 'state')
                                        <span>
                                            {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </div>
                            </th>

                            {{-- Inversión --}}
                            <th wire:click="setSortBy('investments')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                <div class="flex items-center gap-1">
                                    Investments

                                    @if ($sortBy === 'investments')
                                        <span>
                                            {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </div>
                            </th>

                            {{-- Clasificación --}}
                            <th wire:click="setSortBy('classification_of_investments')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                <div class="flex items-center gap-1">
                                    Classification

                                    @if ($sortBy === 'classification_of_investments')
                                        <span>
                                            {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </div>
                            </th>

                            {{-- Justificación --}}
                            <th wire:click="setSortBy('justification')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                <div class="flex items-center gap-1">
                                    Justification

                                    @if ($sortBy === 'justification')
                                        <span>
                                            {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </div>
                            </th>

                            {{-- Fecha de inicio --}}
                            <th wire:click="setSortBy('forecast_start_date')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                <div class="flex items-center gap-1">
                                    Forecast Start Year

                                    @if ($sortBy === 'forecast_start_date')
                                        <span>
                                            {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </div>
                            </th>

                            {{-- Fecha de finalización --}}
                            <th wire:click="setSortBy('forecast_end_date')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                <div class="flex items-center gap-1">
                                    Forecast End Date

                                    @if ($sortBy === 'forecast_end_date')
                                        <span>
                                            {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                                        </span>
                                    @endif
                                </div>
                            </th>

                            <th wire:click="setSortBy('order')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                <div class="flex items-center gap-1">
                                    Order
                                    @if ($sortBy === 'order')
                                        <span>{{ $sortDir === 'ASC' ? '↑' : '↓' }}</span>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="whitespace-nowrap px-2 py-2">Plant</th>
                            <th scope="col" class="whitespace-nowrap px-2 py-2">Created By</th>
                            <th scope="col" class="whitespace-nowrap px-2 py-2">Responsible</th>

                            <th wire:click="setSortBy('data_uploaded')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                Data Uploaded
                            </th>
                            <th wire:click="setSortBy('quartile_date')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                Quartile Date
                            </th>
                            <th wire:click="setSortBy('approve_date')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                Approved Date
                            </th>
                            <th wire:click="setSortBy('close_date')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                Close Date
                            </th>
                            <th wire:click="setSortBy('file_name')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                Document Name
                            </th>
                            <th wire:click="setSortBy('created_at')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                Created At
                            </th>
                            <th wire:click="setSortBy('updated_at')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
                                Updated At
                            </th>

                            <th wire:click="setSortBy('budgeted_euros')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
                                Budgeted Euros
                            </th>
                            <th wire:click="setSortBy('real_euros')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
                                Real Euros
                            </th>
                            <th wire:click="setSortBy('budgeted_dollars')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
                                Budgeted Dollars
                            </th>
                            <th wire:click="setSortBy('real_dollars')" scope="col"
                                class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
                                Real Dollars
                            </th>

                            {{-- Acciones --}}
                            <th scope="col" class="whitespace-nowrap px-2 py-2 text-center">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($projects as $project)
                            @php
                                $projectName = \Illuminate\Support\Str::limit($project->name, 50);
                            @endphp

                            <tr wire:key="project-row-{{ $project->id }}"
                                @if (in_array((int) $project->company_id, $updateCompanyIds, true))
                                    x-on:click="if (!$event.target.closest('a, button, input, select, textarea, [role=button]')) $dispatch('open-project-edit', { projectId: {{ $project->id }} })"
                                @endif
                                @class([
                                    'border-b border-gray-200 bg-white transition hover:bg-gray-50',
                                    'cursor-pointer' => in_array((int) $project->company_id, $updateCompanyIds, true),
                                ])>

                                {{-- ID --}}
                                <th scope="row" class="whitespace-nowrap px-2 py-2 font-medium text-gray-900">
                                    {{ $project->id }}
                                </th>

                                {{-- Nombre --}}
                                <td class="whitespace-nowrap px-2 py-2 font-bold text-gray-900">
                                    @if ($project->data_uploaded)
                                        <a href="{{ route('projects.data', ['project' => $project->slug]) }}"
                                            class="text-red-500 hover:text-red-600 hover:underline">
                                            {{ $projectName }}
                                        </a>
                                    @else
                                        <span>
                                            {{ $projectName }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Dashboard y órdenes --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    <div class="flex items-center gap-2">
                                        @if ($project->data_uploaded)
                                            <a href="{{ route('projects.dashboard', ['project' => $project->slug]) }}"
                                                wire:navigate class="text-red-500 hover:text-red-600 hover:underline">
                                                Dashboard
                                            </a>

                                            <span class="text-slate-300">|</span>
                                        @endif

                                        @if ($project->has_orders)
                                            <a href="{{ route('projects.orders', ['project' => $project->id]) }}"
                                                wire:navigate
                                                class="font-semibold text-blue-600 hover:text-blue-500 hover:underline">
                                                Orders
                                            </a>

                                            @if ($project->data_uploaded)
                                                <span class="text-slate-300">|</span>
                                            @endif
                                        @endif

                                        @if ($project->data_uploaded)
                                            <a href="{{ route('projects.data', ['project' => $project->slug]) }}"
                                                wire:navigate
                                                class="text-emerald-600 hover:text-emerald-500 hover:underline">
                                                Data
                                            </a>
                                        @endif
                                    </div>
                                </td>

                                {{-- Código PDA --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->pda_code }}
                                </td>

                                {{-- Archivo --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    <div class="flex items-center gap-2">
                                        @if (in_array((int) $project->company_id, $updateCompanyIds, true))
                                            @if (filled($project->upload_pda))
                                                <button wire:click="openDeleteDocumentModal({{ $project->id }})"
                                                    data-no-global-loading type="button"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-white shadow-sm transition hover:-translate-y-px hover:brightness-110 hover:shadow-md active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                                    style="background-color: #16a34a" title="Manage document"
                                                    aria-label="Manage document">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.25 3.75H6.75A2.25 2.25 0 0 0 4.5 6v12a2.25 2.25 0 0 0 2.25 2.25h10.5A2.25 2.25 0 0 0 19.5 18V9m-5.25-5.25L19.5 9m-5.25-5.25V9h5.25M8.25 15.75h7.5" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button wire:click="openDocumentModal({{ $project->id }})"
                                                    type="button" data-no-global-loading
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-white shadow-sm transition hover:-translate-y-px hover:brightness-110 hover:shadow-md active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                                                    style="background-color: #eab308" title="Upload document"
                                                    aria-label="Upload document">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.25 3.75H6.75A2.25 2.25 0 0 0 4.5 6v12a2.25 2.25 0 0 0 2.25 2.25h10.5A2.25 2.25 0 0 0 19.5 18V9m-5.25-5.25L19.5 9m-5.25-5.25V9h5.25M12 12v5m0-5-2 2m2-2 2 2" />
                                                    </svg>
                                                </button>
                                            @endif
                                        @else
                                            <span @class([
                                                'inline-flex h-8 w-8 items-center justify-center rounded-md text-white',
                                            ])
                                                style="background-color: {{ filled($project->upload_pda) ? '#16a34a' : '#eab308' }}">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14.25 3.75H6.75A2.25 2.25 0 0 0 4.5 6v12a2.25 2.25 0 0 0 2.25 2.25h10.5A2.25 2.25 0 0 0 19.5 18V9m-5.25-5.25L19.5 9m-5.25-5.25V9h5.25" />
                                                </svg>
                                            </span>
                                        @endif

                                    </div>
                                </td>

                                {{-- Project ideas --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    <button wire:click="openProjectIdeaModal({{ $project->id }})" type="button"
                                        data-no-global-loading
                                        title="{{ filled($project->project_idea_path) ? 'Manage project ideas' : 'Upload project ideas' }}"
                                        class="inline-flex h-8 items-center gap-1.5 rounded-md px-2.5 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-px hover:brightness-110"
                                        style="background-color: {{ filled($project->project_idea_path) ? '#16a34a' : '#d97706' }}">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 3.75h8.25L18 7.5v12.75H6V3.75Zm8.25 0V7.5H18M9 11.25l6 6m0-6-6 6" />
                                        </svg>
                                        {{ filled($project->project_idea_path) ? 'Manage' : 'Upload' }}
                                    </button>
                                </td>

                                {{-- Rate --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->rate }}
                                </td>

                                {{-- Estado --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    @if ($project->state)
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                            style="background-color: {{ $project->state->softColor() }}; color: {{ $project->state->textColor() }};">
                                            {{ $project->state->value }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Inversión --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->investments?->value }}
                                </td>

                                {{-- Clasificación --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->classification_of_investments?->value }}
                                </td>

                                {{-- Justificación --}}
                                <td class="px-2 py-2">
                                    <div class="max-w-xs">
                                        {{ \Illuminate\Support\Str::limit($project->justification?->value, 100) }}
                                    </div>
                                </td>

                                {{-- Fecha inicio --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->forecast_start_date?->format('Y') ?? '—' }}
                                </td>

                                {{-- Fecha final --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->forecast_end_date?->format('Y-m-d') }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2 font-semibold text-slate-700">
                                    {{ $project->order ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->company?->company_name ?? ($project->company?->company_code ?? '—') }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->creator?->name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->responsible?->name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2 text-center">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        style="background-color: {{ $project->data_uploaded ? '#D1FAE5' : '#F1F5F9' }}; color: {{ $project->data_uploaded ? '#065F46' : '#475569' }};">
                                        {{ $project->data_uploaded ? 'Yes' : 'No' }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->quartile_date?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->approve_date?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->close_date?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="max-w-56 truncate px-2 py-2" title="{{ $project->file_name }}">
                                    {{ $project->file_name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->created_at?->format('Y-m-d H:i') }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->updated_at?->format('Y-m-d H:i') }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2 text-right font-semibold text-slate-700">
                                    € {{ number_format((float) $project->budgeted_euros, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 text-right font-semibold text-slate-700">
                                    € {{ number_format((float) $project->real_euros, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 text-right font-semibold text-slate-700">
                                    $ {{ number_format((float) $project->budgeted_dollars, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 text-right font-semibold text-slate-700">
                                    $ {{ number_format((float) $project->real_dollars, 2) }}
                                </td>

                                {{-- Editar y borrar --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    <div class="flex items-center justify-center gap-1">
                                        @if (in_array((int) $project->company_id, $updateCompanyIds, true))
                                            <button wire:click="openDataImportModal({{ $project->id }})"
                                                data-no-global-loading type="button"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md text-white shadow-sm transition hover:-translate-y-px hover:brightness-110 hover:shadow-md active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-1"
                                                style="background-color: {{ $project->data_uploaded ? '#dc2626' : '#0891b2' }}"
                                                title="{{ $project->data_uploaded ? 'Delete imported project data' : 'Import project data from Excel' }}"
                                                aria-label="{{ $project->data_uploaded ? 'Delete imported project data' : 'Import project data from Excel' }}">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    @if ($project->data_uploaded)
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 7.5h12m-10.5 0 .75 12h7.5l.75-12M9.75 7.5V5.25h4.5V7.5m-3 3v6m2.25-6v6" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4.5 19.5h15M12 4v11m0-11L8.5 7.5M12 4l3.5 3.5" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6.75 12.75v4.5h10.5v-4.5" />
                                                    @endif
                                                </svg>
                                            </button>
                                            <livewire:project.edit wire:key="edit-project-{{ $project->id }}"
                                                :project="$project" />
                                        @endif

                                        @if (in_array((int) $project->company_id, $deleteCompanyIds, true))
                                            <livewire:project.delete wire:key="delete-project-{{ $project->id }}"
                                                :project="$project" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="project-empty-row unified-empty-row">
                                <td colspan="{{ $columnCount }}" class="px-6 py-12 text-center">

                                    <div class="flex flex-col items-center justify-center text-gray-500">

                                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-10 w-10" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>

                                        <p class="font-semibold">
                                            No projects found
                                        </p>

                                        <p class="mt-1 text-sm">
                                            Change the search or selected filters.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if ($projects->hasPages())
                <div class="unified-table-pagination">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>

        <x-modal name="upload-project-document" maxWidth="lg" close-method="closeDocumentModal" focusable>
            <div class="p-4 sm:p-6" x-data="{ uploading: false, progress: 0 }" x-on:livewire-upload-start="uploading = true; progress = 0"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
                x-on:livewire-upload-finish="progress = 100; uploading = false"
                x-on:livewire-upload-error="uploading = false; progress = 0"
                x-on:livewire-upload-cancel="uploading = false; progress = 0">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Upload project document</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $documentProjectCode }}
                            @if ($documentProjectName !== '')
                                · {{ $documentProjectName }}
                            @endif
                        </p>
                    </div>
                    <button x-on:click="$dispatch('close-modal', 'upload-project-document')"
                        wire:click="closeDocumentModal" data-no-global-loading type="button"
                        class="rounded-md bg-slate-100 p-1 text-slate-500 transition hover:bg-slate-200 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                        </svg>
                    </button>
                </div>

                @if ($currentDocumentName)
                    <div
                        class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        Current file: <span class="font-semibold">{{ $currentDocumentName }}</span>
                    </div>
                @endif

                <div class="mt-5">
                    <span class="mb-2 block text-sm font-medium text-slate-700">Document</span>
                    <input id="project-document-file" wire:model="document" type="file"
                        accept=".pdf,application/pdf" data-no-global-loading class="sr-only">
                    <label for="project-document-file"
                        class="flex cursor-pointer items-center justify-between gap-3 rounded-lg border border-slate-300 bg-white p-2 text-sm shadow-sm transition hover:border-blue-400 hover:bg-blue-50">
                        <span class="inline-flex h-10 items-center rounded-md bg-blue-600 px-4 font-semibold text-white">
                            Select file
                        </span>
                        <span class="min-w-0 flex-1 truncate text-slate-600">
                            {{ $document?->getClientOriginalName() ?? 'No file selected' }}
                        </span>
                    </label>
                </div>
                <p class="mt-2 text-xs text-slate-500">PDF only. Maximum size: 10 MB.</p>
                @error('document')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror

                <div x-show="uploading" x-cloak class="mt-4" aria-live="polite">
                    <div class="mb-2 flex items-center justify-between gap-4 text-sm font-semibold text-blue-700">
                        <span>Uploading file...</span>
                        <span x-text="`${progress}%`">0%</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-blue-100">
                        <div class="h-full rounded-full bg-blue-600 transition-all duration-200 ease-out"
                            x-bind:style="`width: ${progress}%`"></div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button x-on:click="$dispatch('close-modal', 'upload-project-document')"
                        wire:click="closeDocumentModal" data-no-global-loading type="button"
                        class="upload-document-cancel inline-flex h-10 items-center rounded-lg px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Cancel
                    </button>
                    <button wire:click="uploadDocument" wire:loading.attr="disabled"
                        wire:target="uploadDocument,document" data-no-global-loading type="button"
                        class="inline-flex h-10 items-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="uploadDocument">Upload document</span>
                        <span wire:loading wire:target="uploadDocument">Uploading...</span>
                    </button>
                </div>
            </div>
        </x-modal>

        <x-modal name="manage-project-ideas" maxWidth="lg" close-method="closeProjectIdeaModal" focusable>
            <div class="p-4 sm:p-6" x-data="{ uploading: false, progress: 0 }"
                x-on:livewire-upload-start="uploading = true; progress = 0"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
                x-on:livewire-upload-finish="progress = 100; uploading = false"
                x-on:livewire-upload-error="uploading = false; progress = 0"
                x-on:livewire-upload-cancel="uploading = false; progress = 0">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 3.75h8.25L18 7.5v12.75H6V3.75Zm8.25 0V7.5H18M9 11.25l6 6m0-6-6 6" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Project ideas</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $projectIdeaProjectCode }}
                                @if ($projectIdeaProjectName !== '') · {{ $projectIdeaProjectName }} @endif
                            </p>
                        </div>
                    </div>
                    <button wire:click="closeProjectIdeaModal" data-no-global-loading type="button"
                        class="rounded-md p-1 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                        </svg>
                    </button>
                </div>

                @if ($currentProjectIdeaFileName)
                    <div class="mt-5 flex flex-col items-stretch gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Current Excel file</p>
                            <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $currentProjectIdeaFileName }}</p>
                        </div>
                        <button wire:click="downloadProjectIdea" wire:loading.attr="disabled"
                            wire:target="downloadProjectIdea" data-no-global-loading type="button"
                            class="inline-flex h-10 shrink-0 items-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="downloadProjectIdea">Download</span>
                            <span wire:loading wire:target="downloadProjectIdea">Preparing...</span>
                        </button>
                    </div>
                @else
                    <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        This project does not have a Project ideas file yet.
                    </div>
                @endif

                @if ($projectIdeaCanManage)
                    <div class="mt-5">
                        <input id="table-project-ideas-file" wire:model="projectIdeaFile" type="file"
                            accept=".xlsx,.xls" data-no-global-loading class="sr-only">
                        <label for="table-project-ideas-file"
                            class="group flex cursor-pointer items-center gap-4 rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 p-5 transition hover:border-emerald-500 hover:bg-emerald-50">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white shadow-sm transition group-hover:scale-105">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" d="M12 16V5m0 0L8 9m4-4 4 4M5 19h14" />
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold text-slate-800">
                                    {{ $currentProjectIdeaFileName ? 'Replace Excel file' : 'Select Excel file' }}
                                </span>
                                <span class="mt-1 block truncate text-xs text-slate-500">
                                    {{ $projectIdeaFile?->getClientOriginalName() ?? '.xlsx or .xls · maximum 10 MB' }}
                                </span>
                            </span>
                        </label>
                        @error('projectIdeaFile')
                            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="uploading" x-cloak class="mt-4" aria-live="polite">
                        <div class="mb-2 flex justify-between text-sm font-semibold text-emerald-700">
                            <span>Uploading Excel...</span><span x-text="`${progress}%`">0%</span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-emerald-100">
                            <div class="h-full rounded-full bg-emerald-600 transition-all duration-200"
                                x-bind:style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:flex-wrap sm:justify-end">
                    <button wire:click="closeProjectIdeaModal" data-no-global-loading type="button"
                        class="upload-document-cancel inline-flex h-10 items-center rounded-lg px-4 text-sm font-semibold">Close</button>
                    @if ($projectIdeaCanManage && $currentProjectIdeaFileName)
                        <button wire:click="deleteProjectIdea" wire:loading.attr="disabled"
                            wire:target="deleteProjectIdea" data-no-global-loading type="button"
                            class="inline-flex h-10 items-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="deleteProjectIdea">Delete Excel</span>
                            <span wire:loading wire:target="deleteProjectIdea">Deleting...</span>
                        </button>
                    @endif
                    @if ($projectIdeaCanManage)
                        <button wire:click="saveProjectIdea" wire:loading.attr="disabled"
                            wire:target="saveProjectIdea,projectIdeaFile" data-no-global-loading type="button"
                            class="inline-flex h-10 items-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60">
                            <span wire:loading.remove wire:target="saveProjectIdea">{{ $currentProjectIdeaFileName ? 'Replace Excel' : 'Upload Excel' }}</span>
                            <span wire:loading wire:target="saveProjectIdea">Saving...</span>
                        </button>
                    @endif
                </div>
            </div>
        </x-modal>

        <x-modal name="import-project-data" maxWidth="lg" close-method="closeDataImportModal" focusable>
            <div class="p-4 sm:p-6" x-data="{ uploading: false, progress: 0 }"
                x-on:livewire-upload-start="uploading = true; progress = 0"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
                x-on:livewire-upload-finish="progress = 100; uploading = false"
                x-on:livewire-upload-error="uploading = false; progress = 0"
                x-on:livewire-upload-cancel="uploading = false; progress = 0">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.5 19.5h15M12 4v11m0-11L8.5 7.5M12 4l3.5 3.5M6.75 12.75v4.5h10.5v-4.5" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Import project data</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $dataImportProjectCode }}
                                @if ($dataImportProjectName !== '')
                                    · {{ $dataImportProjectName }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <button x-on:click="$dispatch('close-modal', 'import-project-data')"
                        wire:click="closeDataImportModal" data-no-global-loading type="button"
                        class="rounded-md bg-slate-100 p-1 text-slate-500 transition hover:bg-slate-200 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                        </svg>
                    </button>
                </div>

                <div class="mt-5 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    Use the approved column structure. The Excel <strong>ID</strong> column is ignored and new
                    database IDs are generated automatically.
                    <a href="{{ route('templates') }}" wire:navigate
                        class="ml-1 font-semibold underline decoration-blue-400 underline-offset-2 hover:text-blue-950">
                        Download the template
                    </a>
                </div>

                @if ($dataImportExistingRows > 0)
                    <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4">
                        <h3 class="font-semibold text-red-900">Imported data already exists</h3>
                        <p class="mt-1 text-sm text-red-700">
                            This project contains <strong>{{ number_format($dataImportExistingRows) }}</strong>
                            data rows. Delete them before importing another Excel file.
                        </p>
                    </div>
                @else
                    <div class="mt-5">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Excel file</span>
                        <input id="project-data-excel-file" wire:model="dataImportFile" type="file"
                            accept=".xlsx,.xls" data-no-global-loading class="sr-only">
                        <label for="project-data-excel-file"
                            class="flex cursor-pointer items-center justify-between gap-3 rounded-lg border border-slate-300 bg-white p-2 text-sm shadow-sm transition hover:border-cyan-400 hover:bg-cyan-50">
                            <span class="inline-flex h-10 items-center rounded-md bg-cyan-600 px-4 font-semibold text-white">
                                Select file
                            </span>
                            <span class="min-w-0 flex-1 truncate text-slate-600">
                                {{ $dataImportFile?->getClientOriginalName() ?? 'No file selected' }}
                            </span>
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Excel .xlsx or .xls. Maximum size: 20 MB and 5,000 rows.</p>
                    @error('dataImportFile')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                @endif

                <div x-show="uploading" x-cloak class="mt-4" aria-live="polite">
                    <div class="mb-2 flex items-center justify-between gap-4 text-sm font-semibold text-cyan-700">
                        <span>Uploading file...</span>
                        <span x-text="`${progress}%`">0%</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-cyan-100">
                        <div class="h-full rounded-full bg-cyan-600 transition-all duration-200"
                            x-bind:style="`width: ${progress}%`"></div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button x-on:click="$dispatch('close-modal', 'import-project-data')"
                        wire:click="closeDataImportModal" data-no-global-loading type="button"
                        class="upload-document-cancel inline-flex h-10 items-center rounded-lg px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                        Cancel
                    </button>
                    @if ($dataImportExistingRows > 0)
                        <button wire:click="deleteImportedProjectData" data-global-loading
                            wire:loading.attr="disabled" wire:target="deleteImportedProjectData"
                            type="button"
                            class="inline-flex h-10 items-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-wait disabled:opacity-60">
                            <span wire:loading.remove wire:target="deleteImportedProjectData">Delete project data</span>
                            <span wire:loading wire:target="deleteImportedProjectData">Deleting...</span>
                        </button>
                    @else
                        <button wire:click="importProjectData" wire:loading.attr="disabled"
                            wire:target="importProjectData,dataImportFile" data-global-loading type="button"
                            class="inline-flex h-10 items-center gap-2 rounded-lg bg-cyan-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
                        <svg wire:loading.remove wire:target="importProjectData" class="h-4 w-4" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 4v11m0-11L8.5 7.5M12 4l3.5 3.5M6.75 12.75v4.5h10.5v-4.5" />
                        </svg>
                        <svg wire:loading wire:target="importProjectData" class="h-4 w-4 animate-spin"
                            viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="importProjectData">Import data</span>
                        <span wire:loading wire:target="importProjectData">Importing...</span>
                        </button>
                    @endif
                </div>
            </div>
        </x-modal>

        <x-modal name="delete-project-document" maxWidth="md" close-method="closeDeleteDocumentModal" focusable>
            <div class="p-4 sm:p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                        style="background-color: #fee2e2; color: #dc2626">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 7.5h12m-10.5 0 .75 12h7.5l.75-12M9.75 7.5V5.25h4.5V7.5m-3 3v6m2.25-6v6" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-lg font-semibold text-slate-900">Manage project document</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $deleteDocumentProjectCode }}
                            @if ($deleteDocumentProjectName !== '')
                                · {{ $deleteDocumentProjectName }}
                            @endif
                        </p>
                    </div>
                    <button x-on:click="$dispatch('close-modal', 'delete-project-document')"
                        wire:click="closeDeleteDocumentModal" data-no-global-loading type="button"
                        class="rounded-md p-1 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                        </svg>
                    </button>
                </div>

                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Current document</p>
                    <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $deleteDocumentName }}</p>
                    <p class="mt-2 text-xs text-slate-500">Download the document or permanently remove it from this project.</p>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:flex-wrap sm:justify-end">
                    <button x-on:click="$dispatch('close-modal', 'delete-project-document')"
                        wire:click="closeDeleteDocumentModal" data-no-global-loading type="button"
                        class="upload-document-cancel inline-flex h-10 items-center rounded-lg px-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                        Cancel
                    </button>
                    <button wire:click="downloadDocument" wire:loading.attr="disabled" wire:target="downloadDocument"
                        data-no-global-loading type="button"
                        class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="downloadDocument">Download</span>
                        <span wire:loading wire:target="downloadDocument">Preparing...</span>
                    </button>
                    <button wire:click="deleteDocument" wire:loading.attr="disabled" wire:target="deleteDocument"
                        data-no-global-loading type="button"
                        class="inline-flex h-10 items-center rounded-lg px-4 text-sm font-semibold text-white transition hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60"
                        style="background-color: #dc2626">
                        <span wire:loading.remove wire:target="deleteDocument">Delete document</span>
                        <span wire:loading wire:target="deleteDocument">Deleting...</span>
                    </button>
                </div>
            </div>
        </x-modal>
    @else
        {{-- <livewire:user-disabled /> --}}
    @endif
</div>
