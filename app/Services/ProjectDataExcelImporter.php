<?php

namespace App\Services;

use App\Models\Data;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProjectDataExcelImporter
{
    /**
     * Encabezados aceptados y el campo del modelo al que corresponden.
     */
    private const HEADER_MAP = [
        'area' => 'area',

        'group 1' => 'group_1',
        'group1' => 'group_1',

        'group 2' => 'group_2',
        'group2' => 'group_2',

        'description' => 'description',

        'general classification' => 'general_classification',
        'classification' => 'general_classification',

        'item type' => 'item_type',

        'unit' => 'unit',
        'qty' => 'qty',
        'quantity' => 'qty',

        'unit price' => 'unit_price',
        'unit price $' => 'unit_price',

        'global price' => 'global_price',
        'global price $' => 'global_price',
        'budgeted' => 'global_price',
        'budgeted $' => 'global_price',

        'global price €' => 'global_price_euros',
        'global price euros' => 'global_price_euros',
        'budgeted €' => 'global_price_euros',
        'budgeted euros' => 'global_price_euros',

        'stage' => 'stage',

        'real' => 'real_value',
        'real value' => 'real_value',
        'real $' => 'real_value',
        'real value $' => 'real_value',

        'real €' => 'real_value_euros',
        'real euros' => 'real_value_euros',
        'real value €' => 'real_value_euros',
        'real value euros' => 'real_value_euros',

        'percentage' => 'percentage',

        'executed dollars' => 'executed_dollars',
        'executed $' => 'executed_dollars',

        'executed euros' => 'executed_euros',
        'executed €' => 'executed_euros',

        'supplier' => 'supplier',
        'code' => 'code',

        'order no' => 'order_no',
        'order no.' => 'order_no',
        'order number' => 'order_no',
        'order year' => 'order_year',
        'year' => 'order_year',

        'input num' => 'input_num',
        'input no' => 'input_num',
        'input no.' => 'input_num',
        'input number' => 'input_num',

        'observations' => 'observations',

        'booked' => 'booked',
        'booked $' => 'booked',

        'booked €' => 'booked_euros',
        'booked euros' => 'booked_euros',
    ];

    /**
     * Para cada campo requerido se admite cualquiera de estos encabezados.
     */
    private const REQUIRED_FIELDS = [
        'area' => [
            'area',
        ],

        'description' => [
            'description',
        ],

        'qty' => [
            'qty',
            'quantity',
        ],

        'unit_price' => [
            'unit price',
            'unit price $',
        ],

        'global_price' => [
            'global price',
            'global price $',
            'budgeted',
            'budgeted $',
        ],
    ];

    private const NUMERIC_FIELDS = [
        'qty',
        'unit_price',
        'global_price',
        'global_price_euros',
        'real_value',
        'real_value_euros',
        'booked',
        'booked_euros',
        'percentage',
        'executed_dollars',
        'executed_euros',
        'order_year',
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

        $rows = $sheet->toArray(
            null,
            true,
            true,
            false
        );

        $spreadsheet->disconnectWorksheets();

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'dataImportFile' => 'The workbook must contain a header row and at least one data row.',
            ]);
        }

        $headers = collect(array_shift($rows))
            ->map(fn($header): string => $this->normalizeHeader($header))
            ->all();

        $headerIndexes = [];

        foreach ($headers as $index => $header) {
            if ($header !== '') {
                $headerIndexes[$header] = $index;
            }
        }

        $missingFields = $this->getMissingRequiredFields($headers);

        if ($missingFields !== []) {
            throw ValidationException::withMessages([
                'dataImportFile' => 'Missing required columns: '
                    . implode(', ', $missingFields)
                    . '.',
            ]);
        }

        $records = [];
        $errors = [];

        foreach ($rows as $offset => $row) {
            $excelRow = $offset + 2;

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $record = $this->emptyRecord();

            foreach (self::HEADER_MAP as $header => $field) {
                if (! array_key_exists($header, $headerIndexes)) {
                    continue;
                }

                $columnIndex = $headerIndexes[$header];
                $value = $row[$columnIndex] ?? null;

                if (in_array($field, self::NUMERIC_FIELDS, true)) {
                    if (filled($value) && ! is_numeric($value)) {
                        $column = $this->columnLetter($columnIndex);
                        $errors[] = "Row {$excelRow}, column {$column} ({$header}): value must be numeric.";
                        continue;
                    }

                    $record[$field] = filled($value)
                        ? round((float) $value, 2)
                        : 0;

                    continue;
                }

                $record[$field] = filled($value)
                    ? trim((string) $value)
                    : null;
            }

            foreach (self::REQUIRED_FIELDS as $field => $acceptedHeaders) {
                $columnIndex = $this->findHeaderIndex($headerIndexes, $acceptedHeaders);
                $value = $columnIndex === null ? null : ($row[$columnIndex] ?? null);
                if ($value === null || trim((string) $value) === '') {
                    $column = $columnIndex === null ? '?' : $this->columnLetter($columnIndex);
                    $label = str_replace('_', ' ', $field);
                    $errors[] = "Row {$excelRow}, column {$column} ({$label}): value is required.";
                }
            }

            if ($record['percentage'] < 0 || $record['percentage'] > 100) {
                $errors[] = "Row {$excelRow}, percentage: value must be between 0 and 100.";
            }

            if ($record['order_year'] && ($record['order_year'] < 2000 || $record['order_year'] > 2100)) {
                $errors[] = "Row {$excelRow}, order year: value must be between 2000 and 2100.";
            }

            foreach (
                [
                    'area',
                    'group_1',
                    'group_2',
                    'general_classification',
                    'item_type',
                    'unit',
                    'stage',
                    'supplier',
                    'code',
                    'order_no',
                    'input_num',
                ] as $field
            ) {
                if (mb_strlen((string) $record[$field]) > 255) {
                    $label = str_replace('_', ' ', $field);
                    $errors[] = "Row {$excelRow}, {$label}: exceeds 255 characters.";
                }
            }

            if (mb_strlen((string) $record['description']) > 10000) {
                $errors[] = "Row {$excelRow}, description: exceeds 10,000 characters.";
            }

            if (mb_strlen((string) $record['observations']) > 10000) {
                $errors[] = "Row {$excelRow}, observations: exceeds 10,000 characters.";
            }

            $record['project_id'] = $project->id;
            $record['order_year'] = filled($record['order_no'])
                ? ((int) ($record['order_year'] ?: $project->forecast_start_date?->format('Y') ?: now()->year))
                : null;

            $this->calculateCurrencyValues($record, $rate);

            $records[] = $record;

            if (count($records) > 5000) {
                throw ValidationException::withMessages([
                    'dataImportFile' => 'A maximum of 5,000 rows can be imported at once.',
                ]);
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'dataImportErrors' => array_slice($errors, 0, 50),
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

            $project->update([
                'data_uploaded' => true,
            ]);
        });

        return count($records);
    }

    /**
     * Devuelve un registro con todos los campos del modelo Data.
     */
    private function emptyRecord(): array
    {
        return [
            'project_id' => null,
            'area' => null,
            'group_1' => null,
            'group_2' => null,
            'description' => null,
            'general_classification' => null,
            'item_type' => null,
            'unit' => null,
            'qty' => 0,
            'unit_price' => 0,
            'global_price' => 0,
            'stage' => null,
            'real_value' => 0,
            'percentage' => 0,
            'executed_dollars' => 0,
            'executed_euros' => 0,
            'supplier' => null,
            'code' => null,
            'order_no' => null,
            'order_year' => null,
            'input_num' => null,
            'observations' => null,
            'booked' => 0,
            'global_price_euros' => 0,
            'real_value_euros' => 0,
            'booked_euros' => 0,
        ];
    }

    /**
     * Calcula los valores faltantes entre dólares y euros.
     */
    private function calculateCurrencyValues(array &$record, float $rate): void
    {
        if (
            $record['global_price_euros'] == 0
            && $record['global_price'] != 0
        ) {
            $record['global_price_euros'] = round(
                $record['global_price'] / $rate,
                2
            );
        } elseif (
            $record['global_price'] == 0
            && $record['global_price_euros'] != 0
        ) {
            $record['global_price'] = round(
                $record['global_price_euros'] * $rate,
                2
            );
        }

        if (
            $record['real_value_euros'] == 0
            && $record['real_value'] != 0
        ) {
            $record['real_value_euros'] = round(
                $record['real_value'] / $rate,
                2
            );
        } elseif (
            $record['real_value'] == 0
            && $record['real_value_euros'] != 0
        ) {
            $record['real_value'] = round(
                $record['real_value_euros'] * $rate,
                2
            );
        }

        if (
            $record['booked_euros'] == 0
            && $record['booked'] != 0
        ) {
            $record['booked_euros'] = round(
                $record['booked'] / $rate,
                2
            );
        } elseif (
            $record['booked'] == 0
            && $record['booked_euros'] != 0
        ) {
            $record['booked'] = round(
                $record['booked_euros'] * $rate,
                2
            );
        }

        if (
            $record['executed_euros'] == 0
            && $record['executed_dollars'] != 0
        ) {
            $record['executed_euros'] = round(
                $record['executed_dollars'] / $rate,
                2
            );
        } elseif (
            $record['executed_dollars'] == 0
            && $record['executed_euros'] != 0
        ) {
            $record['executed_dollars'] = round(
                $record['executed_euros'] * $rate,
                2
            );
        }
    }

    /**
     * Comprueba los campos requeridos aceptando distintos encabezados.
     */
    private function getMissingRequiredFields(array $headers): array
    {
        $missing = [];

        foreach (self::REQUIRED_FIELDS as $field => $acceptedHeaders) {
            $found = collect($acceptedHeaders)
                ->contains(fn(string $header): bool => in_array(
                    $header,
                    $headers,
                    true
                ));

            if (! $found) {
                $missing[] = str_replace('_', ' ', $field);
            }
        }

        return $missing;
    }

    private function normalizeHeader(mixed $header): string
    {
        return (string) str((string) $header)
            ->replace("\u{00A0}", ' ')
            ->trim()
            ->lower()
            ->replace(['_', '-'], ' ')
            ->squish();
    }

    private function rowIsEmpty(array $row): bool
    {
        return collect($row)
            ->every(fn($value): bool => blank($value));
    }

    private function columnLetter(int $zeroBasedIndex): string
    {
        $index = $zeroBasedIndex + 1;
        $letters = '';

        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)).$letters;
            $index = intdiv($index, 26);
        }

        return $letters;
    }

    private function findHeaderIndex(array $headerIndexes, array $acceptedHeaders): ?int
    {
        foreach ($acceptedHeaders as $header) {
            if (array_key_exists($header, $headerIndexes)) {
                return $headerIndexes[$header];
            }
        }

        return null;
    }
}
