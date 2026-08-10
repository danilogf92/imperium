<?php

namespace App\Services\Planification;

use App\Enums\ProjectStateEnum;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectWeeklyActivity;
use App\Support\ProjectOrderSort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Carbon\CarbonImmutable;

final class PlanificationQueryService
{
    public function __construct(
        private readonly PlanificationAccessService $access,
    ) {}

    public function viewData(array $filters): array
    {
        $activityWeeks = $this->activityWeeks($filters['activityWeeks'] ?? '');
        $plannedProjects = ProjectOrderSort::apply(
            $this->plannedProjectsQuery($filters, $activityWeeks)
        )->paginate((int) $filters['perPage']);

        $timelineYears = $this->timelineYears(
            $plannedProjects->getCollection()
        );

        $filterProjects = $this->access
            ->authorizedProjects()
            ->with('company:id,company_name')
            ->get([
                'id',
                'company_id',
                'state',
                'forecast_start_date',
            ]);

        return [
            'plannedProjects' => $plannedProjects,
            'timelineYears' => $timelineYears,
            'projects' => $this->modalProjects(),
            'milestones' => $this->milestones(),
            'plantOptions' => $this->plantOptions($filterProjects),
            'statusOptions' => ProjectStateEnum::values(),
            'creationYearOptions' => $this->creationYearOptions($filterProjects),
            'activityWeekOptions' => $this->calendarActivityWeekOptions(
                $filterProjects,
                $filters['creationYears'] ?? []
            ),
            'activityWeeks' => $activityWeeks,
            'canExport' => $this->access->canExport(),
            'months' => $this->months(),
        ];
    }

    private function plannedProjectsQuery(array $filters, array $activityWeeks): Builder
    {
        $query = $this->access
            ->authorizedProjects()
            ->with([
                'company:id,company_name',
                'projectMilestones' => fn ($query) => $query
                    ->with('milestone:id,name,code,color,view_color')
                    ->when(($filters['milestoneExecution'] ?? '') === 'completed', fn (Builder $query) => $query->due()->whereNotNull('executed_at'))
                    ->when(($filters['milestoneExecution'] ?? '') === 'incomplete', fn (Builder $query) => $query->due()->whereNull('executed_at'))
                    ->orderBy('cycle_year')
                    ->orderBy('sequence'),
                'weeklyActivities' => fn ($query) => $query
                    ->when(($filters['activityExecution'] ?? '') === 'completed', fn (Builder $query) => $query->whereNotNull('executed_at'))
                    ->when(($filters['activityExecution'] ?? '') === 'incomplete', fn (Builder $query) => $query->whereNull('executed_at'))
                    ->where(function (Builder $query) use ($activityWeeks): void {
                        foreach ($activityWeeks as $week) {
                            $query->orWhere(fn (Builder $query) => $query
                                ->where('week_year', $week['year'])
                                ->where('week_number', $week['week']));
                        }
                    }),
            ])
            ->withSum('data as data_budgeted', 'global_price')
            ->withSum('data as data_budgeted_euros', 'global_price_euros');

        $this->applyFilters($query, $filters, $activityWeeks);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters, array $activityWeeks): void
    {
        if ($filters['plants'] !== []) {
            $query->whereIn('company_id', $filters['plants']);
        }

        if ($filters['statuses'] !== []) {
            $query->whereIn('state', $filters['statuses']);
        }

        if ($filters['creationYears'] !== []) {
            $query->where(function (Builder $query) use ($filters): void {
                foreach ($filters['creationYears'] as $year) {
                    $query->orWhereYear('forecast_start_date', $year);
                }
            });
        }

        if ($filters['onlyWithMilestones']) {
            $query->whereHas('projectMilestones');
        }

        if (($filters['milestoneExecution'] ?? '') === 'completed') {
            $query->whereHas('projectMilestones', fn (Builder $query) => $query->due()->whereNotNull('executed_at'));
        } elseif (($filters['milestoneExecution'] ?? '') === 'incomplete') {
            $query->whereHas('projectMilestones', fn (Builder $query) => $query->due()->whereNull('executed_at'));
        }

        if (($filters['activityExecution'] ?? '') === 'completed') {
            $query->whereHas('weeklyActivities', fn (Builder $query) => $query
                ->whereNotNull('executed_at')
                ->where(function (Builder $query) use ($activityWeeks): void {
                    foreach ($activityWeeks as $week) {
                        $query->orWhere(fn (Builder $query) => $query
                            ->where('week_year', $week['year'])->where('week_number', $week['week']));
                    }
                }));
        } elseif (($filters['activityExecution'] ?? '') === 'incomplete') {
            $query->whereHas('weeklyActivities', fn (Builder $query) => $query
                ->whereNull('executed_at')
                ->where(function (Builder $query) use ($activityWeeks): void {
                    foreach ($activityWeeks as $week) {
                        $query->orWhere(fn (Builder $query) => $query
                            ->where('week_year', $week['year'])->where('week_number', $week['week']));
                    }
                }));
        }

        if (trim($filters['search']) !== '') {
            $this->applySearch($query, $filters['search']);
        }
    }

