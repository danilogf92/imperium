<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectDetailDashboardExport
{
    private const BLUE = '2563EB';

    private const DARK_BLUE = '1E3A8A';

    private const VALUE_COLUMNS = [
        'global_price_euros',
        'real_value_euros',
        'booked_euros',
        'executed_euros',
    ];

    public function download(
        Project $project,
        string $groupColumn,
        string $valueColumn,
        string $currency,
        float $conversionRate
    ): BinaryFileResponse {
        abort_unless(in_array($groupColumn, [
            'area',
            'group_1',
            'group_2',
            'general_classification',
            'item_type',
            'stage',
            'supplier',
        ], true), 422);
        abort_unless(in_array($valueColumn, self::VALUE_COLUMNS, true), 422);

        $currency = $currency === 'dollar' ? 'dollar' : 'euro';
        $conversionRate = is_finite($conversionRate) && $conversionRate > 0
            ? $conversionRate
            : 1;

        $project->loadMissing('company:id,company_name');
        $rows = $project->data()->orderBy('id')->get();

        $spreadsheet = new Spreadsheet();
        $summary = $spreadsheet->getActiveSheet()->setTitle('Summary');
        $grouping = $spreadsheet->createSheet()->setTitle('Grouping');
        $detail = $spreadsheet->createSheet()->setTitle('Project Data');

        $this->buildSummarySheet($summary, $project, $rows, $currency, $conversionRate);
        $this->buildGroupingSheet(
            $grouping,
            $rows,
            $groupColumn,
            $valueColumn,
            $currency,
            $conversionRate
        );
        $this->buildDetailSheet($detail, $rows, $currency, $conversionRate);

        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getProperties()
            ->setCreator('DA Imperium')
            ->setTitle("Project Dashboard - {$project->name}")
            ->setSubject('Project dashboard export');

        $directory = storage_path('app/private/exports');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory.'/project-'.$project->getKey().'-dashboard-'.uniqid('', true).'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        $name = 'project-'.$project->getKey().'-'.Str::slug($project->name).'-dashboard.xlsx';

        return response()->download(
            $path,
            $name,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function buildSummarySheet(
        Worksheet $sheet,
        Project $project,
        $rows,
        string $currency,
        float $conversionRate
    ): void {
        $symbol = $currency === 'dollar' ? '$' : '€';
        $sheet->setShowGridlines(false);
        $sheet->mergeCells('A1:F2');
        $sheet->setCellValue('A1', 'PROJECT DASHBOARD');
        $sheet->getStyle('A1:F2')->applyFromArray($this->titleStyle());

        $sheet->fromArray([
            ['Project', $project->name],
            ['Plant', $project->company?->company_name],
            ['PDA Code', $project->pda_code],
            ['Status', $project->state?->value],
            ['Currency', $currency === 'dollar' ? 'USD' : 'EUR'],
        ], null, 'A4');

        $metrics = [
            ['Budgeted', $rows->sum('global_price_euros') * $conversionRate],
            ['Executed', $rows->sum('executed_euros') * $conversionRate],
            ['Assigned', $rows->sum('booked_euros') * $conversionRate],
            ['Booked (Real SAP)', $rows->sum('real_value_euros') * $conversionRate],
            ['Committed', ($rows->sum('booked_euros') - $rows->sum('real_value_euros')) * $conversionRate],
        ];
        $sheet->fromArray(['Metric', "Value ({$symbol})"], null, 'D4');
        $sheet->fromArray($metrics, null, 'D5');

        $this->styleHeader($sheet, 'D4:E4');
        $sheet->getStyle('D5:E9')->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('E2E8F0'));
        $sheet->getStyle('E5:E9')->getNumberFormat()->setFormatCode($this->moneyFormat($symbol));
        $sheet->getStyle('A4:A8')->getFont()->setBold(true)->getColor()->setRGB(self::DARK_BLUE);
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(46);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(22);
    }

    private function buildGroupingSheet(
        Worksheet $sheet,
        $rows,
        string $groupColumn,
        string $valueColumn,
        string $currency,
        float $conversionRate
    ): void {
        $symbol = $currency === 'dollar' ? '$' : '€';
        $groups = $rows
            ->groupBy(fn ($row) => filled($row->{$groupColumn}) ? $row->{$groupColumn} : 'Unspecified')
            ->map(fn ($items) => $items->sum($valueColumn) * $conversionRate)
            ->sortDesc();

        $valueLabel = match ($valueColumn) {
            'booked_euros' => 'Assigned',
            'real_value_euros' => 'Booked (Real SAP)',
            default => Str::headline($valueColumn),
        };
        $sheet->fromArray([
            [Str::headline($groupColumn), $valueLabel." ({$symbol})"],
            ...$groups->map(fn ($value, $label) => [$label, $value])->values()->all(),
        ], null, 'A1');

        $lastRow = max(2, $groups->count() + 1);
        $this->styleHeader($sheet, 'A1:B1');
        $sheet->getStyle("B2:B{$lastRow}")->getNumberFormat()->setFormatCode($this->moneyFormat($symbol));
        $sheet->getColumnDimension('A')->setWidth(42);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->setAutoFilter("A1:B{$lastRow}");
        $sheet->freezePane('A2');
    }

    private function buildDetailSheet(
        Worksheet $sheet,
        $rows,
        string $currency,
        float $conversionRate
    ): void {
        $symbol = $currency === 'dollar' ? '$' : '€';
        $headers = [
            'Area', 'Group 1', 'Group 2', 'Description', 'Classification', 'Item Type',
            'Stage', 'Supplier', 'Order No.', 'Budgeted', 'Executed', 'Assigned', 'Booked (Real SAP)', 'Committed',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $rowNumber = 2;
        foreach ($rows as $row) {
            $sheet->fromArray([
                $row->area,
                $row->group_1,
                $row->group_2,
                $row->description,
                $row->general_classification,
                $row->item_type,
                $row->stage,
                $row->supplier,
                $row->order_no,
                (float) $row->global_price_euros * $conversionRate,
                (float) $row->executed_euros * $conversionRate,
                (float) $row->booked_euros * $conversionRate,
                (float) $row->real_value_euros * $conversionRate,
                ((float) $row->booked_euros - (float) $row->real_value_euros) * $conversionRate,
            ], null, "A{$rowNumber}");
            $rowNumber++;
        }

        $lastRow = max(2, $rowNumber - 1);
        $this->styleHeader($sheet, 'A1:N1');
        $sheet->getStyle("J2:N{$lastRow}")->getNumberFormat()->setFormatCode($this->moneyFormat($symbol));
        $sheet->getDefaultColumnDimension()->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(48);
        $sheet->getColumnDimension('E')->setWidth(28);
        $sheet->getColumnDimension('H')->setWidth(28);
        $sheet->getColumnDimension('L')->setWidth(28);
        $sheet->getColumnDimension('M')->setWidth(28);
        $sheet->setAutoFilter("A1:N{$lastRow}");
        $sheet->freezePane('A2');
    }

    private function titleStyle(): array
    {
        return [
            'font' => ['bold' => true, 'size' => 22, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::DARK_BLUE]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
    }

    private function styleHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::BLUE]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    private function moneyFormat(string $symbol): string
    {
        return '"'.$symbol.'" #,##0.00';
    }
}
