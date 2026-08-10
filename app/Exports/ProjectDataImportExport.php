<?php

namespace App\Exports;

use App\Models\Project;
use App\Services\ProjectDataTemplateGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ProjectDataImportExport
{
    private const FIELDS = [
        'id', 'area', 'group_1', 'group_2', 'description',
        'general_classification', 'item_type', 'stage', 'unit', 'qty',
        'unit_price', 'global_price', 'real_value', 'booked', 'percentage',
        'executed_dollars', 'executed_euros', 'supplier', 'code', 'order_no',
        'input_num', 'observations',
    ];

    private const NUMERIC_FIELDS = [
        'qty', 'unit_price', 'global_price', 'real_value', 'booked',
        'percentage', 'executed_dollars', 'executed_euros',
    ];

    public function download(Project $project, Collection $rows): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Project Data');
        $sheet->setShowGridlines(false);
        $sheet->fromArray(ProjectDataTemplateGenerator::HEADERS, null, 'A1');

        $rowNumber = 2;
        foreach ($rows as $row) {
            $sheet->fromArray(array_map(
                fn (string $field): mixed => in_array($field, self::NUMERIC_FIELDS, true)
                    ? (float) ($row->{$field} ?? 0)
                    : $row->{$field},
                self::FIELDS
            ), null, "A{$rowNumber}");
            $rowNumber++;
        }

        $lastRow = max(2, $rowNumber - 1);
        $lastColumn = Coordinate::stringFromColumnIndex(count(self::FIELDS));
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '2563EB']],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        foreach (self::FIELDS as $index => $field) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            if (in_array($field, self::NUMERIC_FIELDS, true)) {
                $sheet->getStyle("{$column}2:{$column}{$lastRow}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            }
            $sheet->getColumnDimension($column)->setWidth(match ($field) {
                'description' => 48,
                'general_classification' => 36,
                'supplier' => 24,
                'observations' => 40,
                default => 16,
            });
        }

        $sheet->freezePane('B2');
        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
        $spreadsheet->getProperties()
            ->setCreator('DA Imperium')
            ->setTitle("Project Data Import - {$project->name}")
            ->setSubject('Project data prepared for re-import');

        $directory = storage_path('app/private/exports');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory.'/project-data-import-'.$project->getKey().'-'.uniqid('', true).'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()->download(
            $path,
            'project-'.$project->getKey().'-'.Str::slug($project->name).'-import-ready.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }
}
