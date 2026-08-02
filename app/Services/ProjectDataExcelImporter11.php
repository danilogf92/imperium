<?php

namespace App\Services;

use App\Models\Data;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProjectDataExcelImporter
{
    private const HEADER_MAP = [
        'area' => 'area',
        'group 1' => 'group_1',
        'group 2' => 'group_2',
        'description' => 'description',
        'general classification' => 'general_classification',
        'item type' => 'item_type',
        'stage' => 'stage',
        'unit' => 'unit',
        'qty' => 'qty',
        'unit price' => 'unit_price',
        'global price' => 'global_price',
        'real' => 'real_value',
        'booked' => 'booked',
        'percentage' => 'percentage',
        'executed dollars' => 'executed_dollars',
        'executed euros' => 'executed_euros',
        'supplier' => 'supplier',
        'code' => 'code',
        'order no' => 'order_no',
        'input num' => 'input_num',
        'observations' => 'observations',
    ];

    private const REQUIRED_HEADERS = [
        'area',
        'description',
        'qty',
        'unit price',
        'global price',
    ];

    private const NUMERIC_FIELDS = [
        'qty',
        'unit_price',
        'global_price',
        'real_value',
        'booked',
        'percentage',
        'executed_dollars',
        'executed_euros',
    ];

    public function import(Project $project, string $path): int
    {
        $rate = (float) $project->rate;
        if ($rate <= 0) {
            throw ValidationException::withMessages([
                'dataImportFile' => 'The project rate must be greater than zero before importing data.',
            ]);
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'dataImportFile' => 'The workbook must contain a header row and at least one data row.',
            ]);
        }

        $headers = collect(array_shift($rows))
            ->map(fn ($header): string => $this->normalizeHeader($header))
            ->all();
        $headerIndexes = array_flip($headers);
        $missing = array_values(array_diff(self::REQUIRED_HEADERS, $headers));

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'dataImportFile' => 'Missing required columns: '.implode(', ', $missing).'.',
            ]);
        }

        $records = [];
        $errors = [];

        foreach ($rows as $offset => $row) {
            $excelRow = $offset + 2;
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $record = [];
            foreach (self::HEADER_MAP as $header => $field) {
                $value = array_key_exists($header, $headerIndexes)
                    ? ($row[$headerIndexes[$header]] ?? null)
                    : null;

                if (in_array($field, self::NUMERIC_FIELDS, true)) {
                    if (filled($value) && ! is_numeric($value)) {
                        $errors[] = "Row {$excelRow}: {$header} must be numeric.";
                    }
                    $record[$field] = filled($value) ? round((float) $value, 2) : 0;
                } else {
                    $record[$field] = filled($value) ? trim((string) $value) : null;
                }
            }

            if ($record['percentage'] < 0 || $record['percentage'] > 100) {
                $errors[] = "Row {$excelRow}: percentage must be between 0 and 100.";
            }

            foreach ([
                'area', 'group_1', 'group_2', 'general_classification', 'item_type',
                'unit', 'stage', 'supplier', 'code', 'order_no', 'input_num',
            ] as $field) {
                if (mb_strlen((string) $record[$field]) > 255) {
                    $errors[] = "Row {$excelRow}: ".str_replace('_', ' ', $field).' exceeds 255 characters.';
                }
            }

            if (mb_strlen((string) $record['description']) > 10000) {
                $errors[] = "Row {$excelRow}: description exceeds 10,000 characters.";
            }
            if (mb_strlen((string) $record['observations']) > 10000) {
                $errors[] = "Row {$excelRow}: observations exceeds 10,000 characters.";
            }

            $record['project_id'] = $project->id;
            $record['committed'] = 0;
            $record['global_price_euros'] = round($record['global_price'] / $rate, 2);
            $record['real_value_euros'] = round($record['real_value'] / $rate, 2);
            $record['booked_euros'] = round($record['booked'] / $rate, 2);

            if ($record['executed_euros'] == 0 && $record['executed_dollars'] != 0) {
                $record['executed_euros'] = round($record['executed_dollars'] / $rate, 2);
            } elseif ($record['executed_dollars'] == 0 && $record['executed_euros'] != 0) {
                $record['executed_dollars'] = round($record['executed_euros'] * $rate, 2);
            }

            $records[] = $record;

            if (count($records) > 5000) {
                throw ValidationException::withMessages([
                    'dataImportFile' => 'A maximum of 5,000 rows can be imported at once.',
                ]);
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'dataImportFile' => implode(' ', array_slice($errors, 0, 10)),
            ]);
        }

        if ($records === []) {
            throw ValidationException::withMessages([
                'dataImportFile' => 'No data rows were found in the workbook.',
            ]);
        }

        DB::transaction(function () use ($project, $records): void {
            foreach ($records as $record) {
                Data::query()->create($record);
            }

            $project->update(['data_uploaded' => true]);
        });

        return count($records);
    }

    private function normalizeHeader(mixed $header): string
    {
        return (string) str((string) $header)
            ->trim()
            ->lower()
            ->replace(['_', '-'], ' ')
            ->squish();
    }

    private function rowIsEmpty(array $row): bool
    {
        return collect($row)->every(fn ($value): bool => blank($value));
    }
}
