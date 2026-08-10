<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Comment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProjectDataTemplateGenerator
{
    public const HEADERS = [
        'id',
        'area',
        'group 1',
        'group 2',
        'description',
        'general classification',
        'item type',
        'stage',
        'unit',
        'qty',
        'unit price',
        'global price',
        'real',
        'booked',
        'percentage',
        'executed dollars',
        'executed euros',
        'supplier',
        'code',
        'order no',
        'input num',
        'observations',
    ];

    public function generate(string $templatePath, string $samplePath): void
    {
        $this->ensureDirectory(dirname($templatePath));
        $this->ensureDirectory(dirname($samplePath));

        $this->saveWorkbook($templatePath, []);
        $this->saveWorkbook($samplePath, $this->sampleRows());
    }

    private function saveWorkbook(string $path, array $rows): void
    {
        $spreadsheet = new Spreadsheet();
        $dataSheet = $spreadsheet->getActiveSheet();
        $dataSheet->setTitle('Project Data');
        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');

        $this->buildDataSheet($dataSheet, $rows);
        $this->buildInstructionsSheet($instructions);

        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getProperties()
            ->setCreator('DA Imperium')
            ->setTitle('Project Data Import Template')
            ->setSubject('Template for importing project data into DA Imperium')
            ->setDescription('Keep the header names unchanged and enter one item per row.');

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
    }

    private function buildDataSheet(Worksheet $sheet, array $rows): void
    {
        $sheet->setShowGridlines(false);
        $sheet->fromArray(self::HEADERS, null, 'A1');

        if ($rows !== []) {
            $sheet->fromArray($rows, null, 'A2');
        }

        $lastRow = max(2, count($rows) + 1);
        $sheet->getStyle('A1:V1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '2563EB'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        if ($rows !== []) {
            $sheet->getStyle("A2:V{$lastRow}")->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_HAIR,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);
        }

        foreach (['J', 'K', 'L', 'M', 'N', 'P', 'Q'] as $column) {
            $sheet->getStyle("{$column}2:{$column}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }
        $sheet->getStyle("O2:O{$lastRow}")->getNumberFormat()->setFormatCode('0.00');

        $widths = [
            'A' => 10, 'B' => 22, 'C' => 18, 'D' => 22, 'E' => 48, 'F' => 36,
            'G' => 18, 'H' => 18, 'I' => 12, 'J' => 12, 'K' => 16, 'L' => 16,
            'M' => 14, 'N' => 14, 'O' => 14, 'P' => 18, 'Q' => 16, 'R' => 24,
            'S' => 16, 'T' => 16, 'U' => 16, 'V' => 40,
        ];
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->freezePane('B2');
        $sheet->setAutoFilter("A1:V{$lastRow}");

        $sheet->getStyle('A2:A5001')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle('A2:A5001')->getFont()->getColor()->setRGB('64748B');

        $sheet->getComment('A1')->setAuthor('DA Imperium');
        $sheet->getComment('A1')->getText()->createText(
            'Optional. This value is ignored during import; the database creates a new ID.'
        );
        $sheet->getComment('O1')->setAuthor('DA Imperium');
        $sheet->getComment('O1')->getText()->createText(
            'Enter a number from 0 to 100. Example: 25 means 25%.'
        );
        $sheet->getComment('P1')->setAuthor('DA Imperium');
        $sheet->getComment('P1')->getText()->createText(
            'If only one executed currency is entered, the other is calculated using the project rate.'
        );

        $percentageValidation = new DataValidation();
        $percentageValidation->setType(DataValidation::TYPE_DECIMAL);
        $percentageValidation->setOperator(DataValidation::OPERATOR_BETWEEN);
        $percentageValidation->setFormula1('0');
        $percentageValidation->setFormula2('100');
        $percentageValidation->setAllowBlank(true);
        $percentageValidation->setShowErrorMessage(true);
        $percentageValidation->setErrorTitle('Invalid percentage');
        $percentageValidation->setError('Enter a number between 0 and 100.');
        $sheet->setDataValidation('O2:O5001', $percentageValidation);

        foreach (['J', 'K', 'L', 'M', 'N', 'P', 'Q'] as $column) {
            $numericValidation = new DataValidation();
            $numericValidation->setType(DataValidation::TYPE_DECIMAL);
            $numericValidation->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL);
            $numericValidation->setFormula1('0');
            $numericValidation->setAllowBlank(true);
            $numericValidation->setShowErrorMessage(true);
            $numericValidation->setErrorTitle('Invalid number');
            $numericValidation->setError('Enter zero or a positive numeric value.');
            $sheet->setDataValidation("{$column}2:{$column}5001", $numericValidation);
        }
    }

    private function buildInstructionsSheet(Worksheet $sheet): void
    {
        $sheet->setShowGridlines(false);
        $sheet->mergeCells('A1:F2');
        $sheet->setCellValue('A1', 'PROJECT DATA IMPORT — INSTRUCTIONS');
        $sheet->getStyle('A1:F2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $instructions = [
            ['Rule', 'Description'],
            ['1', 'Use the Project Data sheet and keep every header name unchanged.'],
            ['2', 'Enter one project item per row. Completely empty rows are ignored.'],
            ['3', 'The ID column is optional and ignored; DA Imperium creates new IDs.'],
            ['4', 'Required columns: area, description, qty, unit price, and global price.'],
            ['5', 'Numeric columns must contain numbers only. Percentage accepts values from 0 to 100.'],
            ['6', 'Dollar values are converted to euros using the rate configured in the selected project.'],
            ['7', 'If the project already contains data, the import appends rows and requires confirmation.'],
            ['8', 'Maximum file size: 20 MB. Maximum rows per import: 5,000.'],
        ];
        $sheet->fromArray($instructions, null, 'A4');
        $sheet->getStyle('A4:B4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
        ]);
        $sheet->getStyle('A5:B12')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('B5:B12')->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(95);
        foreach (range(5, 12) as $row) {
            $sheet->getRowDimension($row)->setRowHeight(30);
        }
        $sheet->freezePane('A5');
    }

    private function sampleRows(): array
    {
        return [
            [
                null, 'Mechanical', 'Local', 'Pumps', 'Centrifugal pump installation',
                'Production equipment', 'Asset', 'Budget', 'unit', 2, 12500, 25000,
                5000, 2500, 20, 5000, 0, 'Sample Industrial Supplier', 'PMP-001',
                'PO-TEST-001', 'IN-001', 'Sample row for import testing',
            ],
            [
                null, 'Electrical', 'Local', 'Control', 'Electrical control panel',
                'Electrical systems', 'Asset', 'Purchase Order', 'unit', 1, 8400, 8400,
                0, 4200, 0, 0, 0, 'Demo Automation Ltd.', 'ELC-002',
                'PO-TEST-002', 'IN-002', 'Values are fictitious',
            ],
            [
                null, 'Civil Works', 'Local', 'Foundations', 'Concrete equipment foundation',
                'Civil infrastructure', 'Service', 'Work in Progress', 'm3', 18, 315, 5670,
                1500, 3000, 35, 1500, 0, 'Example Construction Co.', 'CIV-003',
                'PO-TEST-003', 'IN-003', null,
            ],
            [
                null, 'IT', 'Imported', 'Network', 'Industrial network switches',
                'Technology', 'Asset', 'Booked', 'unit', 6, 950, 5700,
                5700, 5700, 100, 5700, 0, 'Test Network Vendor', 'NET-004',
                'PO-TEST-004', 'IN-004', 'Completed sample item',
            ],
            [
                null, 'Safety', 'Local', 'Protection', 'Machine safety guarding',
                'Safety improvement', 'Service', 'Forecast', 'lot', 1, 3200, 3200,
                0, 0, 0, 0, 0, 'Safety Demo Services', 'SAF-005',
                null, null, 'Row without an order number',
            ],
        ];
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}
