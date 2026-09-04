            @php
                $fixedWidths = [
                    'forecast_year' => 76,
                    'plant' => 112,
                    'pda_code' => 112,
                    'name' => 200,
                    'budgeted' => 120,
                    'status' => 96,
                    'actual_week' => 160,
                    'next_week' => 160,
                ];
                $visibleFixedColumns = collect(array_keys($fixedColumnOptions))
                    ->filter(fn($column) => in_array($column, $visibleColumns, true))
                    ->values();
                $fixedOffsets = [];
                $fixedWidth = 0;
                foreach ($visibleFixedColumns as $column) {
                    $fixedOffsets[$column] = $fixedWidth;
                    $fixedWidth += $fixedWidths[$column];
                }
            @endphp
            <div class="planification-table-scroll unified-table-scroll overflow-x-auto overscroll-x-contain">
                <table class="unified-data-table cursor-pointer"
                    style="min-width: {{ $fixedWidth + $timelineYears->count() * 2304 }}px">
                    <colgroup>
                        @foreach ($visibleFixedColumns as $column)
                            <col
                                style="width: {{ $fixedWidths[$column] }}px; min-width: {{ $fixedWidths[$column] }}px; max-width: {{ $fixedWidths[$column] }}px">
                        @endforeach
                        @foreach ($timelineYears as $year)
                            @for ($monthNumber = 1; $monthNumber <= 12; $monthNumber++)
                                <col style="width: 192px; min-width: 192px; max-width: 192px">
                            @endfor
                        @endforeach
                    </colgroup>
                    <thead class="sticky top-0 z-20 shadow-sm">
                        <tr class="bg-[#7DB9F1] text-slate-900">
                            @foreach ($visibleFixedColumns->filter(fn($column) => !in_array($column, ['actual_week', 'next_week'], true)) as $column)
                                <th rowspan="2"
                                    class="sticky z-30 border-r border-blue-300 bg-[#7DB9F1] px-2 py-2 text-[10px] font-bold uppercase tracking-wide"
                                    style="left: {{ $fixedOffsets[$column] }}px; width: {{ $fixedWidths[$column] }}px; text-align: {{ in_array($column, ['budgeted']) ? 'right' : (in_array($column, ['forecast_year', 'status']) ? 'center' : 'left') }}">
                                    {{ __($fixedColumnOptions[$column]) }}
                                </th>
                            @endforeach
                            @foreach ($activityWeeks as $week)
                                @php $activityColumn = $week['offset'] === 0 ? 'actual_week' : 'next_week'; @endphp
                                @if (in_array($activityColumn, $visibleColumns, true))
                                    <th rowspan="2"
                                        class="sticky z-30 border-r border-blue-300 bg-[#7DB9F1] px-2 py-2 text-center text-[10px] font-bold uppercase tracking-wide"
                                        style="left: {{ $fixedOffsets[$activityColumn] }}px; width: {{ $fixedWidths[$activityColumn] }}px">
                                        {{ $week['offset'] === 0 ? 'Actual Week' : 'Next Week' }}
                                        <span class="mt-1 block font-medium text-blue-900">
                                            W{{ str_pad($week['week'], 2, '0', STR_PAD_LEFT) }} · {{ $week['year'] }}
                                        </span>
                                    </th>
                                @endif
                            @endforeach
                            @foreach ($timelineYears as $year)
                                <th colspan="12"
                                    class="border-r-2 border-blue-300 px-2 py-1.5 text-center text-sm font-bold {{ (int) $year === now()->year ? 'bg-blue-300' : '' }}">
                                    {{ $year }}
                                </th>
                            @endforeach
                        </tr>
                        <tr class="bg-blue-200 text-slate-900">
                            @foreach ($timelineYears as $year)
                                @foreach (['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $monthLabel)
                                    <th
                                        class="w-48 border-r border-blue-300 px-1 py-1.5 text-center text-xs font-semibold
                                        {{ (int) $year === now()->year && $loop->iteration === now()->month ? '!border-x-2 !border-x-blue-500 !bg-[#7DB9F1] text-slate-900' : '' }}
                                        {{ $loop->last ? 'border-r-2 border-blue-300' : '' }}">
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
                                $canUpdateProject = in_array($plannedProject->company_id, $editableCompanyIds, true);
                                $canDeleteProject = in_array($plannedProject->company_id, $deletableCompanyIds, true);
                                $currencySymbol = $currency === 'eur' ? '€' : '$';
                            @endphp
                            <tr wire:key="planned-project-{{ $plannedProject->id }}" class="group min-h-10 ">
                                @if (in_array('forecast_year', $visibleColumns, true))
                                    <td style="left: {{ $fixedOffsets['forecast_year'] }}px"
                                        class="planification-sticky-cell sticky z-10 w-24 border-b border-r border-gray-200 px-2 py-1.5 text-center text-xs font-medium text-slate-700">
                                        {{ $plannedProject->forecast_start_date?->year }}
                                    </td>
                                @endif
                                @if (in_array('plant', $visibleColumns, true))
                                    <td style="left: {{ $fixedOffsets['plant'] }}px"
                                        class="planification-sticky-cell sticky z-10 w-40 border-b border-r border-gray-200 px-2 py-1.5 text-xs text-slate-700">
                                        <div class="truncate" title="{{ $plannedProject->company?->company_name }}">
                                            {{ $plannedProject->company?->company_name ?? '—' }}
                                        </div>
                                    </td>
                                @endif
                                @if (in_array('pda_code', $visibleColumns, true))
                                    <td style="left: {{ $fixedOffsets['pda_code'] }}px"
                                        class="planification-sticky-cell sticky z-10 w-40 border-b border-r border-gray-200 px-2 py-1.5 text-xs font-semibold text-slate-700">
                                        <div class="truncate" title="{{ $plannedProject->pda_code }}">
                                            {{ $plannedProject->pda_code ?? '—' }}
                                        </div>
                                    </td>
                                @endif
                                @if (in_array('name', $visibleColumns, true))
                                    <td style="left: {{ $fixedOffsets['name'] }}px"
                                        class="planification-sticky-cell sticky z-10 w-64 border-b border-r border-gray-200 px-4 py-1.5 align-middle">
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
                                        class="planification-sticky-cell sticky z-10 w-36 border-b border-r border-gray-200 px-2 py-1.5 text-right text-xs font-bold text-slate-800">
                                        {{ $currencySymbol }}{{ number_format($projectBudget, 2) }}
                                    </td>
                                @endif
                                @if (in_array('status', $visibleColumns, true))
                                    <td style="left: {{ $fixedOffsets['status'] }}px"
                                        class="planification-sticky-cell sticky z-10 w-28 border-b border-r-2 border-gray-200 px-2 py-1.5 text-center">
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
                                                ->where('week_year', $week['year'])
                                                ->where('week_number', $week['week'])
                                                ->values();
                                            $weeklyActivity = $weeklyActivitiesForWeek->first();
                                            $weekDeadline = \Carbon\CarbonImmutable::now()
                                                ->setISODate($week['year'], $week['week'])
                                                ->endOfWeek();
                                            $weekExpired = now()->isAfter($weekDeadline);
                                            $allActivitiesExecuted =
                                                $weeklyActivitiesForWeek->isNotEmpty() &&
                                                $weeklyActivitiesForWeek->every(
                                                    fn($activity) => filled($activity->executed_at),
                                                );
                                        @endphp
                                        <td class="sticky z-10 border-b border-r border-cyan-200 bg-cyan-50 px-1.5 py-1.5 text-center"
                                            style="left: {{ $fixedOffsets[$activityColumn] }}px">
                                            <div class="relative flex min-h-8 items-center justify-center gap-1"
                                                @click.outside="tooltipOpen = false" x-data="{
                                                    tooltipOpen: false,
                                                    tooltipStyle: '',
                                                    showTooltip(event) {
                                                        const rect = event.currentTarget.getBoundingClientRect();
                                                        const width = Math.min(320, window.innerWidth - 24);
                                                        const left = Math.max(12, Math.min(rect.left + rect.width / 2 - width / 2, window.innerWidth - width - 12));
                                                        const showBelow = rect.top < 180;
                                                        const top = showBelow ? rect.bottom + 10 : rect.top - 10;
                                                        this.tooltipStyle = `position:fixed;width:${width}px;left:${left}px;${showBelow ? `top:${top}px` : `top:${top}px;transform:translateY(-100%)`}`;
                                                        this.tooltipOpen = true;
                                                    }
                                                }">
                                                <button type="button"
                                                    wire:click="openWeeklyActivity({{ $plannedProject->id }}, {{ $week['offset'] }})"
                                                    data-no-global-loading
                                                    @if ($weeklyActivity) x-on:mouseenter="showTooltip($event)"
                                                    x-on:mouseleave="tooltipOpen = false"
                                                    x-on:focus="showTooltip($event)"
                                                    x-on:blur="tooltipOpen = false"
                                                    aria-describedby="activity-tooltip-{{ $plannedProject->id }}-{{ $week['offset'] }}"
                                                @else
                                                    title="{{ __($canUpdateProject ? 'Add activity' : 'View activities') }}" @endif
                                                    class="inline-flex max-w-full cursor-pointer items-center gap-1.5 rounded-lg border border-cyan-500 bg-cyan-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-px hover:bg-cyan-500 hover:shadow-md">
                                                    @if ($weeklyActivity)
                                                        <span
                                                            class="inline-flex h-5 w-5 items-center justify-center rounded-full font-bold
                                                        {{ $allActivitiesExecuted ? 'bg-green-500 text-white' : ($weekExpired ? 'bg-red-500 text-white' : 'bg-amber-100 text-amber-700') }}">
                                                            {{ $allActivitiesExecuted ? '✓' : ($weekExpired ? '×' : '○') }}
                                                        </span>
                                                    @endif
                                                    <span class="text-base leading-none">{{ $canUpdateProject ? '+' : 'i' }}</span>
                                                    <span
                                                        class="truncate">{{ $weeklyActivity ? $weeklyActivitiesForWeek->count() . ' activities' : ($canUpdateProject ? 'Add activity' : 'View activities') }}</span>
                                                </button>
                                                @if ($weeklyActivity)
                                                    <button type="button" data-no-global-loading
                                                        x-on:click.stop="tooltipOpen ? tooltipOpen = false : showTooltip($event)"
                                                        class="planification-activity-info" aria-label="Show activities"
                                                        aria-controls="activity-tooltip-{{ $plannedProject->id }}-{{ $week['offset'] }}">i</button>
                                                    <template x-teleport="body">
                                                        <div x-cloak x-show="tooltipOpen" x-transition.opacity
                                                            id="activity-tooltip-{{ $plannedProject->id }}-{{ $week['offset'] }}"
                                                            role="tooltip" :style="tooltipStyle"
                                                            class="planification-activity-tooltip">
                                                            <div
                                                                class="flex items-center justify-between border-b border-cyan-400/20 pb-2">
                                                                <span
                                                                    class="font-bold text-cyan-100">{{ $week['offset'] === 0 ? 'Actual week' : 'Next week' }}</span>
                                                                <span
                                                                    class="rounded-full bg-cyan-400/15 px-2 py-0.5 text-[10px] font-semibold text-cyan-100">{{ $weeklyActivitiesForWeek->count() }}
                                                                    {{ $weeklyActivitiesForWeek->count() === 1 ? 'activity' : 'activities' }}</span>
                                                            </div>
                                                            <ol class="mt-2 space-y-2">
                                                                @foreach ($weeklyActivitiesForWeek as $activity)
                                                                    <li class="flex items-start gap-2">
                                                                        <span
                                                                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-cyan-400/20 text-[10px] font-bold text-cyan-100">{{ $loop->iteration }}</span>
                                                                        <span
                                                                            class="min-w-0 flex-1 whitespace-pre-line text-slate-100">{{ $activity->activity }}</span>
                                                                        @if (filled($activity->executed_at))
                                                                            <span
                                                                                class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-400/20 px-2 py-0.5 text-[10px] font-bold text-emerald-200">
                                                                                <span aria-hidden="true">✓</span>
                                                                                Executed
                                                                            </span>
                                                                        @else
                                                                            <span
                                                                                class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $weekExpired ? 'bg-red-400/20 text-red-200' : 'bg-amber-300/20 text-amber-100' }}">
                                                                                <span
                                                                                    aria-hidden="true">{{ $weekExpired ? '×' : '○' }}</span>
                                                                                Not executed
                                                                            </span>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ol>
                                                        </div>
                                                    </template>
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
                                    {{ $monthNumber === 12 ? 'border-r-2 border-r-blue-200' : '' }}
                                    {{ !$cellCanCreate ? 'bg-slate-100/80' : '' }}
                                    {{ (int) $year === now()->year && $monthNumber === now()->month ? '!border-x-2 !border-x-cyan-400 !bg-cyan-100' : '' }}">
                                            <div
                                                class="flex min-h-6 flex-wrap content-center justify-center gap-1 overflow-visible">
                                                @foreach ($yearItems->where('month', $monthNumber) as $item)
                                                    @php
                                                        $milestoneValue =
                                                            $projectBudget * ((float) $item->percentage / 100);
                                                        $formattedMilestoneValue = \App\Support\MoneyValueFormatter::compact(
                                                            $milestoneValue,
                                                            $currencySymbol,
                                                        );
                                                        $milestoneText = match ($cellDisplay) {
                                                            'milestone' => $item->milestone->code,
                                                            'value' => $formattedMilestoneValue,
                                                            default => $item->milestone->code .
                                                                ' | ' .
                                                                $formattedMilestoneValue,
                                                        };
                                                        $milestoneDeadline = \Carbon\CarbonImmutable::create(
                                                            (int) $item->cycle_year,
                                                            (int) $item->month,
                                                            1,
                                                        )->endOfMonth();
                                                        $milestoneExpired = now()->isAfter($milestoneDeadline);
                                                        $milestoneExecuted = filled($item->executed_at);
                                                        $milestoneIsFuture = \Carbon\CarbonImmutable::create(
                                                            (int) $item->cycle_year,
                                                            (int) $item->month,
                                                            1,
                                                        )
                                                            ->startOfMonth()
                                                            ->isAfter(now()->startOfMonth());
                                                    @endphp
                                                    <span wire:key="project-milestone-{{ $item->id }}"
                                                        class="inline-flex shrink-0 items-center overflow-hidden rounded-md text-white shadow-sm ring-1 ring-black/5 transition hover:-translate-y-px hover:shadow"
                                                        style="background-color: {{ $item->milestone->view_color ?: $item->milestone->color }}; color: {{ $item->milestone->viewTextColor() }}">
                                                        @if (!$milestoneIsFuture)
                                                            <span
                                                                title="{{ $milestoneExecuted ? 'Executed' : ($milestoneExpired ? 'Not executed' : 'Pending execution') }}"
                                                                class="m-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold leading-none shadow-sm ring-1 ring-white/40
                                                                    {{ $milestoneExecuted ? 'bg-green-600 text-white' : ($milestoneExpired ? 'bg-red-600 text-white' : 'bg-white/20') }}">
                                                                {{ $milestoneExecuted ? '✓' : ($milestoneExpired ? '×' : '○') }}
                                                            </span>
                                                        @endif
                                                        @if ($canUpdateProject)
                                                            <button type="button" wire:click="editMilestone({{ $item->id }})"
                                                                data-no-global-loading class="px-1.5 py-0.5 text-xs font-semibold leading-4 hover:bg-black/10"
                                                                title="Edit {{ $item->milestone->name }}">{{ $milestoneText }}</button>
                                                        @else
                                                            <span class="px-1.5 py-0.5 text-xs font-semibold leading-4">{{ $milestoneText }}</span>
                                                        @endif
                                                        @if ($canDeleteProject)
                                                            <button type="button" wire:click="requestDeleteMilestone({{ $item->id }})"
                                                                data-no-global-loading class="border-l border-white/30 px-1 py-0.5 text-xs leading-4 hover:bg-black/20"
                                                                title="Remove milestone">×</button>
                                                        @endif
                                                    </span>
                                                @endforeach
                                                @if ($cellCanCreate && $canUpdateProject)
                                                    <button type="button"
                                                        wire:click="openCreateAt({{ $plannedProject->id }}, {{ $year }}, {{ $monthNumber }})"
                                                        data-no-global-loading
                                                        class="inline-flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-full border border-blue-500 bg-[#7DB9F1] text-base font-bold leading-none text-white shadow-sm transition hover:-translate-y-px hover:bg-blue-400 hover:shadow-md"
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
