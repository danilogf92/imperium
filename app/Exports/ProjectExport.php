<?php

namespace App\Exports;

use App\Enums\ProjectPermissionEnum;
use App\Models\Project;
use App\Models\User;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectExport
{
    private const BASE_HEADERS = [
        'id',
        'name',
        'pda_code',
        'rate',
        'state',
        'upload_pda',
        'investments',
        'justification',
        'classification_of_investments',
    ];

    public function download(User $user): BinaryFileResponse
    {
        $directory = storage_path('app/private/exports');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory.'/projects-'.uniqid('', true).'.xlsx';
        $projects = Project::query()
            ->with([
                'projectMilestones' => fn ($query) => $query
                    ->with('milestone:id,name,code,color')
                    ->orderBy('cycle_year')
                    ->orderBy('month')
                    ->orderBy('sequence'),
            ])
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(
                    ProjectPermissionEnum::Export
                )
                    ->select('companies.id')
                    ->reorder()
            )
            ->orderBy('id')
            ->get();

        $timelineYears = $projects
            ->flatMap(fn (Project $project) => $project->projectMilestones->pluck('cycle_year'))
            ->unique()
            ->sort()
            ->values();
        $monthLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $timelineHeaders = $timelineYears
            ->flatMap(fn (int $year) => collect($monthLabels)->map(fn (string $month) => "{$year} {$month}"))
            ->all();
        $headers = [...self::BASE_HEADERS, ...$timelineHeaders];

        $writer = new Writer();
        $writer->openToFile($path);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Worksheet');
        $sheet->setSheetView(
            (new SheetView())->setFreezeRow(2)
        );
        $sheet->setAutoFilter(
            new AutoFilter(0, 1, count($headers) - 1, $projects->count() + 1)
        );

        $this->setColumnWidths($sheet, count($timelineHeaders));

        $writer->addRow(
            Row::fromValues($headers, $this->headerStyle())
                ->setHeight(24)
        );

        $rateStyle = (new Style())
            ->setFormat('0.00')
            ->setCellAlignment(CellAlignment::RIGHT);

        foreach ($projects as $project) {
            $stateStyle = (new Style())
                ->setFontBold()
                ->setFontColor(ltrim($project->state->textColor(), '#'))
                ->setBackgroundColor(ltrim($project->state->softColor(), '#'))
                ->setCellAlignment(CellAlignment::CENTER)
                ->setCellVerticalAlignment('center');

            $values = [
                (int) $project->id,
                $project->name,
                $project->pda_code,
                (float) $project->rate,
                $project->state->value,
                filled($project->upload_pda) ? 1 : 0,
                $project->investments->value,
                $project->justification->value,
                $project->classification_of_investments->value,
            ];
            $columnStyles = [
                3 => $rateStyle,
                4 => $stateStyle,
            ];
            $columnIndex = count(self::BASE_HEADERS);

            foreach ($timelineYears as $year) {
                for ($month = 1; $month <= 12; $month++) {
                    $cellMilestones = $project->projectMilestones
                        ->where('cycle_year', $year)
                        ->where('month', $month);
                    $values[] = $cellMilestones
                        ->pluck('milestone.code')
                        ->filter()
                        ->implode(', ');

                    if ($cellMilestones->isNotEmpty()) {
                        $backgroundColor = $this->normalizeColor(
                            $cellMilestones->first()->milestone?->color
                        );
                        $columnStyles[$columnIndex] = (new Style())
                            ->setFontBold()
                            ->setFontColor($this->contrastColor($backgroundColor))
                            ->setBackgroundColor($backgroundColor)
                            ->setCellAlignment(CellAlignment::CENTER)
                            ->setCellVerticalAlignment('center')
                            ->setShouldWrapText();
                    }

                    $columnIndex++;
                }
            }

            $writer->addRow(
                Row::fromValuesWithStyles(
                    $values,
                    $this->bodyStyle(),
                    $columnStyles
                )->setHeight(20)
            );
        }

        $writer->close();

        return response()
            ->download(
                $path,
                'projects.xlsx',
                [
                    'Content-Type' =>
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            )
            ->deleteFileAfterSend(true);
    }

    private function headerStyle(): Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::DARK_BLUE)
            ->setCellAlignment(CellAlignment::CENTER);
    }

    private function bodyStyle(): Style
    {
        return (new Style())
            ->setCellVerticalAlignment('center');
    }

    private function setColumnWidths(mixed $sheet, int $timelineColumnCount): void
    {
        $widths = [10, 55, 32, 12, 16, 14, 34, 22, 30];

        foreach ($widths as $index => $width) {
            $sheet->setColumnWidth($width, $index + 1);
        }

        for ($index = 0; $index < $timelineColumnCount; $index++) {
            $sheet->setColumnWidth(14, count($widths) + $index + 1);
        }
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
