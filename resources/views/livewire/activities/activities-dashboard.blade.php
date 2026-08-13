<div class="dashboard-page-shell">
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
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Planification control
                            center</p>
                        <h1 class="text-2xl font-bold text-sky-950">Activities & milestones dashboard</h1>
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
            @foreach ([['label' => 'Total activities', 'value' => $metrics['total'], 'accent' => 'border-sky-200', 'icon' => 'bg-sky-100 text-sky-700'], ['label' => 'Completed', 'value' => $metrics['completed'], 'accent' => 'border-sky-200', 'icon' => 'bg-sky-100 text-sky-700'], ['label' => 'Overdue', 'value' => $metrics['overdue'], 'accent' => 'border-orange-200', 'icon' => 'bg-orange-100 text-orange-700'], ['label' => 'Upcoming', 'value' => $metrics['pending'], 'accent' => 'border-orange-200', 'icon' => 'bg-orange-100 text-orange-700'], ['label' => 'Completion', 'value' => $metrics['completion'] . '%', 'accent' => 'border-sky-200', 'icon' => 'bg-sky-600 text-white']] as $metric)
                <article class="rounded-xl border {{ $metric['accent'] }} bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $metric['label'] }}
                            </p>
                            <p class="mt-2 text-3xl font-black text-sky-950">{{ $metric['value'] }}</p>
                        </div>
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $metric['icon'] }}"><span
                                class="h-2.5 w-2.5 rounded-full bg-current"></span></span>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="dashboard-metrics-grid">
            @foreach ([['label' => 'Total milestones', 'value' => $milestoneMetrics['total'], 'color' => 'text-sky-900'], ['label' => 'Completed milestones', 'value' => $milestoneMetrics['completed'], 'color' => 'text-sky-700'], ['label' => 'Overdue milestones', 'value' => $milestoneMetrics['overdue'], 'color' => 'text-orange-700'], ['label' => 'Upcoming milestones', 'value' => $milestoneMetrics['pending'], 'color' => 'text-orange-600'], ['label' => 'Milestone completion', 'value' => $milestoneMetrics['completion'] . '%', 'color' => 'text-blue-700']] as $metric)
                <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $metric['label'] }}</p>
                    <p class="mt-2 text-3xl font-black {{ $metric['color'] }}">{{ $metric['value'] }}</p>
                </article>
            @endforeach
        </section>

        @if ($metrics['total'] > 0 || $milestoneMetrics['total'] > 0)
            <section class="rounded-xl border border-orange-200 bg-orange-50 p-4 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-orange-700">Management focus</p>
                        @if ($riskSummary['project'])
                            <p class="mt-1 text-sm text-slate-700"><b
                                    class="text-orange-900">{{ $riskSummary['project'] }}</b> requires corrective
                                action: <b>{{ $riskSummary['activities'] }}</b> overdue
                                {{ Str::plural('activity', $riskSummary['activities']) }} and
                                <b>{{ $riskSummary['milestones'] }}</b> overdue
                                {{ Str::plural('milestone', $riskSummary['milestones']) }}.
                            </p>
                        @else
                            <p class="mt-1 text-sm font-semibold text-sky-800">There are no overdue activities or
                                milestones in the current selection.</p>
                        @endif
                    </div>
                    @if ($riskSummary['critical'] > 0)
                        <span
                            class="shrink-0 rounded-lg bg-white px-3 py-2 text-xs font-bold text-orange-800 shadow-sm">{{ $riskSummary['critical'] }}
                            overdue for 8+ weeks</span>
                    @endif
                </div>
            </section>

            <section class="grid gap-5 xl:grid-cols-2">
                <x-dashboard-chart-card title="Activity health" filename="activity-health"
                    subtitle="Immediate view of completed work, overdue commitments and upcoming workload"
                    height="30rem">
                    <x-dashboard-apex-chart :options="$statusChart"
                        chart-key="activities-status-{{ md5(json_encode($statusChart)) }}" />
                    <x-slot name="footer">A growing orange segment signals that planned work is not being closed on
                        time.</x-slot>
                </x-dashboard-chart-card>

                <x-dashboard-chart-card title="Projects requiring corrective action"
                    filename="overdue-commitments-by-project"
                    subtitle="Combined overdue activities and milestones; milestones carry greater operational impact"
                    height="30rem">
                    <x-dashboard-apex-chart :options="$riskProjectChart"
                        chart-key="activities-risk-projects-{{ md5(json_encode($riskProjectChart)) }}" />
                    <x-slot name="footer">Start with the projects at the top: they concentrate the largest overdue
                        workload.</x-slot>
                </x-dashboard-chart-card>

                <x-dashboard-chart-card title="Milestone health" filename="milestone-health"
                    subtitle="Completed, overdue and upcoming project milestones" height="30rem">
                    <x-dashboard-apex-chart :options="$milestoneStatusChart"
                        chart-key="milestones-status-{{ md5(json_encode($milestoneStatusChart)) }}" />
                    <x-slot name="footer">Overdue milestones can affect the project sequence and should be reviewed
                        before routine activities.</x-slot>
                </x-dashboard-chart-card>

                <x-dashboard-chart-card title="Eight-week execution trend" filename="weekly-activity-trend"
                    subtitle="Weekly mix of completed, overdue and upcoming activities" height="30rem">
                    <x-dashboard-apex-chart :options="$weeklyTrendChart"
                        chart-key="activities-weekly-trend-{{ md5(json_encode($weeklyTrendChart)) }}" />
                    <x-slot name="footer">Compare blue versus orange each week to identify whether execution is
                        recovering or deteriorating.</x-slot>
                </x-dashboard-chart-card>

                <x-dashboard-chart-card class="xl:col-span-2" title="Overdue aging" filename="overdue-activity-aging"
                    subtitle="How long incomplete activities have remained overdue" height="30rem">
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
                            class="rounded-full px-3 py-1 text-xs font-bold {{ $isOverdue ? 'bg-orange-100 text-orange-800' : 'bg-sky-50 text-sky-700' }}">
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
                                <span>{{ $item['completed'] }} completed</span><span>{{ $item['overdue'] }}
                                    overdue</span><span>{{ $item['pending'] }} upcoming</span>
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
                                class="mt-1.5 h-3 w-3 shrink-0 rounded-full {{ $style['marker'] }}"></span>
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
                                class="rounded-full px-2.5 py-1 text-xs font-bold {{ $style['badge'] }}">{{ $style['label'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="p-12 text-center text-sm text-slate-500">No activities match these filters.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
