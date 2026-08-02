<div class="min-h-screen bg-gray-100 py-6">
    <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Project Planification</h1>
            <p class="mt-1 text-sm text-gray-600">Build each project's timeline with the milestones configured in Admin.
            </p>
        </div>

        @if (session('planification-status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('planification-status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div
                class="flex flex-col gap-2 border-b border-gray-200 px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <label for="planification-search" class="sr-only">Search planification</label>
                    <div class="relative w-full sm:max-w-lg">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                            fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z" />
                        </svg>
                        <input id="planification-search" type="text" wire:model.live.debounce.400ms="search" data-global-loading
                            placeholder="Search project or milestone" autocomplete="off"
                            class="block h-10 w-full rounded-lg border border-slate-300 bg-white py-2 pl-11 pr-11 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        @if (filled($search))
                            <button type="button" wire:click="clearSearch" data-global-loading title="Clear search"
                                aria-label="Clear search"
                                class="absolute right-2 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                                </svg>
                            </button>
                        @endif
                    </div>
                    <span class="hidden whitespace-nowrap text-sm text-slate-500 lg:inline">
                        {{ number_format($plannedProjects->total()) }} projects
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    @if ($canExport)
                        <x-excel-export-button method="exportExcel" />
                    @endif
                    <x-per-page-select id="planification-per-page" />
                    <button type="button" wire:click="openCreate" data-no-global-loading
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add milestone
                    </button>
                </div>
            </div>

            <div x-data="{ open: true }" class="border-b border-slate-200">
                <div
                    class="flex items-center justify-between bg-gradient-to-r from-blue-50 via-white to-white px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 shadow-sm">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 5.25h16.5l-6.375 7.125v5.25l-3.75 1.875v-7.125L3.75 5.25Z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-sm font-bold text-slate-900">Planification filters</h2>
                                @if (count($plantFilter) ||
                                        count($statusFilter) ||
                                        count($creationYearFilter) ||
                                        $onlyWithMilestones ||
                                        $currency !== 'usd' ||
                                        $cellDisplay !== 'combined')
                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-white">
                                        Active
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500">Refine the projects displayed in the timeline</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="open = !open"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="h-5 w-5 transition-transform" x-bind:class="{ 'rotate-180': !open }"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6" />
                        </svg>
                    </button>
                </div>

                <div x-show="open" x-collapse>
                    <div class="flex items-center gap-3 overflow-x-auto bg-white px-4 py-4">
                        <x-dashboard-filter-dropdown label="Plants" model="plantFilter" :options="$plantOptions->map(
                            fn($plant) => ['value' => $plant['id'], 'label' => $plant['name']],
                        )"
                            :selected="$plantFilter" multiple />

                        <x-dashboard-filter-dropdown label="Status" model="statusFilter" :options="collect($statusOptions)->map(
                            fn($status) => ['value' => $status, 'label' => $status],
                        )"
                            :selected="$statusFilter" multiple />

                        <x-dashboard-filter-dropdown label="Creation Years" model="creationYearFilter" :options="$creationYearOptions->map(fn($year) => ['value' => $year, 'label' => $year])"
                            :selected="$creationYearFilter" multiple />

                        <label class="shrink-0">
                            <span class="sr-only">Currency</span>
                            <select wire:model.live="currency" data-global-loading
                                class="h-11 rounded-lg border-slate-300 bg-white text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="usd">USD ($)</option>
                                <option value="eur">EUR (&euro;)</option>
                            </select>
                        </label>

                        <label class="shrink-0">
                            <span class="sr-only">Cell content</span>
                            <select wire:model.live="cellDisplay" data-global-loading
                                class="h-11 rounded-lg border-slate-300 bg-white text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="combined">Milestone | Value</option>
                                <option value="milestone">Milestone only</option>
                                <option value="value">Value only</option>
                            </select>
                        </label>

                        <button wire:click="toggleOnlyWithMilestones" data-global-loading wire:loading.attr="disabled"
                            wire:target="toggleOnlyWithMilestones" type="button"
                            class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg border px-4 text-sm font-semibold shadow-sm transition hover:-translate-y-px hover:shadow-md disabled:opacity-60
                                {{ $onlyWithMilestones
                                    ? 'border-blue-600 bg-blue-600 text-white hover:bg-blue-500'
                                    : 'border-slate-300 bg-white text-slate-700 hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                @if ($onlyWithMilestones)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M7 12h10m-7 5h4" />
                                @endif
                            </svg>
                            With milestones
                        </button>

                        <x-clear-filters-button method="clearFilters" :active="$plantFilter !== [] || $statusFilter !== [] || $creationYearFilter !== [] || $onlyWithMilestones || $currency !== 'usd' || $cellDisplay !== 'combined'" />
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table-fixed border-separate border-spacing-0"
                    style="min-width: {{ 768 + $timelineYears->count() * 1728 }}px">
                    <thead class="sticky top-0 z-20 shadow-sm">
                        <tr class="bg-indigo-600 text-white">
                            <th rowspan="2"
                                class="sticky left-0 z-30 w-24 border-r border-indigo-500 bg-indigo-700 px-2 py-2 text-center text-xs font-semibold uppercase tracking-wide">
                                AÑO CREACIÓN
                            </th>
                            <th rowspan="2"
                                class="sticky left-24 z-30 w-40 border-r border-indigo-500 bg-indigo-700 px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide">
                                Planta
                            </th>
                            <th rowspan="2"
                                class="sticky left-64 z-30 w-64 border-r border-indigo-500 bg-indigo-700 px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide">
                                Nombre
                            </th>
                            <th rowspan="2"
                                class="sticky left-[32rem] z-30 w-36 border-r border-indigo-500 bg-indigo-700 px-2 py-2 text-right text-xs font-semibold uppercase tracking-wide">
                                Budgeted total
                            </th>
                            <th rowspan="2"
                                class="sticky left-[41rem] z-30 w-28 border-r-2 border-indigo-400 bg-indigo-700 px-2 py-2 text-center text-xs font-semibold uppercase tracking-wide">
                                Status
                            </th>
                            @foreach ($timelineYears as $year)
                                <th colspan="12"
                                    class="border-r-2 border-indigo-300 px-2 py-1.5 text-center text-sm font-bold {{ (int) $year === now()->year ? 'bg-indigo-500' : '' }}">
                                    {{ $year }}
                                </th>
                            @endforeach
                        </tr>
                        <tr class="bg-indigo-500 text-white">
                            @foreach ($timelineYears as $year)
                                @foreach (['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $monthLabel)
                                    <th
                                        class="w-36 border-r border-indigo-400 px-1 py-1.5 text-center text-xs font-semibold
                                        {{ (int) $year === now()->year && $loop->iteration === now()->month ? 'bg-amber-400 text-gray-900' : '' }}
                                        {{ $loop->last ? 'border-r-2 border-indigo-300' : '' }}">
                                        {{ $monthLabel }}
                                    </th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($plannedProjects as $plannedProject)
                            @php
                                $projectFirstYear =
                                    $plannedProject->forecast_start_date?->year ??
                                    ($plannedProject->projectMilestones->min('cycle_year') ?? now()->year);
                                $firstPlannedMilestone = $plannedProject->projectMilestones
                                    ->sortBy(fn($item) => $item->cycle_year * 12 + $item->month)
                                    ->first();
                                $firstPlannedPosition = $firstPlannedMilestone
                                    ? $firstPlannedMilestone->cycle_year * 12 + $firstPlannedMilestone->month
                                    : null;
                                $projectClosed = $plannedProject->projectMilestones->contains(
                                    fn($item) => strtoupper($item->milestone?->code ?? '') === 'CLOSED',
                                );
                                $projectBudget =
                                    (float) ($currency === 'eur'
                                        ? $plannedProject->data_budgeted_euros ?? 0
                                        : $plannedProject->data_budgeted ?? 0);
                                $currencySymbol = $currency === 'eur' ? '€' : '$';
                            @endphp
                            <tr wire:key="planned-project-{{ $plannedProject->id }}"
                                class="group h-10 {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} hover:bg-indigo-50">
                                <td
                                    class="sticky left-0 z-10 w-24 border-b border-r border-gray-200 px-2 py-1.5 text-center text-sm font-medium text-slate-700
                            {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} group-hover:bg-indigo-50">
                                    {{ $plannedProject->created_at?->year }}
                                </td>
                                <td
                                    class="sticky left-24 z-10 w-40 border-b border-r border-gray-200 px-3 py-1.5 text-sm text-slate-700
                            {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} group-hover:bg-indigo-50">
                                    <div class="truncate" title="{{ $plannedProject->company?->company_name }}">
                                        {{ $plannedProject->company?->company_name ?? '—' }}
                                    </div>
                                </td>
                                <td
                                    class="sticky left-64 z-10 w-64 border-b border-r border-gray-200 px-4 py-1.5 align-middle
                            {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} group-hover:bg-indigo-50">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-indigo-100 px-1.5 text-xs font-bold text-indigo-700">
                                            {{ $plannedProject->id }}
                                        </span>
                                        <div class="line-clamp-1 text-sm font-medium text-gray-900"
                                            title="{{ $plannedProject->name }}">
                                            {{ $plannedProject->name }}
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="sticky left-[32rem] z-10 w-36 border-b border-r border-gray-200 px-3 py-1.5 text-right text-sm font-bold text-slate-800
                            {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} group-hover:bg-indigo-50">
                                    {{ $currencySymbol }}{{ number_format($projectBudget, 2) }}
                                </td>
                                <td
                                    class="sticky left-[41rem] z-10 w-28 border-b border-r-2 border-gray-200 px-2 py-1.5 text-center
                            {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} group-hover:bg-indigo-50">
                                    @php
                                        $statusValue = $plannedProject->state?->value ?? '—';
                                        $statusBackground = $plannedProject->state?->softColor() ?? '#F1F5F9';
                                        $statusText = $plannedProject->state?->textColor() ?? '#334155';
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        style="background-color: {{ $statusBackground }}; color: {{ $statusText }};">
                                        {{ $statusValue }}
                                    </span>
                                </td>
                                @foreach ($timelineYears as $year)
                                    @php
                                        $yearItems = $plannedProject->projectMilestones->where('cycle_year', $year);
                                    @endphp
                                    @for ($monthNumber = 1; $monthNumber <= 12; $monthNumber++)
                                        @php
                                            $yearIsAvailable = in_array(
                                                (int) $year,
                                                [$projectFirstYear, $projectFirstYear + 1],
                                                true,
                                            );
                                            $cellPosition = (int) $year * 12 + $monthNumber;
                                            $cellCanCreate =
                                                !$projectClosed &&
                                                $yearIsAvailable &&
                                                ($firstPlannedPosition === null ||
                                                    $cellPosition >= $firstPlannedPosition);
                                        @endphp
                                        <td
                                            class="w-36 border-b border-r border-gray-200 px-0.5 py-0.5 text-center align-middle
                                    {{ $monthNumber === 12 ? 'border-r-2 border-r-indigo-200' : '' }}
                                    {{ !$cellCanCreate ? 'bg-slate-100/80' : '' }}
                                    {{ $yearIsAvailable && (int) $year === now()->year && $monthNumber === now()->month ? 'bg-amber-50' : '' }}">
                                            <div class="flex flex-nowrap justify-center gap-0.5 overflow-hidden">
                                                @foreach ($yearItems->where('month', $monthNumber) as $item)
                                                    @php
                                                        $milestoneValue =
                                                            $projectBudget * ((float) $item->percentage / 100);
                                                        $milestoneText = match ($cellDisplay) {
                                                            'milestone' => $item->milestone->code,
                                                            'value' => $currencySymbol .
                                                                number_format($milestoneValue, 2),
                                                            default => $item->milestone->code .
                                                                ' | ' .
                                                                $currencySymbol .
                                                                number_format($milestoneValue, 2),
                                                        };
                                                    @endphp
                                                    <span wire:key="project-milestone-{{ $item->id }}"
                                                        class="inline-flex shrink-0 items-center overflow-hidden rounded-md text-white shadow-sm ring-1 ring-black/5 transition hover:-translate-y-px hover:shadow"
                                                        style="background-color: {{ $item->milestone->color }}">
                                                        <button type="button"
                                                            wire:click="editMilestone({{ $item->id }})" data-no-global-loading
                                                            class="px-1.5 py-0.5 text-xs font-semibold leading-4 hover:bg-black/10"
                                                            title="Edit {{ $item->milestone->name }}">
                                                            {{ $milestoneText }}
                                                        </button>
                                                        <button type="button"
                                                            wire:click="requestDeleteMilestone({{ $item->id }})" data-no-global-loading
                                                            class="border-l border-white/30 px-1 py-0.5 text-xs leading-4 hover:bg-black/20"
                                                            title="Remove milestone">×</button>
                                                    </span>
                                                @endforeach
                                                @if ($cellCanCreate)
                                                    <button type="button"
                                                        wire:click="openCreateAt({{ $plannedProject->id }}, {{ $year }}, {{ $monthNumber }})" data-no-global-loading
                                                        class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded text-xs font-medium text-slate-300 transition hover:bg-indigo-100 hover:text-indigo-600"
                                                        title="Add milestone to {{ $months[$monthNumber] }} {{ $year }}">+</button>
                                                @endif
                                            </div>
                                        </td>
                                    @endfor
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + $timelineYears->count() * 12 }}"
                                    class="px-5 py-12 text-center text-sm text-gray-500">No project plans found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($plannedProjects->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">{{ $plannedProjects->links() }}</div>
            @endif
        </div>
    </div>

    @if ($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
            wire:click.self="closeForm" x-data x-on:keydown.escape.window="$wire.closeForm()">
            <form wire:submit="saveMilestone"
                class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $editingId ? 'M16.862 4.487 18.55 2.8a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z' : 'M12 4v16m8-8H4' }}" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                {{ $editingId ? 'Edit milestone' : 'Add milestone' }}</h2>
                            <p class="text-xs text-slate-500">
                                {{ $editingId ? 'Update its position in the project timeline.' : 'Place a milestone in the project timeline.' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeForm"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
                        aria-label="Close">✕</button>
                </div>
                <div class="grid gap-4 px-6 py-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Project</label>
                        <select wire:model.live="projectId" data-no-global-loading @disabled($editingId)
                            class="block w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @disabled(!$editingId && $project->is_closed)>
                                    {{ $project->name }}{{ $project->is_closed ? ' (Closed)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('projectId')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Milestone</label>
                        <select wire:model="milestoneId"
                            class="block w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a milestone</option>
                            @foreach ($milestones as $milestone)
                                <option value="{{ $milestone->id }}">{{ $milestone->code }} — {{ $milestone->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('milestoneId')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Month</label>
                        <select wire:model="month"
                            class="block w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a month</option>
                            @foreach ($months as $number => $label)
                                <option value="{{ $number }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('month')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Year</label>
                        <input type="number" wire:model="cycleYear" min="2000" max="2200"
                            class="block w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('cycleYear')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Maximum two consecutive years per project.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Project percentage</label>
                        <div class="relative">
                            <input type="number" wire:model.live.debounce.250ms="percentage" data-no-global-loading min="0"
                                max="100" step="0.01"
                                class="block w-full rounded-lg border-gray-300 pr-10 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span
                                class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm font-semibold text-slate-500">%</span>
                        </div>
                        @error('percentage')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        @php
                            $selectedPlanProject = $projects->firstWhere('id', (int) $projectId);
                            $selectedBudget =
                                (float) ($currency === 'eur'
                                    ? $selectedPlanProject?->data_budgeted_euros ?? 0
                                    : $selectedPlanProject?->data_budgeted ?? 0);
                            $previewValue = $selectedBudget * ((float) $percentage / 100);
                        @endphp
                        <p class="mt-1 text-xs text-slate-500">
                            Calculated value:
                            {{ $currency === 'eur' ? '€' : '$' }}{{ number_format($previewValue, 2) }}
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-4 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" wire:click="closeForm"
                        class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        style="background-color: #ef4444; border-color: #dc2626;"
                        onmouseenter="this.style.backgroundColor='#dc2626'"
                        onmouseleave="this.style.backgroundColor='#ef4444'">
                        Cancel
                    </button>
                    <button type="submit" data-global-loading wire:loading.attr="disabled" wire:target="saveMilestone"
                        class="inline-flex h-10 items-center gap-2 rounded-lg border px-4 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60"
                        style="background-color: #2563eb; border-color: #1d4ed8;"
                        onmouseenter="this.style.backgroundColor='#1d4ed8'"
                        onmouseleave="this.style.backgroundColor='#2563eb'">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $editingId ? 'm5 13 4 4L19 7' : 'M12 4v16m8-8H4' }}" />
                        </svg>
                        <span wire:loading.remove
                            wire:target="saveMilestone">{{ $editingId ? 'Save changes' : 'Create milestone' }}</span>
                        <span wire:loading
                            wire:target="saveMilestone">{{ $editingId ? 'Saving...' : 'Creating...' }}</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if ($pendingDeleteId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
            wire:click.self="cancelDelete" x-data x-on:keydown.escape.window="$wire.cancelDelete()">
            <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                <div class="px-6 pb-5 pt-6 text-center">
                    <span
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v4m0 4h.01M10.3 3.7 2.8 17a2 2 0 0 0 1.74 3h14.92A2 2 0 0 0 21.2 17L13.7 3.7a2 2 0 0 0-3.4 0Z" />
                        </svg>
                    </span>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">Remove milestone?</h2>
                    <p class="mt-2 text-sm text-slate-600">This milestone will be removed from the project timeline.
                    </p>
                    <p class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-800">
                        {{ $pendingDeleteLabel }}</p>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" wire:click="cancelDelete"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button" wire:click="confirmDeleteMilestone" data-global-loading wire:loading.attr="disabled"
                        wire:target="confirmDeleteMilestone"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 disabled:opacity-60">
                        Remove milestone
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
