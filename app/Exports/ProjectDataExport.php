<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectDataExport
{
    private const HEADERS = [
        'area' => 'Area', 'group_1' => 'Group 1', 'group_2' => 'Group 2',
        'description' => 'Description', 'general_classification' => 'Classification',
        'item_type' => 'Item type', 'unit' => 'Unit', 'qty' => 'Qty',
        'unit_price' => 'Unit price', 'global_price' => 'Budgeted $',
        'global_price_euros' => 'Budgeted €', 'stage' => 'Stage',
        'real_value' => 'Real $', 'real_value_euros' => 'Real €',
        'percentage' => 'Percentage',
        'executed_dollars' => 'Executed $', 'executed_euros' => 'Executed €',
        'booked' => 'Booked $', 'booked_euros' => 'Booked €', 'supplier' => 'Supplier',
        'code' => 'Code', 'order_no' => 'Order no.', 'input_num' => 'Input no.',
        'observations' => 'Observations',
    ];

    private const NUMERIC_COLUMNS = [
        'qty', 'unit_price', 'global_price', 'global_price_euros', 'real_value',
        'real_value_euros', 'percentage', 'executed_dollars',
        'executed_euros', 'booked', 'booked_euros',
    ];

    public function download(Project $project, Collection $rows, array $columns): BinaryFileResponse
    {
        $columns = array_values(array_intersect($columns, array_keys(self::HEADERS)));
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Project Data');
        $sheet->setShowGridlines(false);
        $sheet->fromArray(array_map(fn (string $column): string => self::HEADERS[$column], $columns), null, 'A1');

        $rowNumber = 2;
        foreach ($rows as $row) {
            $sheet->fromArray(array_map(
                fn (string $column): mixed => in_array($column, self::NUMERIC_COLUMNS, true)
                    ? (float) $row->{$column}
                    : $row->{$column},
                $columns
            ), null, "A{$rowNumber}");
            $rowNumber++;
        }

        $lastRow = max(2, $rowNumber - 1);
        $lastColumn = Coordinate::stringFromColumnIndex(max(count($columns), 1));
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        foreach ($columns as $index => $column) {
            $letter = Coordinate::stringFromColumnIndex($index + 1);
            if (in_array($column, self::NUMERIC_COLUMNS, true)) {
                $sheet->getStyle("{$letter}2:{$letter}{$lastRow}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            }
            $sheet->getColumnDimension($letter)->setWidth(match ($column) {
                'description' => 50,
                'general_classification', 'supplier' => 30,
                'observations' => 40,
                default => 16,
            });
        }

        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
        $sheet->freezePane('A2');
        $spreadsheet->getProperties()
            ->setCreator('DA Imperium')
            ->setTitle("Project Data - {$project->name}")
            ->setSubject('Project Data Export');

        $directory = storage_path('app/private/exports');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $path = $directory.'/project-data-'.$project->getKey().'-'.uniqid('', true).'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()->download(
            $path,
            'project-'.$project->getKey().'-'.Str::slug($project->name).'-data.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }
}
