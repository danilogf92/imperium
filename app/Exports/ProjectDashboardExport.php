<?php

namespace App\Exports;

use App\Enums\ProjectPermissionEnum;
use App\Enums\ProjectStateEnum;
use App\Models\Project;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectDashboardExport
{
    private const BLUE = '2563EB';

    private const DARK_BLUE = '1E3A8A';

    private array $temporaryImages = [];

    public function download(User $user): BinaryFileResponse
    {
        $projects = Project::query()
            ->with([
                'company:id,company_name',
                'projectMilestones' => fn ($query) => $query
                    ->with('milestone:id,name,code,color')
                    ->orderBy('cycle_year')
                    ->orderBy('month')
                    ->orderBy('sequence'),
            ])
            ->withCount('data')
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(ProjectPermissionEnum::Export)
                    ->select('companies.id')
                    ->reorder()
            )
            ->orderBy('name')
            ->get();

        $spreadsheet = new Spreadsheet();
        $dashboard = $spreadsheet->getActiveSheet();
        $dashboard->setTitle('Dashboard');
        $projectsSheet = $spreadsheet->createSheet()->setTitle('Projects');
        $planificationSheet = $spreadsheet->createSheet()->setTitle('Planification');

        $this->buildProjectsSheet($projectsSheet, $projects);
        $this->buildPlanificationSheet($planificationSheet, $projects);
        $this->buildDashboardSheet($dashboard, $projects);

        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getProperties()
            ->setCreator('DA Imperium')
            ->setTitle('Project Dashboard')
            ->setSubject('Project portfolio dashboard and planification');

        $directory = storage_path('app/private/exports');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . '/project-dashboard-' . uniqid('', true) . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(true);
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        foreach ($this->temporaryImages as $temporaryImage) {
            @unlink($temporaryImage);
        }

        return response()->download(
            $path,
            'project-dashboard.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function buildProjectsSheet(Worksheet $sheet, $projects): void
    {
        $headers = [
            'ID', 'Creation Year', 'Plant', 'Name', 'PDA Code', 'Status', 'Rate',
            'Investment', 'Classification', 'Justification', 'Forecast Start Date',
            'Forecast End Date', 'Approved Date', 'Close Date', 'Data Rows', 'Milestones',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($projects as $project) {
            $sheet->fromArray([
                $project->id,
                $project->created_at?->year,
                $project->company?->company_name,
                $project->name,
                $project->pda_code,
                $project->state?->value,
                (float) $project->rate,
                $project->investments?->value,
                $project->classification_of_investments?->value,
                $project->justification?->value,
                $project->forecast_start_date,
                $project->forecast_end_date,
                $project->approve_date,
                $project->close_date,
                $project->data_count,
                $project->projectMilestones->count(),
            ], null, "A{$row}");

            if ($project->state) {
                $sheet->getStyle("F{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => ltrim($project->state->textColor(), '#')]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ltrim($project->state->softColor(), '#')]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }
            $row++;
        }

        $lastRow = max(2, $row - 1);
        $this->styleDetailSheet($sheet, "A1:P{$lastRow}");
        $sheet->getStyle("G2:G{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (['K', 'L', 'M', 'N'] as $column) {
            $sheet->getStyle("{$column}2:{$column}{$lastRow}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        }
        $sheet->getColumnDimension('D')->setWidth(48);
        $sheet->getColumnDimension('C')->setWidth(24);
        $sheet->getColumnDimension('H')->setWidth(28);
        $sheet->getColumnDimension('I')->setWidth(30);
        $sheet->getColumnDimension('J')->setWidth(22);
        $sheet->setAutoFilter("A1:P{$lastRow}");
        $sheet->freezePane('A2');
    }

    private function buildPlanificationSheet(Worksheet $sheet, $projects): void
    {
        $sheet->fromArray(
            ['Project ID', 'Plant', 'Project', 'Status', 'Year', 'Month', 'Milestone Code', 'Milestone Name'],
            null,
            'A1'
        );

        $row = 2;
        foreach ($projects as $project) {
            foreach ($project->projectMilestones as $projectMilestone) {
                $sheet->fromArray([
                    $project->id,
                    $project->company?->company_name,
                    $project->name,
                    $project->state?->value,
                    $projectMilestone->cycle_year,
                    $projectMilestone->month,
                    $projectMilestone->milestone?->code,
                    $projectMilestone->milestone?->name,
                ], null, "A{$row}");

                $color = $this->normalizeColor($projectMilestone->milestone?->color);
                $sheet->getStyle("G{$row}:H{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $this->contrastColor($color)]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                ]);
                $row++;
            }
        }

        $lastRow = max(2, $row - 1);
        $this->styleDetailSheet($sheet, "A1:H{$lastRow}");
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(48);
        $sheet->getColumnDimension('H')->setWidth(32);
        $sheet->setAutoFilter("A1:H{$lastRow}");
        $sheet->freezePane('A2');
    }

    private function buildDashboardSheet(Worksheet $sheet, $projects): void
    {
        $sheet->setShowGridlines(false);
        $sheet->mergeCells('A1:L2');
        $sheet->setCellValue('A1', 'PROJECT PORTFOLIO DASHBOARD');
        $sheet->getStyle('A1:L2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 22, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::DARK_BLUE]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $projectLastRow = max(2, $projects->count() + 1);
        $cards = [
            ['A4:C4', 'A5:C7', 'Total Projects', "=COUNTA('Projects'!D2:D{$projectLastRow})", self::BLUE],
            ['D4:F4', 'D5:F7', 'In Execution', "=COUNTIF('Projects'!F2:F{$projectLastRow},\"Execution\")", '2563EB'],
            ['G4:I4', 'G5:I7', 'Finished', "=COUNTIF('Projects'!F2:F{$projectLastRow},\"Finished\")", '059669'],
            ['J4:L4', 'J5:L7', 'Milestones', "=SUM('Projects'!P2:P{$projectLastRow})", '7C3AED'],
        ];
        foreach ($cards as [$labelRange, $valueRange, $label, $formula, $color]) {
            $sheet->mergeCells($labelRange);
            $sheet->mergeCells($valueRange);
            $sheet->setCellValue(explode(':', $labelRange)[0], $label);
            $sheet->setCellValue(explode(':', $valueRange)[0], $formula);
            $sheet->getStyle($labelRange)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getStyle($valueRange)->applyFromArray([
                'font' => ['bold' => true, 'size' => 24, 'color' => ['rgb' => $color]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
                'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        $statusStart = 28;
        $sheet->fromArray(['Status', 'Projects'], null, "A{$statusStart}");
        $statusRow = $statusStart + 1;
        foreach (ProjectStateEnum::cases() as $state) {
            $sheet->setCellValue("A{$statusRow}", $state->value);
            $sheet->setCellValue("B{$statusRow}", "=COUNTIF('Projects'!F2:F{$projectLastRow},A{$statusRow})");
            $sheet->getStyle("A{$statusRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => ltrim($state->textColor(), '#')]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ltrim($state->softColor(), '#')]],
            ]);
            $statusRow++;
        }
        $this->styleHelperTable($sheet, "A{$statusStart}:B" . ($statusRow - 1));

        $plants = $projects->pluck('company.company_name')->filter()->countBy()->sortDesc()->take(10);
        $plantStart = 28;
        $sheet->fromArray(['Plant', 'Projects'], null, "D{$plantStart}");
        $plantRow = $plantStart + 1;
        foreach ($plants as $plant => $count) {
            $sheet->fromArray([$plant, $count], null, "D{$plantRow}");
            $plantRow++;
        }
        $this->styleHelperTable($sheet, "D{$plantStart}:E" . max($plantStart + 1, $plantRow - 1));

        $years = $projects->groupBy(fn (Project $project) => $project->created_at?->year)
            ->map->count()
            ->sortKeys();
        $yearStart = 28;
        $sheet->fromArray(['Creation Year', 'Projects'], null, "G{$yearStart}");
        $yearRow = $yearStart + 1;
        foreach ($years as $year => $count) {
            $sheet->fromArray([(int) $year, $count], null, "G{$yearRow}");
            $yearRow++;
        }
        $this->styleHelperTable($sheet, "G{$yearStart}:H" . max($yearStart + 1, $yearRow - 1));

        $statusData = collect(ProjectStateEnum::cases())->mapWithKeys(
            fn (ProjectStateEnum $state) => [
                $state->value => [
                    'value' => $projects->where('state', $state)->count(),
                    'color' => ltrim($state->color(), '#'),
                ],
            ]
        )->all();
        $this->addDashboardImage(
            $sheet,
            $this->createDoughnutImage('Projects by Status', $statusData),
            'A9',
            520,
            320
        );
        $this->addDashboardImage(
            $sheet,
            $this->createBarImage('Top Plants by Projects', $plants->all()),
            'G9',
            520,
            320
        );
        $this->addDashboardImage(
            $sheet,
            $this->createLineImage('Projects by Creation Year', $years->all()),
            'A28',
            720,
            320
        );

        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setWidth(13);
        }
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->freezePane('A4');
    }

    private function styleDetailSheet(Worksheet $sheet, string $range): void
    {
        $sheet->setShowGridlines(false);
        $sheet->getStyle(explode(':', $range)[0] . ':' . preg_replace('/\d+$/', '1', explode(':', $range)[1]))
            ->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::DARK_BLUE]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        $sheet->getStyle($range)->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_HAIR)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('E2E8F0'));
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($range)->getAlignment()->setWrapText(false);
        $sheet->getDefaultColumnDimension()->setWidth(16);
    }

    private function addDashboardImage(
        Worksheet $sheet,
        string $path,
        string $coordinates,
        int $width,
        int $height
    ): void {
        $drawing = new Drawing();
        $drawing->setName(pathinfo($path, PATHINFO_FILENAME));
        $drawing->setPath($path);
        $drawing->setCoordinates($coordinates);
        $drawing->setWidth($width);
        $drawing->setHeight($height);
        $drawing->setWorksheet($sheet);
    }

    private function createDoughnutImage(string $title, array $data): string
    {
        [$image, $colors] = $this->createChartCanvas($title, 760, 440);
        $total = max(1, array_sum(array_column($data, 'value')));
        $startAngle = -90.0;
        $index = 0;

        foreach ($data as $label => $item) {
            $value = (int) $item['value'];
            $endAngle = $startAngle + (($value / $total) * 360);
            $sliceColor = $this->allocateHexColor($image, $item['color']);
            if ($value > 0) {
                imagefilledarc($image, 220, 235, 270, 270, (int) $startAngle, (int) $endAngle, $sliceColor, IMG_ARC_PIE);
            }

            $percentage = round(($value / $total) * 100);
            imagefilledrectangle($image, 410, 120 + ($index * 60), 430, 140 + ($index * 60), $sliceColor);
            imagestring($image, 5, 445, 116 + ($index * 60), "{$label}: {$value} ({$percentage}%)", $colors['text']);
            $startAngle = $endAngle;
            $index++;
        }

        imagefilledellipse($image, 220, 235, 135, 135, $colors['white']);
        imagestring($image, 5, 187, 224, (string) array_sum(array_column($data, 'value')), $colors['dark']);
        imagestring($image, 3, 184, 245, 'projects', $colors['muted']);

        return $this->saveChartImage($image, 'status');
    }

    private function createBarImage(string $title, array $data): string
    {
        [$image, $colors] = $this->createChartCanvas($title, 760, 440);
        $data = array_slice($data, 0, 8, true);
        $max = max(1, $data === [] ? 1 : max($data));
        $row = 0;

        if ($data === []) {
            imagestring($image, 5, 320, 220, 'No data', $colors['muted']);
        }

        foreach ($data as $label => $value) {
            $y = 95 + ($row * 40);
            $barWidth = (int) (($value / $max) * 410);
            $shortLabel = mb_strimwidth((string) $label, 0, 25, '...');
            imagestring($image, 3, 25, $y + 4, $shortLabel, $colors['text']);
            imagefilledrectangle($image, 220, $y, 220 + $barWidth, $y + 24, $colors['blue']);
            imagestring($image, 4, 230 + $barWidth, $y + 3, (string) $value, $colors['dark']);
            $row++;
        }

        return $this->saveChartImage($image, 'plants');
    }

    private function createLineImage(string $title, array $data): string
    {
        [$image, $colors] = $this->createChartCanvas($title, 980, 440);
        $left = 80;
        $right = 920;
        $top = 90;
        $bottom = 350;
        imageline($image, $left, $top, $left, $bottom, $colors['grid']);
        imageline($image, $left, $bottom, $right, $bottom, $colors['grid']);

        if ($data === []) {
            imagestring($image, 5, 440, 220, 'No data', $colors['muted']);

            return $this->saveChartImage($image, 'years');
        }

        $max = max(1, max($data));
        $count = count($data);
        $points = [];
        $index = 0;
        foreach ($data as $year => $value) {
            $x = $count === 1 ? ($left + $right) / 2 : $left + (($right - $left) * $index / ($count - 1));
            $y = $bottom - (($value / $max) * ($bottom - $top - 20));
            $points[] = [(int) $x, (int) $y, (int) $value, (string) $year];
            $index++;
        }

        for ($index = 1; $index < count($points); $index++) {
            imageline($image, $points[$index - 1][0], $points[$index - 1][1], $points[$index][0], $points[$index][1], $colors['blue']);
            imageline($image, $points[$index - 1][0], $points[$index - 1][1] + 1, $points[$index][0], $points[$index][1] + 1, $colors['blue']);
        }

        foreach ($points as [$x, $y, $value, $year]) {
            imagefilledellipse($image, $x, $y, 12, 12, $colors['blue']);
            imagestring($image, 4, $x - 8, $y - 25, (string) $value, $colors['dark']);
            imagestring($image, 4, $x - 18, $bottom + 12, $year, $colors['text']);
        }

        return $this->saveChartImage($image, 'years');
    }

    private function createChartCanvas(string $title, int $width, int $height): array
    {
        $image = imagecreatetruecolor($width, $height);
        imageantialias($image, true);
        $colors = [
            'white' => imagecolorallocate($image, 255, 255, 255),
            'dark' => imagecolorallocate($image, 15, 23, 42),
            'text' => imagecolorallocate($image, 51, 65, 85),
            'muted' => imagecolorallocate($image, 100, 116, 139),
            'grid' => imagecolorallocate($image, 203, 213, 225),
            'blue' => imagecolorallocate($image, 37, 99, 235),
        ];
        imagefill($image, 0, 0, $colors['white']);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $colors['grid']);
        imagestring($image, 5, 24, 22, $title, $colors['dark']);

        return [$image, $colors];
    }

    private function allocateHexColor(\GdImage $image, string $hex): int
    {
        $hex = $this->normalizeColor($hex);

        return imagecolorallocate(
            $image,
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }

    private function saveChartImage(\GdImage $image, string $name): string
    {
        $directory = storage_path('app/private/exports');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . '/' . $name . '-' . uniqid('', true) . '.png';
        imagepng($image, $path, 6);
        imagedestroy($image);
        $this->temporaryImages[] = $path;

        return $path;
    }

    private function styleHelperTable(Worksheet $sheet, string $range): void
    {
        $start = explode(':', $range)[0];
        $headerRow = preg_replace('/[A-Z]+/', '', $start);
        $startColumn = preg_replace('/\d+/', '', $start);
        $endColumn = preg_replace('/\d+/', '', explode(':', $range)[1]);
        $sheet->getStyle("{$startColumn}{$headerRow}:{$endColumn}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::DARK_BLUE]],
        ]);
        $sheet->getStyle($range)->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CBD5E1'));
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

        return (($red * 299) + ($green * 587) + ($blue * 114)) / 1000 > 150
            ? '0F172A'
            : 'FFFFFF';
    }
}
