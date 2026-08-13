<?php

namespace App\Livewire\Activities;

use App\Enums\ProjectStateEnum;
use App\Models\ProjectWeeklyActivity;
use App\Models\ProjectMilestone;
use App\Services\Planification\PlanificationAccessService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;

class ActivitiesDashboard extends Component
{
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
        $today = CarbonImmutable::today();
        $activities = $this->activityQuery($access)
            ->get()
            ->map(function (ProjectWeeklyActivity $activity) use ($today): ProjectWeeklyActivity {
                $activity->setAttribute('dashboard_status', $this->activityStatus($activity, $today));
                $activity->setAttribute('week_start', CarbonImmutable::now()->setISODate(
                    $activity->week_year,
                    $activity->week_number
                )->startOfWeek());

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
            });

        $metrics = [
            'total' => $activities->count(),
            'completed' => $activities->where('dashboard_status', 'completed')->count(),
            'overdue' => $activities->where('dashboard_status', 'overdue')->count(),
            'pending' => $activities->where('dashboard_status', 'pending')->count(),
        ];
        $metrics['completion'] = $metrics['total'] > 0
            ? (int) round(($metrics['completed'] / $metrics['total']) * 100)
            : 0;

        $filtered = $this->status === 'all'
            ? $activities
            : $activities->where('dashboard_status', $this->status);
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
            'metrics' => $metrics,
            'milestoneMetrics' => $milestoneMetrics,
            ...$charts,
            'topProjects' => $this->topProjects($activities),
            'urgentMilestones' => $milestones
                ->whereIn('dashboard_status', ['overdue', 'pending'])
                ->sortBy(fn (ProjectMilestone $milestone) => $milestone->due_date->timestamp)
                ->take(12)
                ->values(),
            'activities' => $filtered->sortBy(
                fn (ProjectWeeklyActivity $activity) => sprintf('%d-%04d-%02d', match ($activity->dashboard_status) {
                    'overdue' => 0,
                    'pending' => 1,
                    default => 2,
                }, $activity->week_year, $activity->week_number)
            )->take(50)->values(),
        ])->layout('layouts.app');
    }

    private function activityQuery(PlanificationAccessService $access): Builder
    {
        return ProjectWeeklyActivity::query()
            ->whereHas('project', fn (Builder $query) => $query
                ->whereIn('company_id', $access->allowedCompanyIds())
                ->where('state', '<>', ProjectStateEnum::Postponed->value))
            ->with('project:id,name,slug,pda_code,company_id')
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
                ->where('state', '<>', ProjectStateEnum::Postponed->value))
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

    private function activityStatus(ProjectWeeklyActivity $activity, CarbonImmutable $today): string
    {
        if ($activity->executed_at !== null) {
            return 'completed';
        }

        $weekEnd = CarbonImmutable::now()
            ->setISODate($activity->week_year, $activity->week_number)
            ->endOfWeek()
            ->startOfDay();

        return $today->isAfter($weekEnd) ? 'overdue' : 'pending';
    }

    private function topProjects(Collection $activities): Collection
    {
        return $activities->groupBy('project_id')
            ->map(function (Collection $items): array {
                $project = $items->first()->project;

                return [
                    'project' => $project,
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
                'name' => $project?->name ?? 'Unknown project',
                'activities' => $activityItems->count(),
                'milestones' => $milestoneItems->count(),
                'score' => $activityItems->count() + ($milestoneItems->count() * 3),
            ];
        })->sortByDesc('score')
            ->take(8)
            ->values();

        $weeks = collect(range(7, 0))->map(function (int $offset) use ($today, $activities): array {
            $date = $today->startOfWeek()->subWeeks($offset);
            $items = $activities->filter(fn (ProjectWeeklyActivity $activity): bool =>
                $activity->week_year === (int) $date->isoWeekYear
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
                'style' => ['fontSize' => '11px', 'fontWeight' => 700],
                'background' => ['enabled' => false],
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
                'fontSize' => '12px',
                'fontWeight' => 600,
                'markers' => ['width' => 9, 'height' => 9, 'radius' => 9],
                'itemMargin' => ['horizontal' => 10, 'vertical' => 4],
            ],
        ];

        return [
            'statusChart' => array_merge($base, [
                'series' => [
                    $activities->where('dashboard_status', 'completed')->count(),
                    $overdue->count(),
                    $activities->where('dashboard_status', 'pending')->count(),
                ],
                'labels' => ['Completed', 'Overdue', 'Upcoming'],
                'chart' => $base['chart'] + ['type' => 'donut'],
                'colors' => ['#0284C7', '#F97316', '#FED7AA'],
                'stroke' => ['width' => 3, 'colors' => ['#FFFFFF']],
                'dataLabels' => ['enabled' => false],
                'legend' => array_merge($base['legend'], ['position' => 'bottom', 'horizontalAlign' => 'center']),
                'plotOptions' => ['pie' => ['expandOnClick' => false, 'donut' => ['size' => '68%', 'labels' => [
                    'show' => true,
                    'name' => ['show' => true, 'color' => '#64748B'],
                    'value' => ['show' => true, 'fontSize' => '24px', 'fontWeight' => 700, 'color' => '#0F172A'],
                    'total' => ['show' => true, 'label' => 'Activities', 'fontSize' => '12px', 'color' => '#64748B'],
                ]]]],
            ]),
            'riskProjectChart' => array_merge($base, [
                'series' => [
                    ['name' => 'Overdue activities', 'data' => $riskProjects->pluck('activities')->all()],
                    ['name' => 'Overdue milestones', 'data' => $riskProjects->pluck('milestones')->all()],
                ],
                'chart' => $base['chart'] + ['type' => 'bar', 'stacked' => true],
                'colors' => ['#0284C7', '#F97316'],
                'plotOptions' => ['bar' => ['horizontal' => true, 'borderRadius' => 4, 'borderRadiusApplication' => 'end', 'barHeight' => '52%']],
                'dataLabels' => ['enabled' => false],
                'xaxis' => [
                    'categories' => $riskProjects->pluck('name')->all(),
                    'tickAmount' => 5,
                    'labels' => ['style' => ['fontSize' => '11px'], 'formatter' => 'function(value) { return Math.round(value); }'],
                ],
                'yaxis' => ['labels' => ['maxWidth' => 180, 'style' => ['fontSize' => '11px', 'fontWeight' => 600, 'colors' => ['#475569']]]],
                'legend' => $base['legend'],
            ]),
            'weeklyTrendChart' => array_merge($base, [
                'series' => [
                    ['name' => 'Completed', 'data' => $weeks->pluck('completed')->all()],
                    ['name' => 'Overdue', 'data' => $weeks->pluck('overdue')->all()],
                    ['name' => 'Upcoming', 'data' => $weeks->pluck('pending')->all()],
                ],
                'chart' => $base['chart'] + ['type' => 'bar', 'stacked' => true],
                'colors' => ['#0284C7', '#F97316', '#FED7AA'],
                'plotOptions' => ['bar' => ['borderRadius' => 3, 'borderRadiusApplication' => 'end', 'columnWidth' => '48%']],
                'dataLabels' => ['enabled' => false],
                'xaxis' => ['categories' => $weeks->pluck('label')->all(), 'labels' => ['style' => ['fontSize' => '11px']]],
                'yaxis' => ['min' => 0, 'forceNiceScale' => true, 'labels' => ['formatter' => 'function(value) { return Math.round(value); }']],
                'legend' => $base['legend'],
            ]),
            'agingChart' => array_merge($base, [
                'series' => [['name' => 'Overdue activities', 'data' => array_values($aging)]],
                'chart' => $base['chart'] + ['type' => 'bar'],
                'colors' => ['#FB923C'],
                'plotOptions' => ['bar' => ['borderRadius' => 5, 'borderRadiusApplication' => 'end', 'columnWidth' => '42%', 'distributed' => true]],
                'xaxis' => ['categories' => array_keys($aging), 'labels' => ['style' => ['fontSize' => '11px', 'fontWeight' => 600]]],
                'yaxis' => ['min' => 0, 'forceNiceScale' => true, 'labels' => ['formatter' => 'function(value) { return Math.round(value); }']],
                'legend' => ['show' => false],
            ]),
            'milestoneStatusChart' => array_merge($base, [
                'series' => [
                    $milestones->where('dashboard_status', 'completed')->count(),
                    $overdueMilestones->count(),
                    $milestones->where('dashboard_status', 'pending')->count(),
                ],
                'labels' => ['Completed', 'Overdue', 'Upcoming'],
                'chart' => $base['chart'] + ['type' => 'donut'],
                'colors' => ['#0284C7', '#F97316', '#FED7AA'],
                'stroke' => ['width' => 3, 'colors' => ['#FFFFFF']],
                'dataLabels' => ['enabled' => false],
                'legend' => array_merge($base['legend'], ['position' => 'bottom', 'horizontalAlign' => 'center']),
                'plotOptions' => ['pie' => ['expandOnClick' => false, 'donut' => ['size' => '68%', 'labels' => [
                    'show' => true,
                    'name' => ['show' => true, 'color' => '#64748B'],
                    'value' => ['show' => true, 'fontSize' => '24px', 'fontWeight' => 700, 'color' => '#0F172A'],
                    'total' => ['show' => true, 'label' => 'Milestones', 'fontSize' => '12px', 'color' => '#64748B'],
                ]]]],
            ]),
            'riskSummary' => [
                'project' => $riskProjects->first()['name'] ?? null,
                'activities' => $riskProjects->first()['activities'] ?? 0,
                'milestones' => $riskProjects->first()['milestones'] ?? 0,
                'critical' => $aging['8+ weeks'],
            ],
        ];
    }
}
