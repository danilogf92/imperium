<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectResumeExport
{
    public function download(Collection $rows, array $filters, string $currencySymbol = "\u{20AC}"): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Annual Resume');
        $sheet->setShowGridlines(false);
        $sheet->mergeCells('A1:G2');
        $sheet->setCellValue('A1', 'ANNUAL PROJECT FINANCIAL RESUME');
        $sheet->getStyle('A1:G2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 20, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $filterRow = 4;
        foreach ($filters as $label => $value) {
            $sheet->setCellValue("A{$filterRow}", $label);
            $sheet->setCellValue("B{$filterRow}", $value);
            $filterRow++;
        }
        $sheet->getStyle("A4:A".($filterRow - 1))->getFont()->setBold(true);

        $headerRow = $filterRow + 1;
        $headers = [
            'Year', 'Number of Projects', "Budgeted {$currencySymbol}", "Approved {$currencySymbol}",
            "Booked {$currencySymbol}", "Committed {$currencySymbol}", "Available {$currencySymbol}",
        ];
        $sheet->fromArray($headers, null, "A{$headerRow}");
        $sheet->getStyle("A{$headerRow}:H{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $rowNumber = $headerRow + 1;
        foreach ($rows as $row) {
            $sheet->fromArray([
                $row['year'],
                $row['project_count'],
                $row['budgeted'],
                $row['approved'],
                $row['booked'],
                $row['committed'],
                $row['available'],
            ], null, "A{$rowNumber}");
            $rowNumber++;
        }

        $lastRow = max($headerRow + 1, $rowNumber - 1);
        $sheet->getStyle("C".($headerRow + 1).":G{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('"'.$currencySymbol.'" #,##0.00');
        $sheet->getStyle("A{$headerRow}:G{$lastRow}")->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_HAIR)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('CBD5E1'));
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(22);
        foreach (range('C', 'G') as $column) {
            $sheet->getColumnDimension($column)->setWidth(20);
        }
        $sheet->setAutoFilter("A{$headerRow}:G{$lastRow}");
        $sheet->freezePane('C'.($headerRow + 1));

        $directory = storage_path('app/private/exports');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $path = $directory.'/annual-project-resume-'.uniqid('', true).'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()->download(
            $path,
            'annual-project-resume.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }
}
