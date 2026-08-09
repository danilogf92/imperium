<?php

namespace App\Exports;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PlanificationExport
{
    public function download(User $user, array $filters): BinaryFileResponse
    {
        $selectedWeek = (string) ($filters['activityWeeks'] ?? '');
        $baseDate = preg_match('/^(\d{4})-W(\d{2})$/', $selectedWeek, $matches)
            ? CarbonImmutable::now()->setISODate((int) $matches[1], (int) $matches[2])->startOfDay()
            : CarbonImmutable::now()->startOfWeek();
        $activityWeeks = collect([0, 1])->map(function (int $offset) use ($baseDate): array {
            $date = $baseDate->addWeeks($offset);
            return ['year' => (int) $date->isoWeekYear, 'week' => (int) $date->isoWeek];
        });

        $projects = Project::query()
            ->with([
                'company:id,company_name',
                'projectMilestones' => fn ($query) => $query
                    ->with('milestone:id,name,code,color')
                    ->orderBy('cycle_year')
                    ->orderBy('month')
                    ->orderBy('sequence'),
                'weeklyActivities' => fn ($query) => $query->where(function (Builder $query) use ($activityWeeks): void {
                    foreach ($activityWeeks as $week) {
                        $query->orWhere(fn (Builder $query) => $query
                            ->where('week_year', $week['year'])->where('week_number', $week['week']));
                    }
                }),
            ])
            ->withSum('data as data_budgeted', 'global_price')
            ->withSum('data as data_budgeted_euros', 'global_price_euros')
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(ProjectPermissionEnum::Export)
                    ->select('companies.id')
                    ->reorder()
            )
            ->when($filters['plants'] !== [], fn (Builder $query) => $query->whereIn('company_id', $filters['plants']))
            ->when($filters['statuses'] !== [], fn (Builder $query) => $query->whereIn('state', $filters['statuses']))
            ->when($filters['creationYears'] !== [], function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    foreach ($filters['creationYears'] as $year) {
                        $query->orWhereYear('forecast_start_date', $year);
                    }
                });
            })
            ->when($filters['onlyWithMilestones'], fn (Builder $query) => $query->whereHas('projectMilestones'))
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = '%' . trim($filters['search']) . '%';
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('pda_code', 'like', $search)
                        ->orWhere('state', 'like', $search)
                        ->orWhereHas('company', fn (Builder $query) => $query->where('company_name', 'like', $search))
                        ->orWhereHas('weeklyActivities', fn (Builder $query) => $query->where('activity', 'like', $search))
                        ->orWhereHas('projectMilestones.milestone', fn (Builder $query) => $query
                            ->where('name', 'like', $search)
                            ->orWhere('code', 'like', $search));
                });
            })
            ->orderBy('name')
            ->get();

        $years = $projects
            ->flatMap(function (Project $project) {
                $firstYear = $project->forecast_start_date?->year
                    ?? $project->projectMilestones->min('cycle_year')
                    ?? now()->year;

                return $project->projectMilestones->pluck('cycle_year')->push($firstYear, $firstYear + 1);
            })
            ->unique()
            ->sort()
            ->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Planification');
        $sheet->freezePane('I3');

        $currency = ($filters['currency'] ?? 'usd') === 'eur' ? 'eur' : 'usd';
        $cellDisplay = in_array(($filters['cellDisplay'] ?? 'combined'), ['combined', 'milestone', 'value'], true)
            ? $filters['cellDisplay']
            : 'combined';
        $currencySymbol = $currency === 'eur' ? '€' : '$';

        $fixedHeaders = ['Forecast Start Year', 'Plant', 'PDA Code', 'Name', 'Budgeted Total', 'Status', 'Actual Week', 'Next Week'];
        foreach ($fixedHeaders as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue("{$column}1", $header);
            $sheet->mergeCells("{$column}1:{$column}2");
        }

        $monthLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $columnIndex = 9;
        foreach ($years as $year) {
            $startColumn = Coordinate::stringFromColumnIndex($columnIndex);
            $endColumn = Coordinate::stringFromColumnIndex($columnIndex + 11);
            $sheet->setCellValue("{$startColumn}1", $year);
            $sheet->mergeCells("{$startColumn}1:{$endColumn}1");

            foreach ($monthLabels as $monthLabel) {
                $column = Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->setCellValue("{$column}2", $monthLabel);
                $columnIndex++;
            }
        }

        $row = 3;
        foreach ($projects as $project) {
            $sheet->fromArray([
                $project->forecast_start_date?->year,
                $project->company?->company_name,
                $project->pda_code,
                $project->name,
                (float) ($currency === 'eur' ? $project->data_budgeted_euros : $project->data_budgeted),
                $project->state?->value,
                $project->weeklyActivities->filter(fn ($activity) =>
                    $activity->week_year === $activityWeeks[0]['year'] && $activity->week_number === $activityWeeks[0]['week'])
                    ->pluck('activity')->implode("\n"),
                $project->weeklyActivities->filter(fn ($activity) =>
                    $activity->week_year === $activityWeeks[1]['year'] && $activity->week_number === $activityWeeks[1]['week'])
                    ->pluck('activity')->implode("\n"),
            ], null, "A{$row}");

            $sheet->getStyle("E{$row}")
                ->getNumberFormat()
                ->setFormatCode($currency === 'eur' ? '€#,##0.00' : '$#,##0.00');

            $sheet->getStyle("G{$row}:H{$row}")->getAlignment()->setWrapText(true);

            $statusColors = [
                ltrim($project->state?->softColor() ?? '#F1F5F9', '#'),
                ltrim($project->state?->textColor() ?? '#334155', '#'),
            ];
            $sheet->getStyle("F{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $statusColors[1]]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusColors[0]]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $columnIndex = 9;
            foreach ($years as $year) {
                for ($month = 1; $month <= 12; $month++) {
                    $cellMilestones = $project->projectMilestones
                        ->where('cycle_year', $year)
                        ->where('month', $month);
                    $codes = $cellMilestones
                        ->map(function ($item) use ($project, $currency, $currencySymbol, $cellDisplay): string {
                            $budget = (float) ($currency === 'eur'
                                ? $project->data_budgeted_euros
                                : $project->data_budgeted);
                            $value = $budget * ((float) $item->percentage / 100);

                            return match ($cellDisplay) {
                                'milestone' => (string) $item->milestone?->code,
                                'value' => $currencySymbol . number_format($value, 2),
                                default => $item->milestone?->code . ' | ' . $currencySymbol . number_format($value, 2),
                            };
                        })
                        ->filter()
                        ->implode(', ');
                    $column = Coordinate::stringFromColumnIndex($columnIndex);
                    $sheet->setCellValue("{$column}{$row}", $codes);

                    if ($cellMilestones->isNotEmpty()) {
                        $backgroundColor = $this->normalizeColor(
                            $cellMilestones->first()->milestone?->color
                        );
                        $sheet->getStyle("{$column}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => $this->contrastColor($backgroundColor)],
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $backgroundColor],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                        ]);
                    }

                    $columnIndex++;
                }
            }
            $row++;
        }

        $lastColumn = Coordinate::stringFromColumnIndex(max(6, $columnIndex - 1));
        $sheet->getStyle("A1:{$lastColumn}2")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("A3:{$lastColumn}" . max(3, $row - 1))
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A1:{$lastColumn}" . max(2, $row - 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CBD5E1'));
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(48);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(16);
        $sheet->getColumnDimension('G')->setWidth(35);
        $sheet->getColumnDimension('H')->setWidth(35);
        for ($index = 9; $index <= max(8, $columnIndex - 1); $index++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index))->setWidth(22);
        }

        $fixedColumnKeys = [
            'forecast_year', 'plant', 'pda_code', 'name',
            'budgeted', 'status', 'actual_week', 'next_week',
        ];
        $visibleColumns = (array) ($filters['visibleColumns'] ?? $fixedColumnKeys);
        foreach (array_reverse($fixedColumnKeys, true) as $index => $key) {
            if (! in_array($key, $visibleColumns, true)) {
                $sheet->removeColumn(Coordinate::stringFromColumnIndex($index + 1));
            }
        }
        $sheet->freezePane(Coordinate::stringFromColumnIndex(
            count(array_intersect($fixedColumnKeys, $visibleColumns)) + 1
        ).'3');

        $directory = storage_path('app/private/exports');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $path = $directory . '/planification-' . uniqid('', true) . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()->download(
            $path,
            'project-planification.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function normalizeColor(?string $color): string
    {
        $color = strtoupper(ltrim((string) $color, '#'));

        return preg_match('/^[0-9A-F]{6}$/', $color) ? $color : '64748B';
    }

    private function contrastColor(string $backgroundColor): string
    {
        $red = hexdec(substr($backgroundColor, 0, 2));
        $green = hexdec(substr($backgroundColor, 2, 2));
        $blue = hexdec(substr($backgroundColor, 4, 2));
        $luminance = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $luminance > 150 ? '0F172A' : 'FFFFFF';
    }
}
