<div class="activities-dashboard dashboard-page-shell">
    <style>
        @media (max-width: 639px) {
            .activities-dashboard .dashboard-page-content {
                gap: 1rem;
            }

            .activities-dashboard .activities-chart-grid {
                gap: 1rem;
            }

            .activities-dashboard .dashboard-chart-card {
                height: 27rem !important;
            }

            .activities-dashboard .dashboard-chart-card.activities-risk-chart {
                height: 31rem !important;
            }

            .activities-dashboard .dashboard-metrics-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .activities-dashboard .dashboard-metrics-grid article {
                min-height: 7rem;
            }
        }
    </style>
    <div class="dashboard-page-content space-y-6">
        <header class="module-accent-line dashboard-panel relative overflow-hidden">
            <div class="flex flex-col gap-5 bg-white px-5 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#7DB9F1] text-white shadow-sm ring-4 ring-blue-50">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 7l2 2 4-4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-600">Planification control
                            center</p>
                        {{-- <h1 class="text-2xl font-bold text-sky-950">Activities & milestones dashboard</h1> --}}
                        <h1 class="text-2xl font-bold text-blue-600">Activities & milestones dashboard</h1>
                        <p class="mt-1 text-sm text-slate-600">Identify delayed commitments and projects that require
                            corrective action.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-lg border border-orange-200 bg-orange-50 px-3 py-2 text-xs font-bold text-orange-700">{{ $metrics['overdue'] + $milestoneMetrics['overdue'] }}
                        overdue commitments</span>
                    {{-- <a href="{{ route('planification') }}" wire:navigate
                        class="inline-flex h-10 items-center justify-center rounded-lg
           border border-[#6AA9E3] bg-[#7DB9F1] px-4
           text-sm font-bold text-white shadow-sm
           transition duration-150
           hover:-translate-y-px hover:border-[#5599D8] hover:bg-[#5FA3E0]
           hover:text-white hover:shadow-md
           focus:outline-none focus:ring-2 focus:ring-[#7DB9F1]/40 focus:ring-offset-2">
                        Open planification
                    </a> --}}

                    <x-ui-button :href="route('planification')" :text="__('Open planification')" icon="external-link" color="#7DB9F1"
                        hover-opacity="0.80" text-color="#FFFFFF" wire:navigate />
                </div>
            </div>
        </header>

        <section class="dashboard-metrics-grid">
            @foreach ([
        ['label' => 'Total activities', 'value' => $metrics['total'], 'accent' => 'border-blue-200', 'icon' => 'bg-blue-100 text-blue-700', 'path' => 'M9 5h6M9 9h6m-6 4h4m-7 8h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z'],
        ['label' => 'Completed', 'value' => $metrics['completed'], 'accent' => 'border-emerald-200', 'icon' => 'bg-emerald-100 text-emerald-700', 'path' => 'm5 12 4 4L19 6'],
        ['label' => 'Overdue', 'value' => $metrics['overdue'], 'accent' => 'border-red-200', 'icon' => 'bg-red-100 text-red-700', 'path' => 'M12 8v5m0 3h.01M10.3 3.9 2.4 18a2 2 0 0 0 1.75 3h15.7a2 2 0 0 0 1.75-3L13.7 3.9a2 2 0 0 0-3.4 0Z'],
        ['label' => 'On time', 'value' => $metrics['pending'], 'accent' => 'border-cyan-200', 'icon' => 'bg-cyan-100 text-cyan-700', 'path' => 'M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['label' => 'Completion', 'value' => $metrics['completion'] . '%', 'accent' => 'border-violet-200', 'icon' => 'bg-violet-100 text-violet-700', 'path' => 'M4 19V9m6 10V5m6 14v-7m4 7H2'],
    ] as $metric)
                <article class="{{ $metric['accent'] }} rounded-xl border bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $metric['label'] }}
                            </p>
                            <p class="mt-2 text-3xl font-black text-sky-950">{{ $metric['value'] }}</p>
                        </div>
                        <span class="{{ $metric['icon'] }} flex h-10 w-10 items-center justify-center rounded-lg">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $metric['path'] }}" />
                            </svg>
                        </span>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="dashboard-metrics-grid">
            @foreach ([['label' => 'Total milestones', 'value' => $milestoneMetrics['total'], 'color' => 'text-blue-800', 'icon' => 'bg-blue-100 text-blue-700'], ['label' => 'Completed milestones', 'value' => $milestoneMetrics['completed'], 'color' => 'text-emerald-700', 'icon' => 'bg-emerald-100 text-emerald-700'], ['label' => 'Overdue milestones', 'value' => $milestoneMetrics['overdue'], 'color' => 'text-red-700', 'icon' => 'bg-red-100 text-red-700'], ['label' => 'Upcoming milestones', 'value' => $milestoneMetrics['pending'], 'color' => 'text-cyan-700', 'icon' => 'bg-cyan-100 text-cyan-700'], ['label' => 'Milestone completion', 'value' => $milestoneMetrics['completion'] . '%', 'color' => 'text-violet-700', 'icon' => 'bg-violet-100 text-violet-700']] as $metric)
                <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $metric['label'] }}
                            </p>
                            <p class="{{ $metric['color'] }} mt-2 text-3xl font-black">{{ $metric['value'] }}</p>
                        </div>
                        <span class="{{ $metric['icon'] }} flex h-10 w-10 items-center justify-center rounded-lg">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3 4 7v6c0 4.5 3.2 7.4 8 8 4.8-.6 8-3.5 8-8V7l-8-4Zm-3 9 2 2 4-4" />
                            </svg>
                        </span>
                    </div>
                </article>
            @endforeach
        </section>

        @if ($metrics['total'] > 0 || $milestoneMetrics['total'] > 0)
            <section class="rounded-xl border border-orange-200 bg-orange-50 p-4 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Management focus</p>
                        <div class="mt-3 space-y-3">
                            @forelse ($topOverdueActivities->groupBy(fn ($activity) => $activity->planned_month->format('Y-m')) as $monthActivities)
                                <p class="text-xs font-bold uppercase tracking-wide text-blue-700">
                                    {{ $monthActivities->first()->planned_month->translatedFormat('F Y') }}
                                </p>
                                @foreach ($monthActivities as $activity)
                                    <div class="rounded-lg border border-orange-200 bg-white p-3 shadow-sm">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="whitespace-pre-line text-sm font-semibold text-slate-800">
                                                    {{ $activity->activity }}</p>
                                                <a href="{{ route('projects.dashboard', $activity->project) }}"
                                                    class="mt-1 block text-xs font-bold text-sky-700 hover:text-orange-700">
                                                    {{ $activity->project->name }}@if ($activity->project->pda_code)
                                                        · PDA {{ $activity->project->pda_code }}
                                                    @endif
                                                </a>
                                            </div>
                                            <span
                                                class="shrink-0 rounded-full bg-orange-100 px-2.5 py-1 text-xs font-bold text-blue-800">
                                                {{ $activity->months_overdue }}
                                                {{ Str::plural('month', $activity->months_overdue) }} overdue
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            @empty
                                <p class="text-sm font-semibold text-sky-800">There are no overdue activities.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            <section class="activities-chart-grid grid gap-5 xl:grid-cols-2">
                <x-dashboard-chart-card class="activities-health-chart" title="Activity health"
                    filename="activity-health" subtitle="Completed, overdue and upcoming activities" height="27rem">
                    @if (array_sum($statusChart['series']) > 0)
                        <x-dashboard-apex-chart :options="$statusChart"
                            chart-key="activities-status-{{ md5(json_encode($statusChart)) }}" />
                    @else
                        <div class="flex h-full flex-col items-center justify-center gap-2 text-center text-slate-500">
                            <span
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl">0</span>
                            <p class="text-sm font-semibold">No activities available</p>
                        </div>
                    @endif
                    <x-slot name="footer">A growing red segment signals that planned work is not being closed on
                        time.</x-slot>
                </x-dashboard-chart-card>

                <x-dashboard-chart-card class="activities-health-chart" title="Milestone health"
                    filename="milestone-health" subtitle="Completed, overdue and upcoming milestones" height="27rem">
                    @if (array_sum($milestoneStatusChart['series']) > 0)
                        <x-dashboard-apex-chart :options="$milestoneStatusChart"
                            chart-key="milestones-status-{{ md5(json_encode($milestoneStatusChart)) }}" />
                    @else
                        <div class="flex h-full flex-col items-center justify-center gap-2 text-center text-slate-500">
                            <span
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl">0</span>
                            <p class="text-sm font-semibold">No milestones available</p>
                        </div>
                    @endif
                    <x-slot name="footer">Overdue milestones can affect the project sequence and should be reviewed
                        before routine activities.</x-slot>
                </x-dashboard-chart-card>

                <x-dashboard-chart-card class="activities-risk-chart xl:col-span-2"
                    title="Projects requiring corrective action" filename="overdue-commitments-by-project"
                    subtitle="Combined overdue activities and milestones, ranked by operational exposure"
                    height="32rem">
                    <x-dashboard-apex-chart :options="$riskProjectChart"
                        chart-key="activities-risk-projects-{{ md5(json_encode($riskProjectChart)) }}" />
                    <x-slot name="footer">Projects at the top concentrate the largest overdue workload and should be
                        reviewed first.</x-slot>
                </x-dashboard-chart-card>

                <x-dashboard-chart-card title="Eight-week execution trend" filename="weekly-activity-trend"
                    subtitle="Weekly movement of completed, overdue and upcoming work" height="28rem">
                    <x-dashboard-apex-chart :options="$weeklyTrendChart"
                        chart-key="activities-weekly-trend-{{ md5(json_encode($weeklyTrendChart)) }}" />
                    <x-slot name="footer">Compare blue versus orange each week to identify whether execution is
                        recovering or deteriorating.</x-slot>
                </x-dashboard-chart-card>

                <x-dashboard-chart-card title="Overdue aging" filename="overdue-activity-aging"
                    subtitle="Time elapsed since incomplete activities became overdue" height="28rem">
                    <x-dashboard-apex-chart :options="$agingChart"
                        chart-key="activities-aging-{{ md5(json_encode($agingChart)) }}" />
                    <x-slot name="footer">Items in the 8+ week range should be escalated, rescheduled or formally
                        closed.</x-slot>
                </x-dashboard-chart-card>
            </section>
        @endif

        <section class="dashboard-panel overflow-hidden bg-white">
            <div class="soft-title-surface flex items-center justify-between border-b px-5 py-4">
                <div>
                    <h2 class="font-bold text-sky-950">Milestones requiring attention</h2>
                    <p class="text-xs text-slate-500">Overdue milestones first, followed by the nearest upcoming
                        commitments</p>
                </div>
                <span
                    class="rounded-full bg-white px-3 py-1 text-xs font-bold text-orange-700 shadow-sm">{{ $milestoneMetrics['overdue'] }}
                    overdue</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($urgentMilestones as $item)
                    @php
                        $isOverdue = $item->dashboard_status === 'overdue';
                        $days = now()->startOfDay()->diffInDays($item->due_date, false);
                    @endphp
                    <div
                        class="grid gap-3 p-4 hover:bg-sky-50/40 md:grid-cols-[minmax(0,1fr)_auto_auto] md:items-center">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-800">
                                {{ $item->milestone?->name ?? ($item->milestone?->code ?? 'Milestone') }}</p>
                            <a href="{{ route('projects.dashboard', $item->project) }}"
                                class="mt-1 block truncate text-xs font-semibold text-sky-700 hover:text-orange-700">{{ $item->project->name }}
                                @if ($item->project->pda_code)
                                    · {{ $item->project->pda_code }}
                                @endif
                            </a>
                        </div>
                        <span class="text-xs font-semibold text-slate-500">{{ $item->due_date->format('M Y') }}</span>
                        <span
                            class="{{ $isOverdue ? 'bg-orange-100 text-orange-800' : 'bg-sky-50 text-sky-700' }} rounded-full px-3 py-1 text-xs font-bold">
                            {{ $isOverdue ? abs((int) $days) . ' days overdue' : (int) $days . ' days remaining' }}
                        </span>
                    </div>
                @empty
                    <p class="p-10 text-center text-sm text-slate-500">No milestones require attention.</p>
                @endforelse
            </div>
        </section>

        <section class="grid gap-5 xl:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
            <div class="dashboard-panel bg-white p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-sky-950">Top projects by activities</h2>
                        <p class="text-xs text-slate-500">Execution distribution by project</p>
                    </div>
                    <select wire:model.live="topLimit"
                        class="rounded-lg border-sky-200 bg-white py-2 text-sm text-sky-900 focus:border-orange-400 focus:ring-orange-300">
                        <option value="5">Top 5</option>
                        <option value="10">Top 10</option>
                    </select>
                </div>
                <div class="mt-5 space-y-4">
                    @forelse ($topProjects as $item)
                        <div class="rounded-lg border border-sky-100 bg-white p-3">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <a href="{{ route('projects.dashboard', $item['project']) }}"
                                    class="truncate text-sm font-bold text-sky-900 hover:text-orange-700">{{ $item['project']->name }}</a>
                                <span
                                    class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-sky-800 shadow-sm">{{ $item['total'] }}</span>
                            </div>
                            <div class="flex h-2.5 overflow-hidden rounded-full bg-white">
                                @if ($item['total'])
                                    <span class="bg-sky-600"
                                        style="width: {{ ($item['completed'] / $item['total']) * 100 }}%"></span>
                                    <span class="bg-orange-500"
                                        style="width: {{ ($item['overdue'] / $item['total']) * 100 }}%"></span>
                                    <span class="bg-orange-200"
                                        style="width: {{ ($item['pending'] / $item['total']) * 100 }}%"></span>
                                @endif
                            </div>
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[11px] font-semibold text-slate-600">
                                <span>{{ $item['completed'] }} completed</span>
                                <span>{{ $item['overdue'] }} overdue</span>
                                <span>{{ $item['pending'] }} upcoming</span>
                            </div>
                        </div>
                    @empty
                        <p class="py-12 text-center text-sm text-slate-500">No activities have been registered.</p>
                    @endforelse
                </div>
            </div>

            <aside class="dashboard-panel overflow-hidden">
                <div class="border-b border-orange-100 bg-orange-50 px-5 py-4">
                    <h2 class="font-bold text-orange-900">Status guide</h2>
                </div>
                <div class="space-y-3 p-5 text-sm">
                    <div class="rounded-lg border border-sky-100 bg-sky-50 p-3"><b class="text-sky-900">Completed</b>
                        <p class="mt-1 text-slate-600">Marked with a check in Planification.</p>
                    </div>
                    <div class="rounded-lg border border-orange-200 bg-orange-50 p-3"><b
                            class="text-orange-900">Overdue</b>
                        <p class="mt-1 text-slate-600">The planned week ended without execution.</p>
                    </div>
                    <div class="rounded-lg border border-orange-100 bg-white p-3"><b
                            class="text-orange-800">Upcoming</b>
                        <p class="mt-1 text-slate-600">Still within its planned date.</p>
                    </div>
                </div>
            </aside>
        </section>

        <section class="dashboard-panel overflow-hidden">
            <div
                class="soft-title-surface flex flex-col gap-3 border-b p-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="font-bold text-sky-950">Activity detail</h2>
                    <p class="text-xs text-slate-500">Up to 50 activities, with overdue items first</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <input type="search" wire:model.live.debounce.350ms="search"
                        placeholder="Search activity or project..."
                        class="rounded-lg border-sky-200 bg-white text-sm focus:border-orange-400 focus:ring-orange-300 sm:w-72">
                    <select wire:model.live="status"
                        class="rounded-lg border-sky-200 bg-white text-sm focus:border-orange-400 focus:ring-orange-300">
                        <option value="all">All statuses</option>
                        <option value="completed">Completed</option>
                        <option value="overdue">Overdue</option>
                        <option value="pending">Upcoming</option>
                    </select>
                </div>
            </div>
            <div class="divide-y divide-sky-100">
                @forelse ($activities as $activity)
                    @php
                        $style = match ($activity->dashboard_status) {
                            'completed' => [
                                'marker' => 'bg-sky-600',
                                'badge' => 'bg-sky-100 text-sky-800',
                                'label' => 'Completed',
                            ],
                            'overdue' => [
                                'marker' => 'bg-orange-500',
                                'badge' => 'bg-orange-100 text-orange-800',
                                'label' => 'Overdue',
                            ],
                            default => [
                                'marker' => 'bg-orange-200',
                                'badge' => 'bg-orange-50 text-orange-700',
                                'label' => 'Upcoming',
                            ],
                        };
                    @endphp
                    <div
                        class="grid gap-3 border-l-4 border-transparent p-4 transition hover:border-orange-300 hover:bg-sky-50/50 sm:grid-cols-[1fr_auto] sm:items-center">
                        <div class="flex min-w-0 gap-3"><span
                                class="{{ $style['marker'] }} mt-1.5 h-3 w-3 shrink-0 rounded-full"></span>
                            <div class="min-w-0">
                                <p class="whitespace-pre-line text-sm text-slate-800">{{ $activity->activity }}</p><a
                                    href="{{ route('projects.dashboard', $activity->project) }}"
                                    class="mt-1 block truncate text-xs font-bold text-sky-700 hover:text-orange-700">{{ $activity->project->name }}
                                    @if ($activity->project->pda_code)
                                        · {{ $activity->project->pda_code }}
                                    @endif
                                </a>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 pl-6 sm:pl-0"><span
                                class="text-xs text-slate-500">W{{ str_pad($activity->week_number, 2, '0', STR_PAD_LEFT) }}
                                · {{ $activity->week_year }}</span><span
                                class="{{ $style['badge'] }} rounded-full px-2.5 py-1 text-xs font-bold">{{ $style['label'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="p-12 text-center text-sm text-slate-500">No activities match these filters.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