    private function applySearch(Builder $query, string $search): void
    {
        $search = '%'.trim($search).'%';

        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('name', 'like', $search)
                ->orWhere('pda_code', 'like', $search)
                ->orWhere('state', 'like', $search)
                ->orWhereHas(
                    'company',
                    fn (Builder $query) => $query
                        ->where('company_name', 'like', $search)
                )
                ->orWhereHas(
                    'projectMilestones.milestone',
                    fn (Builder $query) => $query
                        ->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search)
                )
                ->orWhereHas('weeklyActivities', fn (Builder $query) => $query
                    ->where('activity', 'like', $search));
        });
    }

    private function timelineYears(Collection $projects): Collection
    {
        return $projects
            ->flatMap(function (Project $project) {
                $firstYear = $project->forecast_start_date?->year
                    ?? $project->projectMilestones->min('cycle_year')
                    ?? now()->year;

                return $project
                    ->projectMilestones
                    ->pluck('cycle_year')
                    ->push($firstYear, $firstYear + 1);
            })
            ->unique()
            ->sort()
            ->values();
    }

    private function modalProjects(): Collection
    {
        return ProjectOrderSort::apply($this->access
            ->authorizedProjects()
            ->withExists([
                'projectMilestones as is_closed' => fn (Builder $query) => $query
                    ->whereHas(
                        'milestone',
                        fn (Builder $query) => $query
                            ->whereRaw('UPPER(code) = ?', ['CLOSED'])
                    ),
            ])
            ->withSum('data as data_budgeted', 'global_price')
            ->withSum('data as data_budgeted_euros', 'global_price_euros'))
            ->get([
                'id',
                'name',
                'forecast_start_date',
            ]);
    }

    private function milestones(): Collection
    {
        return Milestone::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'color',
                'view_color',
            ]);
    }

    private function plantOptions(Collection $projects): Collection
    {
        return $projects
            ->filter(fn (Project $project) => $project->company)
            ->map(fn (Project $project) => [
                'id' => $project->company_id,
                'name' => $project->company->company_name,
            ])
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    private function creationYearOptions(Collection $projects): Collection
    {
        return $projects
            ->map(fn (Project $project) => $project->forecast_start_date?->year)
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
    }

    private function activityWeeks(string $selectedWeek = ''): array
    {
        $baseDate = preg_match('/^(\d{4})-W(\d{2})$/', $selectedWeek, $matches)
            ? CarbonImmutable::now()->setISODate((int) $matches[1], (int) $matches[2])->startOfDay()
            : CarbonImmutable::now()->startOfWeek();

        return collect([0, 1])->map(function (int $offset) use ($baseDate): array {
            $date = $baseDate->addWeeks($offset);
            return [
                'offset' => $offset,
                'year' => (int) $date->isoWeekYear,
                'week' => (int) $date->isoWeek,
                'label' => ($offset === 0 ? 'Actual Week' : 'Next Week').' · '.$date->isoFormat('MMM D'),
            ];
        })->all();
    }

    private function calendarActivityWeekOptions(Collection $projects, array $selectedYears): Collection
    {
        $projectYears = $projects
            ->pluck('forecast_start_date')
            ->filter()
            ->map(fn ($date) => (int) $date->year)
            ->unique()
            ->sortDesc();
        $years = $selectedYears !== []
            ? $projectYears->intersect(array_map('intval', $selectedYears))
            : $projectYears;

        return $years
            ->flatMap(function (int $year): array {
                $weeksInYear = CarbonImmutable::create($year, 12, 28)->isoWeek();

                return collect(range(1, $weeksInYear))->map(fn (int $week) => [
                    'value' => sprintf('%d-W%02d', $year, $week),
                    'label' => sprintf('Week %02d - %d', $week, $year),
                ])->all();
            })
            ->values();
    }

    private function activityWeekOptions(): Collection
    {
        return ProjectWeeklyActivity::query()
            ->whereHas('project', fn (Builder $query) => $query
                ->whereIn('company_id', $this->access->allowedCompanyIds()))
            ->select(['week_year', 'week_number'])->distinct()
            ->orderByDesc('week_year')->orderByDesc('week_number')->get()
            ->map(fn (ProjectWeeklyActivity $activity) => [
                'value' => sprintf('%d-W%02d', $activity->week_year, $activity->week_number),
                'label' => sprintf('%d · Week %02d', $activity->week_year, $activity->week_number),
            ]);
    }

    private function months(): array
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }
}
