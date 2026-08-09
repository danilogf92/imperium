            @php
                $fixedWidths = [
                    'forecast_year' => 76, 'plant' => 112, 'pda_code' => 112, 'name' => 200,
                    'budgeted' => 120, 'status' => 96, 'actual_week' => 160, 'next_week' => 160,
                ];
                $visibleFixedColumns = collect(array_keys($fixedColumnOptions))
                    ->filter(fn($column) => in_array($column, $visibleColumns, true))->values();
                $fixedOffsets = [];
                $fixedWidth = 0;
                foreach ($visibleFixedColumns as $column) {
                    $fixedOffsets[$column] = $fixedWidth;
                    $fixedWidth += $fixedWidths[$column];
                }
            @endphp
            <div class="overflow-x-auto">
                <table class="table-fixed border-separate border-spacing-0"
                    style="min-width: {{ $fixedWidth + $timelineYears->count() * 2304 }}px">
                    <colgroup>
                        @foreach ($visibleFixedColumns as $column)
                            <col style="width: {{ $fixedWidths[$column] }}px; min-width: {{ $fixedWidths[$column] }}px; max-width: {{ $fixedWidths[$column] }}px">
                        @endforeach
                        @foreach ($timelineYears as $year)
                            @for ($monthNumber = 1; $monthNumber <= 12; $monthNumber++)
                                <col style="width: 192px; min-width: 192px; max-width: 192px">
                            @endfor
                        @endforeach
                    </colgroup>
                    <thead class="sticky top-0 z-20 shadow-sm">
                        <tr class="bg-indigo-600 text-white">
                            @foreach ($visibleFixedColumns->filter(fn($column) => !in_array($column, ['actual_week', 'next_week'], true)) as $column)
                                <th rowspan="2" class="sticky z-30 border-r border-indigo-500 bg-indigo-700 px-2 py-2 text-[10px] font-semibold uppercase tracking-wide"
                                    style="left: {{ $fixedOffsets[$column] }}px; width: {{ $fixedWidths[$column] }}px; text-align: {{ in_array($column, ['budgeted']) ? 'right' : (in_array($column, ['forecast_year', 'status']) ? 'center' : 'left') }}">
                                    {{ __($fixedColumnOptions[$column]) }}
                                </th>
                            @endforeach
                            @foreach ($activityWeeks as $week)
                                @php $activityColumn = $week['offset'] === 0 ? 'actual_week' : 'next_week'; @endphp
                                @if (in_array($activityColumn, $visibleColumns, true))
                                    <th rowspan="2"
                                    class="sticky z-30 border-r border-indigo-500 bg-cyan-700 px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-wide"
                                    style="left: {{ $fixedOffsets[$activityColumn] }}px; width: {{ $fixedWidths[$activityColumn] }}px">
                                    {{ $week['offset'] === 0 ? 'Actual Week' : 'Next Week' }}
                                    <span class="mt-1 block font-normal text-cyan-100">
                                        W{{ str_pad($week['week'], 2, '0', STR_PAD_LEFT) }} · {{ $week['year'] }}
                                    </span>
                                    </th>
                                @endif
                            @endforeach
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
                                        class="w-48 border-r border-indigo-400 px-1 py-1.5 text-center text-xs font-semibold
                                        {{ (int) $year === now()->year && $loop->iteration === now()->month ? '!border-x-2 !border-x-cyan-200 !bg-cyan-500 text-white' : '' }}
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
                                class="group min-h-10 {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} hover:bg-indigo-50">
                                @if (in_array('forecast_year', $visibleColumns, true))
                                <td style="left: {{ $fixedOffsets['forecast_year'] }}px"
                                    class="sticky z-10 w-24 border-b border-r border-gray-200 px-2 py-1.5 text-center text-xs font-medium text-slate-700
                            {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} group-hover:bg-indigo-50">
                                    {{ $plannedProject->forecast_start_date?->year }}
                                </td>
                                @endif
                                @if (in_array('plant', $visibleColumns, true))
                                <td style="left: {{ $fixedOffsets['plant'] }}px"
                                    class="sticky z-10 w-40 border-b border-r border-gray-200 px-2 py-1.5 text-xs text-slate-700
                            {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} group-hover:bg-indigo-50">
                                    <div class="truncate" title="{{ $plannedProject->company?->company_name }}">
                                        {{ $plannedProject->company?->company_name ?? '—' }}
                                    </div>
                                </td>
                                @endif
                                @if (in_array('pda_code', $visibleColumns, true))
                                <td style="left: {{ $fixedOffsets['pda_code'] }}px"
                                    class="sticky z-10 w-40 border-b border-r border-gray-200 px-2 py-1.5 text-xs font-semibold text-slate-700
                            {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} group-hover:bg-indigo-50">
                                    <div class="truncate" title="{{ $plannedProject->pda_code }}">
                                        {{ $plannedProject->pda_code ?? '—' }}
                                    </div>
                                </td>
                                @endif
                                @if (in_array('name', $visibleColumns, true))
                                <td style="left: {{ $fixedOffsets['name'] }}px"
                                    class="sticky z-10 w-64 border-b border-r border-gray-200 px-4 py-1.5 align-middle
                            {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} group-hover:bg-indigo-50">
                                    <div class="flex items-center gap-2">
                                        <div class="line-clamp-2 text-xs font-medium leading-tight text-gray-900"
                                            title="{{ $plannedProject->name }}">
                                            {{ $plannedProject->name }}
                                        </div>
                                    </div>
                                </td>
                                @endif
                                @if (in_array('budgeted', $visibleColumns, true))
                                <td style="left: {{ $fixedOffsets['budgeted'] }}px"
                                    class="sticky z-10 w-36 border-b border-r border-gray-200 px-2 py-1.5 text-right text-xs font-bold text-slate-800
                            {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} group-hover:bg-indigo-50">
                                    {{ $currencySymbol }}{{ number_format($projectBudget, 2) }}
                                </td>
                                @endif
                                @if (in_array('status', $visibleColumns, true))
                                <td style="left: {{ $fixedOffsets['status'] }}px"
                                    class="sticky z-10 w-28 border-b border-r-2 border-gray-200 px-2 py-1.5 text-center
                            {{ $loop->even ? 'bg-slate-50' : 'bg-white' }} group-hover:bg-indigo-50">
                                    @php
                                        $statusValue = $plannedProject->state?->value ?? '—';
                                        $statusBackground = $plannedProject->state?->softColor() ?? '#F1F5F9';
                                        $statusText = $plannedProject->state?->textColor() ?? '#334155';
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-1 text-[10px] font-semibold"
                                        style="background-color: {{ $statusBackground }}; color: {{ $statusText }};">
                                        {{ $statusValue }}
                                    </span>
                                </td>
                                @endif
                                @foreach ($activityWeeks as $week)
                                    @php $activityColumn = $week['offset'] === 0 ? 'actual_week' : 'next_week'; @endphp
                                    @if (in_array($activityColumn, $visibleColumns, true))
                                    @php
                                        $weeklyActivitiesForWeek = $plannedProject->weeklyActivities
                                            ->where('week_year', $week['year'])->where('week_number', $week['week'])->values();
                                        $weeklyActivity = $weeklyActivitiesForWeek->first();
                                        $weeklyActivityTooltip = $weeklyActivitiesForWeek
                                            ->map(fn($activity, $index) => ($index + 1).'. '.$activity->activity)->implode("\n");
                                    @endphp
                                    <td class="group/activity sticky z-10 border-b border-r border-cyan-200 bg-cyan-50 px-1.5 py-1.5 text-center"
                                        style="left: {{ $fixedOffsets[$activityColumn] }}px">
                                        <div class="relative flex min-h-8 items-center justify-center">
                                            <button type="button"
                                                wire:click="openWeeklyActivity({{ $plannedProject->id }}, {{ $week['offset'] }})"
                                                data-no-global-loading
                                                title="{{ $weeklyActivityTooltip ?: __('Add activity') }}"
                                                class="inline-flex max-w-full cursor-pointer items-center gap-1.5 rounded-lg border border-cyan-500 bg-cyan-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-px hover:bg-cyan-500 hover:shadow-md">
                                                <span class="text-base leading-none">+</span>
                                                <span class="truncate">{{ $weeklyActivity ? $weeklyActivitiesForWeek->count().' activities' : 'Add activity' }}</span>
                                            </button>
                                            @if ($weeklyActivity)
                                                <div class="pointer-events-none absolute bottom-full left-1/2 z-50 mb-2 hidden w-72 -translate-x-1/2 rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-left text-xs font-normal leading-relaxed text-white shadow-xl group-hover/activity:block">
                                                    <div class="space-y-2">
                                                        @foreach ($weeklyActivitiesForWeek as $activity)
                                                            <p>{{ $loop->iteration }}. {{ $activity->activity }}</p>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    @endif
                                @endforeach
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
                                            class="w-48 border-b border-r border-gray-200 px-1 py-1 text-center align-middle
                                    {{ $monthNumber === 12 ? 'border-r-2 border-r-indigo-200' : '' }}
                                    {{ !$cellCanCreate ? 'bg-slate-100/80' : '' }}
                                    {{ (int) $year === now()->year && $monthNumber === now()->month ? '!border-x-2 !border-x-cyan-400 !bg-cyan-100' : '' }}">
                                            <div class="flex min-h-6 flex-wrap content-center justify-center gap-1 overflow-visible">
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
                                                        style="background-color: {{ $item->milestone->view_color ?: $item->milestone->color }}; color: {{ $item->milestone->viewTextColor() }}">
                                                        <button type="button"
                                                            wire:click="editMilestone({{ $item->id }})"
                                                            data-no-global-loading
                                                            class="px-1.5 py-0.5 text-xs font-semibold leading-4 hover:bg-black/10"
                                                            title="Edit {{ $item->milestone->name }}">
                                                            {{ $milestoneText }}
                                                        </button>
                                                        <button type="button"
                                                            wire:click="requestDeleteMilestone({{ $item->id }})"
                                                            data-no-global-loading
                                                            class="border-l border-white/30 px-1 py-0.5 text-xs leading-4 hover:bg-black/20"
                                                            title="Remove milestone">×</button>
                                                    </span>
                                                @endforeach
                                                @if ($cellCanCreate)
                                                    <button type="button"
                                                        wire:click="openCreateAt({{ $plannedProject->id }}, {{ $year }}, {{ $monthNumber }})"
                                                        data-no-global-loading
                                                        class="inline-flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-full border border-indigo-600 bg-indigo-600 text-base font-bold leading-none text-white shadow-sm transition hover:-translate-y-px hover:bg-indigo-500 hover:shadow-md"
                                                        title="Add milestone to {{ $months[$monthNumber] }} {{ $year }}">+</button>
                                                @endif
                                            </div>
                                        </td>
                                    @endfor
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $visibleFixedColumns->count() + $timelineYears->count() * 12 }}"
                                    class="px-5 py-12 text-center text-sm text-gray-500">No project plans found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($plannedProjects->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">{{ $plannedProjects->links() }}</div>
            @endif
