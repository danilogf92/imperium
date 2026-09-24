<?php

namespace App\Livewire\Activities;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectPermissionEnum;
use App\Livewire\Dashboard\Concerns\InteractsWithDashboardFilters;
use App\Models\ProjectMilestone;
use App\Models\ProjectWeeklyActivity;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\Dashboard\DashboardQueryService;
use App\Services\Planification\PlanificationAccessService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ActivitiesDashboard extends Component
{
    use InteractsWithDashboardFilters { resetAll as private resetProjectFilters; }
    use WithPagination;

    private const PAGE_SIZES = [5, 10, 20, 50, 100];

    private const UI_PREFERENCES = [
        'activityPerPage' => ['activities.detail.per_page', 'count'],
        'milestonePerPage' => ['activities.milestones.per_page', 'count'],
        'summaryOpen' => ['activities.summary', 'open'],
    ];

    private const FILTER_LABELS = [
        'companyFilter' => 'Companies',
        'yearSearch' => 'Years',
        'stateSearch' => 'States',
        'typeOfProjectSearch' => 'Classifications',
        'investmentSearch' => 'Investments',
        'justificationSearch' => 'Justifications',
        'userFilter' => 'activity_control.user',
        'projectFilter' => 'activity_control.project',
        'status' => 'activity_control.status',
        'search' => 'activity_control.search',
    ];

    public int $activityPerPage = 10;

    public int $milestonePerPage = 10;

    public bool $summaryOpen = true;

    public string $userFilter = '';

    public string $projectFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function resetAll(): void
    {
        $this->resetProjectFilters();
        $this->reset(['userFilter', 'projectFilter', 'dateFrom', 'dateTo', 'status', 'search']);
        $this->resetValidation();
        $this->resetListPages();
    }

    public function updated($property): void
    {
        if (isset(self::UI_PREFERENCES[$property])) {
            if ($property !== 'summaryOpen') {
                $this->{$property} = $this->validPageSize($this->{$property});
                $this->resetPage($property === 'activityPerPage' ? 'page' : 'milestonesPage');
            }
            [$key, $field] = self::UI_PREFERENCES[$property];
            UserPreference::query()->updateOrCreate(
                ['user_id' => auth()->id(), 'key' => $key],
                ['value' => [$field => $this->{$property}]]
            );

            return;
        }

        if (! str_starts_with($property, 'paginators.') && $property !== 'topLimit') {
            $this->resetListPages();
        }

        if ($property === 'companyFilter' || str_starts_with($property, 'companyFilter.')) {
            $this->reset(['userFilter', 'projectFilter']);
        }

        if (in_array($property, ['dateFrom', 'dateTo'], true)) {
            $this->validate([
                'dateFrom' => ['nullable', 'date_format:Y-m-d'],
                'dateTo' => ['nullable', 'date_format:Y-m-d', ...($this->dateFrom !== '' ? ['after_or_equal:dateFrom'] : [])],
            ]);
        }
    }

    private function validPageSize(mixed $value): int
    {
        return in_array($value, self::PAGE_SIZES, true) ? $value : 10;
    }

    public function toggleSummary(): void
    {
        $this->summaryOpen = ! $this->summaryOpen;
        $this->updated('summaryOpen');
    }

    private function resetListPages(): void
    {
        $this->resetPage();
        $this->resetPage('milestonesPage');
    }

    public function removeFilter(string $property, ?string $value = null): void
    {
        abort_unless(isset(self::FILTER_LABELS[$property]) || $property === 'due', 422);

        if ($property === 'due') {
            $this->reset(['dateFrom', 'dateTo']);
        } elseif (is_array($this->{$property})) {
            $this->{$property} = array_values(array_filter($this->{$property}, fn ($item) => (string) $item !== $value));
        } else {
            $this->reset($property);
        }

        $this->resetValidation();
        $this->resetListPages();
    }

    private function activeFilters(Collection $users, Collection $projects, Collection $companies): array
    {
        $chips = [];
        foreach (self::FILTER_LABELS as $property => $label) {
            $selection = $this->{$property};
            if ($selection === '' || $selection === [] || ($property === 'status' && $selection === 'all')) {
                continue;
            }
            foreach (is_array($selection) ? $selection : [$selection] as $value) {
                $display = match ($property) {
                    'userFilter' => $users->firstWhere('id', $value)?->name ?? __('activity_control.unavailable_selection'),
                    'projectFilter' => $projects->firstWhere('id', $value)?->pda_code ?: ($projects->firstWhere('id', $value)?->name ?? __('activity_control.unavailable_selection')),
                    'companyFilter' => $companies->firstWhere('company_code', $value)?->company_name ?? $value,
                    'status' => __('activity_control.'.$value),
                    default => __((string) $value),
                };
                $chips[] = ['property' => $property, 'value' => (string) $value, 'label' => __($label).': '.$display];
            }
        }
        if ($this->dateFrom !== '' || $this->dateTo !== '') {
            $chips[] = ['property' => 'due', 'value' => null, 'label' => __('activity_control.due').': '.($this->dateFrom ?: '…').' – '.($this->dateTo ?: '…')];
        }

        return $chips;
    }

    private function matchesDueRange($item): bool
    {
        return ($this->dateFrom === '' || $item->due_date->toDateString() >= $this->dateFrom)
            && ($this->dateTo === '' || $item->due_date->toDateString() <= $this->dateTo);
    }

    private function paginateItems(Collection $items, int $perPage, string $pageName): LengthAwarePaginator
    {
        $page = max(1, min($this->getPage($pageName), max(1, (int) ceil($items->count() / $perPage))));
        if ($this->getPage($pageName) !== $page) {
            $this->setPage($page, $pageName);
        }

        return new LengthAwarePaginator($items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, [
            'path' => request()->url(), 'pageName' => $pageName,
        ]);
    }

    public function selectUser(int $userId): void
    {
        abort_unless($this->visibleUsers()->whereKey($userId)->exists(), 403);
        $this->userFilter = (string) $userId;
        $this->resetListPages();
        $this->dispatch('activities-user-selected');
    }

    public function showUserOverdue(int $userId): void
    {
        abort_unless($this->visibleUsers()->whereKey($userId)->exists(), 403);
        $this->userFilter = (string) $userId;
        $this->status = 'overdue';
        $this->resetListPages();
        $this->dispatch('activities-filtered');
    }

    private function visibleUsers(): Builder
    {
        $companies = auth()->user()->companiesForPermissionQuery(ProjectPermissionEnum::View)
            ->when($this->companyFilter, fn (Builder $query) => $query->whereIn('company_code', $this->companyFilter))
            ->select('companies.id')->reorder();

        return User::query()->whereHas('roles', fn (Builder $roles) => $roles
            ->whereIn('company_id', $companies)
            ->whereHas('permissions', fn (Builder $permissions) => $permissions->where('name', ProjectPermissionEnum::View->value)));
    }

    public function mount(DashboardQueryService $queries): void
    {
        abort_unless(auth()->check(), 403);
        $this->years = $queries->availableYears(auth()->user());
        $stored = auth()->user()->preferences()->whereIn('key', array_column(self::UI_PREFERENCES, 0))->get()->keyBy('key');
        foreach (self::UI_PREFERENCES as $property => [$key, $field]) {
            $value = $stored->get($key)?->value[$field] ?? $this->{$property};
            $this->{$property} = $property === 'summaryOpen'
                ? (is_bool($value) ? $value : true)
                : $this->validPageSize($value);
        }
    }

    public string $status = 'all';

    public string $search = '';

    public int $topLimit = 5;

    public function updatedStatus(): void
    {
        if (! in_array($this->status, ['all', 'completed', 'overdue', 'pending'], true)) {
            $this->status = 'all';
        }
    }

    public function updatedTopLimit(): void
    {
        if (! in_array((int) $this->topLimit, [5, 10], true)) {
            $this->topLimit = 5;
        }
    }

    public function render(PlanificationAccessService $access): View
    {
        $this->sanitizeFilters();
        $this->activityPerPage = $this->validPageSize($this->activityPerPage);
        $this->milestonePerPage = $this->validPageSize($this->milestonePerPage);
        $today = CarbonImmutable::today();
        $companies = auth()->user()->companiesForPermission(ProjectPermissionEnum::View);
        $users = $this->visibleUsers()->orderBy('name')->get(['id', 'name']);
        $projects = app(DashboardQueryService::class)->projectQuery(auth()->user(), $this->filters())
            ->orderBy('name')->get(['projects.id', 'name', 'pda_code']);
        $activities = $this->activityQuery($access)
            ->get()
            ->map(function (ProjectWeeklyActivity $activity) use ($today): ProjectWeeklyActivity {
                $activity->setAttribute('dashboard_status', $this->activityStatus($activity, $today));
                $activity->setAttribute('week_start', CarbonImmutable::now()->setISODate(
                    $activity->week_year,
                    $activity->week_number
                )->startOfWeek());

                $activity->setAttribute('due_date', $activity->week_start->endOfWeek());

                return $activity;
            })->filter(fn (ProjectWeeklyActivity $activity) => $this->matchesDueRange($activity))
            ->when($this->status !== 'all', fn (Collection $items) => $items->where('dashboard_status', $this->status));
        $metrics = $this->summarize($activities);
        $topOverdueActivities = $activities->where('dashboard_status', 'overdue')
            ->sortBy('due_date')->take(5)->map(function (ProjectWeeklyActivity $activity) use ($today) {
                $activity->setAttribute('planned_month', $activity->week_start->startOfMonth()->locale(app()->getLocale()));
                $activity->setAttribute('days_overdue', (int) $activity->due_date->startOfDay()->diffInDays($today));

                return $activity;
            });
        $milestones = $this->milestoneQuery($access)
            ->get()
            ->map(function (ProjectMilestone $milestone) use ($today): ProjectMilestone {
                $dueDate = CarbonImmutable::create($milestone->cycle_year, $milestone->month, 1)->endOfMonth();
                $milestone->setAttribute('due_date', $dueDate);
                $milestone->setAttribute('dashboard_status', $milestone->executed_at
                    ? 'completed'
                    : ($today->isAfter($dueDate) ? 'overdue' : 'pending'));

                return $milestone;
            })->filter(fn (ProjectMilestone $milestone) => $this->matchesDueRange($milestone))
            ->when($this->status !== 'all', fn (Collection $items) => $items->where('dashboard_status', $this->status));

        $filtered = $activities;
        $userSummary = $users->when($this->userFilter !== '', fn (Collection $items) => $items->where('id', $this->userFilter))
            ->map(fn (User $user) => ['user' => $user, ...$this->summarize($filtered->where('assigned_to', $user->id))])
            ->sortByDesc('overdue')->values();
        $sorted = $filtered->sortBy(fn (ProjectWeeklyActivity $activity) => sprintf('%d-%04d-%02d-%010d', match ($activity->dashboard_status) {
            'overdue' => 0,
            'pending' => 1,
            default => 2,
        }, $activity->week_year, $activity->week_number, $activity->id))->values();
        $charts = $this->chartData($activities, $milestones, $today);
        $milestoneMetrics = [
            'total' => $milestones->count(),
            'completed' => $milestones->where('dashboard_status', 'completed')->count(),
            'overdue' => $milestones->where('dashboard_status', 'overdue')->count(),
            'pending' => $milestones->where('dashboard_status', 'pending')->count(),
        ];
        $milestoneMetrics['completion'] = $milestoneMetrics['total'] > 0
            ? (int) round(($milestoneMetrics['completed'] / $milestoneMetrics['total']) * 100)
            : 0;

        return view('livewire.activities.activities-dashboard', [
            'companies' => $companies,
            'activeFilters' => $this->activeFilters($users, $projects, $companies),
            'pageSizes' => self::PAGE_SIZES,
            'stateOptions' => $this->reportableStateOptions(),
            'classificationOptions' => InvestmentClassificationEnum::cases(),
            'investmentOptions' => InvestmentEnum::cases(),
            'justificationOptions' => ProjectJustificationEnum::cases(),
            'metrics' => $metrics,
            'users' => $users,
            'projects' => $projects,
            'userSummary' => $userSummary,
            'milestoneMetrics' => $milestoneMetrics,
            ...$charts,
            'topOverdueActivities' => $topOverdueActivities,
            'topProjects' => $this->topProjects($activities),
            'urgentMilestones' => $this->paginateItems($milestones
                ->whereIn('dashboard_status', ['overdue', 'pending'])
                ->sortBy(fn (ProjectMilestone $milestone) => [$milestone->due_date->timestamp, $milestone->id])
                ->values(), $this->milestonePerPage, 'milestonesPage'),
            'activities' => $this->paginateItems($sorted, $this->activityPerPage, 'page'),
        ])->layout('layouts.app');
    }

    private function activityQuery(PlanificationAccessService $access): Builder
    {
        return ProjectWeeklyActivity::query()->whereNotNull('week_year')
            ->whereHas('project', fn (Builder $query) => $query
                ->whereIn('company_id', $access->allowedCompanyIds())
                ->whereIn('projects.id', $this->filteredProjectIds()))
            ->where(fn (Builder $query) => $query->whereNull('assigned_to')
                ->orWhereIn('assigned_to', $this->visibleUsers()->select('users.id')))
            ->when($this->userFilter !== '', fn (Builder $query) => $query->where('assigned_to', $this->userFilter))
            ->with(['project:id,name,slug,pda_code,company_id', 'author:id,name', 'assignee:id,name'])
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('activity', 'like', $term)
                        ->orWhereHas('project', fn (Builder $project) => $project
                            ->where('name', 'like', $term)
                            ->orWhere('pda_code', 'like', $term));
                });
            });
    }

    private function milestoneQuery(PlanificationAccessService $access): Builder
    {
        return ProjectMilestone::query()
            ->whereHas('project', fn (Builder $query) => $query
                ->whereIn('company_id', $access->allowedCompanyIds())
                ->whereIn('projects.id', $this->filteredProjectIds()))
            ->with([
                'project:id,name,slug,pda_code,company_id',
                'milestone:id,name,code,color,view_color',
            ])
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function (Builder $query) use ($term): void {
                    $query->whereHas('milestone', fn (Builder $milestone) => $milestone
                        ->where('name', 'like', $term)->orWhere('code', 'like', $term))
                        ->orWhereHas('project', fn (Builder $project) => $project
                            ->where('name', 'like', $term)->orWhere('pda_code', 'like', $term));
                });
            });
    }

    private function filteredProjectIds(): Builder
    {
        return app(DashboardQueryService::class)
            ->projectQuery(auth()->user(), $this->filters())
            ->when($this->projectFilter !== '', fn (Builder $query) => $query->where('projects.id', $this->projectFilter))
            ->select('projects.id');
    }

    private function activityStatus(ProjectWeeklyActivity $activity, CarbonImmutable $today): string
    {
        if ($activity->executed_at !== null) {
            return 'completed';
        }

        $dueDate = CarbonImmutable::now()
            ->setISODate($activity->week_year, $activity->week_number)
            ->endOfWeek();

        return $dueDate->isBefore($today) ? 'overdue' : 'pending';
    }

    private function summarize(Collection $activities): array
    {
        $total = $activities->count();
        $completed = $activities->where('dashboard_status', 'completed')->count();

        return [
            'total' => $total,
            'pending' => $activities->where('dashboard_status', 'pending')->count(),
            'overdue' => $activities->where('dashboard_status', 'overdue')->count(),
            'completed' => $completed,
            'completion' => $total > 0 ? (int) round($completed / $total * 100) : 0,
        ];
    }

    private function topProjects(Collection $activities): Collection
    {
        return $activities->groupBy('project_id')
            ->map(function (Collection $items): array {
                return [
                    'project' => $items->first()->project,
                    'total' => $items->count(),
                    'completed' => $items->where('dashboard_status', 'completed')->count(),
                    'overdue' => $items->where('dashboard_status', 'overdue')->count(),
                    'pending' => $items->where('dashboard_status', 'pending')->count(),
                ];
            })
            ->sortByDesc('total')
            ->take($this->topLimit)
            ->values();
    }

    private function chartData(Collection $activities, Collection $milestones, CarbonImmutable $today): array
    {
        $overdue = $activities->where('dashboard_status', 'overdue');
        $overdueMilestones = $milestones->where('dashboard_status', 'overdue');
        $projectIds = $overdue->pluck('project_id')->merge($overdueMilestones->pluck('project_id'))->unique();
        $riskProjects = $projectIds->map(function (int $projectId) use ($overdue, $overdueMilestones): array {
            $activityItems = $overdue->where('project_id', $projectId);
            $milestoneItems = $overdueMilestones->where('project_id', $projectId);
            $project = $activityItems->first()?->project ?? $milestoneItems->first()?->project;

            return [
                'name' => $project?->name ?? __('Unknown project'),
                'activities' => $activityItems->count(),
                'milestones' => $milestoneItems->count(),
                'score' => $activityItems->count() + ($milestoneItems->count() * 3),
            ];
        })->sortByDesc('score')
            ->take(8)
            ->values();

        $weeks = collect(range(7, 0))->map(function (int $offset) use ($today, $activities): array {
            $date = $today->startOfWeek()->subWeeks($offset);
            $items = $activities->filter(fn (ProjectWeeklyActivity $activity): bool => $activity->week_year === (int) $date->isoWeekYear
                && $activity->week_number === (int) $date->isoWeek
            );

            return [
                'label' => 'W'.str_pad((string) $date->isoWeek, 2, '0', STR_PAD_LEFT),
                'completed' => $items->where('dashboard_status', 'completed')->count(),
                'overdue' => $items->where('dashboard_status', 'overdue')->count(),
                'pending' => $items->where('dashboard_status', 'pending')->count(),
            ];
        });

        $aging = ['1 week' => 0, '2–3 weeks' => 0, '4–7 weeks' => 0, '8+ weeks' => 0];
        foreach ($overdue as $activity) {
            $weekEnd = CarbonImmutable::now()->setISODate($activity->week_year, $activity->week_number)->endOfWeek();
            $age = max(1, (int) $weekEnd->diffInWeeks($today));
            $bucket = match (true) {
                $age <= 1 => '1 week',
                $age <= 3 => '2–3 weeks',
                $age <= 7 => '4–7 weeks',
                default => '8+ weeks',
            };
            $aging[$bucket]++;
        }

        $base = [
            'chart' => [
                'height' => '100%',
                'toolbar' => ['show' => false],
                'fontFamily' => 'Figtree, sans-serif',
                'foreColor' => '#64748B',
                'background' => 'transparent',
                'parentHeightOffset' => 0,
                'animations' => ['enabled' => true, 'speed' => 450],
            ],
            'dataLabels' => [
                'enabled' => true,
                'style' => ['fontSize' => '12px', 'fontWeight' => 700, 'colors' => ['#0F172A']],
                'background' => [
                    'enabled' => true,
                    'foreColor' => '#0F172A',
                    'borderRadius' => 4,
                    'padding' => 4,
                    'opacity' => 0.92,
                    'borderWidth' => 1,
                    'borderColor' => '#E2E8F0',
                ],
            ],
            'grid' => [
                'show' => true,
                'borderColor' => '#E2E8F0',
                'strokeDashArray' => 3,
                'padding' => ['left' => 8, 'right' => 12, 'top' => 4, 'bottom' => 0],
            ],
            'tooltip' => ['theme' => 'light', 'shared' => true, 'intersect' => false],
            'legend' => [
                'show' => true,
                'position' => 'top',
                'horizontalAlign' => 'left',
                'fontSize' => '13px',
                'fontWeight' => 600,
                'labels' => ['colors' => '#334155'],
                'markers' => ['width' => 11, 'height' => 11, 'radius' => 3],
                'itemMargin' => ['horizontal' => 14, 'vertical' => 6],
            ],
            'noData' => [
                'text' => __('No data available for this chart'),
                'align' => 'center',
                'verticalAlign' => 'middle',
                'style' => ['color' => '#64748B', 'fontSize' => '14px', 'fontFamily' => 'Figtree, sans-serif'],
            ],
        ];

        return [
            'statusChart' => array_merge($base, [
                'series' => [
                    $activities->where('dashboard_status', 'completed')->count(),
                    $overdue->count(),
                    $activities->where('dashboard_status', 'pending')->count(),
                ],
                'labels' => [__('activity_control.completed'), __('activity_control.overdue'), __('Upcoming')],
                'chart' => $base['chart'] + ['type' => 'donut'],
                'colors' => ['#22C55E', '#EF4444', '#38BDF8'],
                'stroke' => ['show' => true, 'width' => 3, 'colors' => ['#FFFFFF'], 'lineCap' => 'round'],
                'fill' => ['type' => 'gradient', 'gradient' => ['shade' => 'light', 'shadeIntensity' => 0.18, 'opacityFrom' => 1, 'opacityTo' => 0.88, 'stops' => [0, 85, 100]]],
                'dataLabels' => [
                    'enabled' => true,
                    'formatter' => 'function(value, options) { const count = options.w.config.series[options.seriesIndex] || 0; return count > 0 ? count : ""; }',
                    'style' => ['fontSize' => '13px', 'fontWeight' => 800, 'colors' => ['#FFFFFF']],
                    'dropShadow' => ['enabled' => true, 'opacity' => 0.35, 'blur' => 3, 'left' => 0, 'top' => 1],
                ],
                'tooltip' => ['theme' => 'light', 'fillSeriesColor' => false, 'y' => ['formatter' => 'function(value) { return value + (value === 1 ? " activity" : " activities"); }']],
                'legend' => array_merge($base['legend'], [
                    'position' => 'bottom',
                    'horizontalAlign' => 'center',
                    'formatter' => 'function(name, options) { return name + "  " + options.w.globals.series[options.seriesIndex]; }',
                    'markers' => ['width' => 10, 'height' => 10, 'radius' => 10],
                ]),
                'plotOptions' => ['pie' => ['expandOnClick' => false, 'customScale' => 0.9, 'donut' => ['size' => '58%', 'labels' => [
                    'show' => true,
                    'name' => ['show' => true, 'offsetY' => -8, 'fontSize' => '12px', 'fontWeight' => 600, 'color' => '#64748B'],
                    'value' => ['show' => true, 'offsetY' => 8, 'fontSize' => '28px', 'fontWeight' => 800, 'color' => '#0F172A', 'formatter' => 'function(value) { return Math.round(value); }'],
                    'total' => ['show' => true, 'showAlways' => true, 'label' => __('Total activities'), 'fontSize' => '12px', 'fontWeight' => 600, 'color' => '#64748B', 'formatter' => 'function(options) { return options.globals.seriesTotals.reduce((total, value) => total + value, 0); }'],
                ]]]],
                'responsive' => [[
                    'breakpoint' => 640,
                    'options' => [
                        'plotOptions' => ['pie' => ['customScale' => 0.84, 'donut' => ['size' => '56%']]],
                        'dataLabels' => ['style' => ['fontSize' => '11px']],
                        'legend' => ['fontSize' => '11px', 'itemMargin' => ['horizontal' => 7, 'vertical' => 4]],
                    ],
                ]],
            ]),
            'riskProjectChart' => array_merge($base, [
                'series' => [
                    ['name' => __('Overdue activities'), 'data' => $riskProjects->pluck('activities')->all()],
                    ['name' => __('Overdue milestones'), 'data' => $riskProjects->pluck('milestones')->all()],
                ],
                'chart' => $base['chart'] + ['type' => 'bar', 'stacked' => true],
                'colors' => ['#2563EB', '#EA580C'],
                'plotOptions' => ['bar' => ['horizontal' => true, 'borderRadius' => 4, 'borderRadiusApplication' => 'end', 'barHeight' => '52%']],
                'dataLabels' => [
                    'enabled' => true,
                    'formatter' => 'function(value) { return value > 0 ? Math.round(value) : ""; }',
                    'style' => ['fontSize' => '11px', 'fontWeight' => 800, 'colors' => ['#FFFFFF']],
                ],
                'xaxis' => [
                    'categories' => $riskProjects->pluck('name')->all(),
                    'tickAmount' => 5,
                    'labels' => ['style' => ['fontSize' => '12px', 'fontWeight' => 600, 'colors' => ['#475569']], 'formatter' => 'function(value) { return Math.round(value); }'],
                ],
                'yaxis' => ['labels' => ['maxWidth' => 260, 'trim' => false, 'style' => ['fontSize' => '12px', 'fontWeight' => 600, 'colors' => ['#334155']]]],
                'legend' => $base['legend'],
            ]),
            'weeklyTrendChart' => array_merge($base, [
                'series' => [
                    ['name' => __('activity_control.completed'), 'data' => $weeks->pluck('completed')->all()],
                    ['name' => __('activity_control.overdue'), 'data' => $weeks->pluck('overdue')->all()],
                    ['name' => __('Upcoming'), 'data' => $weeks->pluck('pending')->all()],
                ],
                'chart' => $base['chart'] + ['type' => 'bar', 'stacked' => true],
                'colors' => ['#2563EB', '#EA580C', '#F59E0B'],
                'plotOptions' => ['bar' => ['borderRadius' => 3, 'borderRadiusApplication' => 'end', 'columnWidth' => '48%']],
                'dataLabels' => [
                    'enabled' => true,
                    'formatter' => 'function(value) { return value > 0 ? Math.round(value) : ""; }',
                    'style' => ['fontSize' => '11px', 'fontWeight' => 800, 'colors' => ['#FFFFFF']],
                ],
                'xaxis' => ['categories' => $weeks->pluck('label')->all(), 'labels' => ['style' => ['fontSize' => '11px']]],
                'yaxis' => ['min' => 0, 'forceNiceScale' => true, 'labels' => ['formatter' => 'function(value) { return Math.round(value); }']],
                'legend' => $base['legend'],
            ]),
            'agingChart' => array_merge($base, [
                'series' => [['name' => __('Overdue activities'), 'data' => array_values($aging)]],
                'chart' => $base['chart'] + ['type' => 'bar'],
                'colors' => ['#F59E0B', '#F97316', '#EA580C', '#C2410C'],
                'plotOptions' => ['bar' => ['borderRadius' => 5, 'borderRadiusApplication' => 'end', 'columnWidth' => '42%', 'distributed' => true]],
                'xaxis' => ['categories' => array_map(fn ($label) => __($label), array_keys($aging)), 'labels' => ['style' => ['fontSize' => '11px', 'fontWeight' => 600]]],
                'yaxis' => ['min' => 0, 'forceNiceScale' => true, 'labels' => ['formatter' => 'function(value) { return Math.round(value); }']],
                'legend' => ['show' => false],
            ]),
            'milestoneStatusChart' => array_merge($base, [
                'series' => [
                    $milestones->where('dashboard_status', 'completed')->count(),
                    $overdueMilestones->count(),
                    $milestones->where('dashboard_status', 'pending')->count(),
                ],
                'labels' => [__('activity_control.completed'), __('activity_control.overdue'), __('Upcoming')],
                'chart' => $base['chart'] + ['type' => 'donut'],
                'colors' => ['#22C55E', '#EF4444', '#38BDF8'],
                'stroke' => ['show' => true, 'width' => 3, 'colors' => ['#FFFFFF'], 'lineCap' => 'round'],
                'fill' => ['type' => 'gradient', 'gradient' => ['shade' => 'light', 'shadeIntensity' => 0.18, 'opacityFrom' => 1, 'opacityTo' => 0.88, 'stops' => [0, 85, 100]]],
                'dataLabels' => [
                    'enabled' => true,
                    'formatter' => 'function(value, options) { const count = options.w.config.series[options.seriesIndex] || 0; return count > 0 ? count : ""; }',
                    'style' => ['fontSize' => '13px', 'fontWeight' => 800, 'colors' => ['#FFFFFF']],
                    'dropShadow' => ['enabled' => true, 'opacity' => 0.35, 'blur' => 3, 'left' => 0, 'top' => 1],
                ],
                'tooltip' => ['theme' => 'light', 'fillSeriesColor' => false, 'y' => ['formatter' => 'function(value) { return value + (value === 1 ? " milestone" : " milestones"); }']],
                'legend' => array_merge($base['legend'], [
                    'position' => 'bottom',
                    'horizontalAlign' => 'center',
                    'formatter' => 'function(name, options) { return name + "  " + options.w.globals.series[options.seriesIndex]; }',
                    'markers' => ['width' => 10, 'height' => 10, 'radius' => 10],
                ]),
                'plotOptions' => ['pie' => ['expandOnClick' => false, 'customScale' => 0.9, 'donut' => ['size' => '58%', 'labels' => [
                    'show' => true,
                    'name' => ['show' => true, 'offsetY' => -8, 'fontSize' => '12px', 'fontWeight' => 600, 'color' => '#64748B'],
                    'value' => ['show' => true, 'offsetY' => 8, 'fontSize' => '28px', 'fontWeight' => 800, 'color' => '#0F172A', 'formatter' => 'function(value) { return Math.round(value); }'],
                    'total' => ['show' => true, 'showAlways' => true, 'label' => __('Total milestones'), 'fontSize' => '12px', 'fontWeight' => 600, 'color' => '#64748B', 'formatter' => 'function(options) { return options.globals.seriesTotals.reduce((total, value) => total + value, 0); }'],
                ]]]],
                'responsive' => [[
                    'breakpoint' => 640,
                    'options' => [
                        'plotOptions' => ['pie' => ['customScale' => 0.84, 'donut' => ['size' => '56%']]],
                        'dataLabels' => ['style' => ['fontSize' => '11px']],
                        'legend' => ['fontSize' => '11px', 'itemMargin' => ['horizontal' => 7, 'vertical' => 4]],
                    ],
                ]],
            ]),
            'riskSummary' => [
                'project' => $riskProjects->first()['name'] ?? null,
                'activities' => $riskProjects->first()['activities'] ?? 0,
                'milestones' => $riskProjects->first()['milestones'] ?? 0,
                'projects' => $riskProjects->take(3)->values()->all(),
                'critical' => $aging['8+ weeks'],
            ],
        ];
    }
}
