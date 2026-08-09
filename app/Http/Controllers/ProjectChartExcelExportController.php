<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectChartExcelExportController extends Controller
{
    private const CHART_WIDTH_PX = 590; // 6.15 inches at 96 DPI.

    private const CHART_HEIGHT_PX = 360; // 3.75 inches at 96 DPI.

    public function __invoke(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'filename' => ['required', 'string', 'max:150'],
            'image' => ['required', 'string'],
            'rows' => ['required', 'array', 'min:2', 'max:1000'],
            'rows.*' => ['array', 'max:100'],
            'rows.*.*' => ['nullable'],
        ]);

        abort_unless(
            preg_match('/^data:image\/png;base64,(.+)$/s', $validated['image'], $matches) === 1,
            422,
            'Invalid chart image.'
        );

        $image = base64_decode($matches[1], true);
        abort_if($image === false || strlen($image) > 10 * 1024 * 1024, 422, 'Invalid chart image.');

        $directory = storage_path('app/private/exports');
        File::ensureDirectoryExists($directory);
        $token = Str::uuid()->toString();
        $imagePath = "{$directory}/chart-{$token}.png";
        $outputPath = "{$directory}/chart-{$token}.xlsx";
        File::put($imagePath, $image);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Chart and data');
        $sheet->setShowGridlines(false);
        $sheet->mergeCells('A1:H1')->setCellValue('A1', $validated['title']);
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $drawing = new Drawing();
        $drawing->setName($validated['title']);
        $drawing->setPath($imagePath);
        $drawing->setCoordinates('A3');
        $drawing->setResizeProportional(true);
        $drawing->setWidthAndHeight(self::CHART_WIDTH_PX, self::CHART_HEIGHT_PX);
        $drawing->setWorksheet($sheet);

        $tableRow = 1;
        $tableColumn = 12; // Column L.
        foreach ($validated['rows'] as $rowIndex => $row) {
            foreach (array_values($row) as $columnIndex => $value) {
                $sheet->setCellValue([$tableColumn + $columnIndex, $tableRow + $rowIndex], $value);
            }
        }

        $columnCount = max(array_map('count', $validated['rows']));
        $firstColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($tableColumn);
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(
            $tableColumn + $columnCount - 1
        );
        $sheet->getStyle("{$firstColumn}{$tableRow}:{$lastColumn}{$tableRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
        ]);
        foreach (range($tableColumn, $tableColumn + $columnCount - 1) as $column) {
            $sheet->getColumnDimension(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($column)
            )->setWidth($column === $tableColumn ? 32 : 20);
        }

        (new Xlsx($spreadsheet))->save($outputPath);
        $spreadsheet->disconnectWorksheets();
        File::delete($imagePath);

        $filename = Str::slug($validated['filename']).'-chart-and-data.xlsx';

        return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
    }
}
