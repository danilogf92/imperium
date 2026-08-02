<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectDataExport
{
    public function download(Project $project, Collection $rows): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Project Data');
        $sheet->setShowGridlines(false);

        $headers = [
            'ID',
            'Project ID',
            'Area',
            'Group 1',
            'Group 2',
            'Description',
            'General Classification',
            'Item Type',
            'Unit',
            'Qty',
            'Unit Price',
            'Global Price $',
            'Stage',
            'Real Value $',
            'Committed',
            'Percentage',
            'Executed $',
            'Executed €',
            'Supplier',
            'Code',
            'Order No.',
            'Input No.',
            'Observations',
            'Booked $',
            'Global Price €',
            'Real Value €',
            'Booked €',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $rowNumber = 2;

        foreach ($rows as $row) {
            $sheet->fromArray([
                $row->id,
                $row->project_id,
                $row->area,
                $row->group_1,
                $row->group_2,
                $row->description,
                $row->general_classification,
                $row->item_type,
                $row->unit,
                (float) $row->qty,
                (float) $row->unit_price,
                (float) $row->global_price,
                $row->stage,
                (float) $row->real_value,
                (float) $row->committed,
                (float) $row->percentage,
                (float) $row->executed_dollars,
                (float) $row->executed_euros,
                $row->supplier,
                $row->code,
                $row->order_no,
                $row->input_num,
                $row->observations,
                (float) $row->booked,
                (float) $row->global_price_euros,
                (float) $row->real_value_euros,
                (float) $row->booked_euros,
            ], null, "A{$rowNumber}");

            $rowNumber++;
        }

        $lastRow = max(2, $rowNumber - 1);

        $sheet->getStyle('A1:AA1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        foreach (
            [
                'J', // Qty
                'K', // Unit Price
                'L', // Global Price $
                'N', // Real Value $
                'O', // Committed
                'Q', // Executed $
                'R', // Executed €
                'X', // Booked $
                'Y', // Global Price €
                'Z', // Real Value €
                'AA', // Booked €
            ] as $column
        ) {
            $sheet->getStyle("{$column}2:{$column}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        $sheet->getStyle("P2:P{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('0.00%');

        $sheet->getDefaultColumnDimension()->setWidth(16);

        $sheet->getColumnDimension('F')->setWidth(50); // Description
        $sheet->getColumnDimension('G')->setWidth(30); // Classification
        $sheet->getColumnDimension('S')->setWidth(28); // Supplier
        $sheet->getColumnDimension('W')->setWidth(40); // Observations

        $sheet->setAutoFilter("A1:AA{$lastRow}");
        $sheet->freezePane('A2');

        $spreadsheet->getProperties()
            ->setCreator('DA Imperium')
            ->setTitle("Project Data - {$project->name}")
            ->setSubject('Project Data Export');

        $directory = storage_path('app/private/exports');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . '/project-data-' . $project->getKey() . '-' . uniqid('', true) . '.xlsx';

        (new Xlsx($spreadsheet))->save($path);

        $spreadsheet->disconnectWorksheets();

        return response()->download(
            $path,
            'project-' . $project->getKey() . '-' . Str::slug($project->name) . '-data.xlsx',
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        )->deleteFileAfterSend(true);
    }
}
