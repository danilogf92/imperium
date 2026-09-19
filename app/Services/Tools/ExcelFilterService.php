<?php

namespace App\Services\Tools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use RuntimeException;

final class ExcelFilterService
{
    private const ROOT = 'tools/excel-filter';
    private const CHUNK_SIZE = 500;
    private const MAX_COLUMNS = 256;
    private const LIFETIME_SECONDS = 86400;

    public function store(UploadedFile $upload, int $userId): string
    {
        $extension = strtolower($upload->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            throw ValidationException::withMessages(['upload' => __('tools.error_upload_extension')]);
        }

        $token = (string) Str::uuid();
        $stored = $upload->storeAs(self::ROOT."/sources/{$userId}", "{$token}.{$extension}", 'local');
        if (! $stored) {
            throw new RuntimeException('The workbook could not be saved temporarily.');
        }

        return $token;
    }

    public function removeSource(int $userId, string $token): void
    {
        if (Str::isUuid($token)) {
            foreach (['xlsx', 'xls'] as $extension) {
                Storage::disk('local')->delete(self::ROOT."/sources/{$userId}/{$token}.{$extension}");
            }
        }
    }

    public function analyze(int $userId, string $token): array
    {
        [$reader, $path, $info] = $this->open($userId, $token);
        $columnCount = (int) $info['totalColumns'];
        if ($columnCount < 1 || $columnCount > self::MAX_COLUMNS) {
            throw ValidationException::withMessages(['upload' => __('tools.error_column_limit', ['max' => self::MAX_COLUMNS])]);
        }

        $firstRows = $this->readChunk($reader, $path, $info, 1, min(6, (int) $info['totalRows']));
        $rawHeader = $firstRows[0] ?? [];
        $lastHeaderIndex = -1;
        foreach ($rawHeader as $index => $value) {
            if (trim((string) $value) !== '') {
                $lastHeaderIndex = $index;
            }
        }
        if ($lastHeaderIndex < 0) {
            throw ValidationException::withMessages(['upload' => __('tools.error_missing_headers')]);
        }

        $headers = array_map(fn ($value): string => trim((string) $value), array_slice($rawHeader, 0, $lastHeaderIndex + 1));
        if (in_array('', $headers, true) || count(array_unique(array_map('mb_strtolower', $headers))) !== count($headers)) {
            throw ValidationException::withMessages(['upload' => __('tools.error_duplicate_headers')]);
        }

        $samples = [];
        foreach (array_slice($firstRows, 1) as $row) {
            $values = array_map(fn ($value): string => (string) ($value ?? ''), array_slice($row, 0, count($headers)));
            if ($this->hasData($values)) {
                $samples[] = $values;
            }
        }
        if (count($samples) < 5) {
            foreach ($this->rows($reader, $path, $info, count($headers), 7) as $row) {
                $samples[] = $row;
                if (count($samples) === 5) {
                    break;
                }
            }
        }
        if ($samples === []) {
            throw ValidationException::withMessages(['upload' => __('tools.error_no_data')]);
        }

        return ['headers' => $headers, 'columnCount' => count($headers), 'sampleRows' => $samples];
    }

    public function preview(int $userId, string $token, array $selectedColumns, int $filterColumn, string $value, int $page, int $perPage = 25, array $columnTypes = []): array
    {
        [$reader, $path, $info] = $this->open($userId, $token);
        $headers = $this->headers($reader, $path, $info);
        $selectedColumns = $this->validColumns($selectedColumns, count($headers), $filterColumn, $value);
        $types = $this->validTypes($columnTypes, $selectedColumns);
        $converter = new ExcelFilterValueConverter;
        $count = 0;
        $pageRows = [];
        foreach ($this->matchingRows($reader, $path, $info, count($headers), $filterColumn, $value) as $row) {
            $cells = array_map(fn (int $index): array => $converter->convert($row[$index], $types[$index], $headers[$index]), $selectedColumns);
            if ($count >= ($page - 1) * $perPage && count($pageRows) < $perPage) {
                $pageRows[] = array_column($cells, 'display');
            }
            $count++;
        }

        return [
            'headers' => array_map(fn (int $index): string => $headers[$index], $selectedColumns),
            'rows' => $pageRows,
            'count' => $count,
            'page' => $page,
            'lastPage' => max(1, (int) ceil($count / $perPage)),
        ];
    }

    public function export(int $userId, string $token, array $selectedColumns, int $filterColumn, string $value, array $columnTypes = []): string
    {
        [$reader, $path, $info] = $this->open($userId, $token);
        $headers = $this->headers($reader, $path, $info);
        $selectedColumns = $this->validColumns($selectedColumns, count($headers), $filterColumn, $value);
        $types = $this->validTypes($columnTypes, $selectedColumns);
        $converter = new ExcelFilterValueConverter;
        $selectedHeaders = array_map(fn (int $index): string => $headers[$index], $selectedColumns);
        $relativePath = self::ROOT.'/exports/'.Str::uuid().'.xlsx';
        Storage::disk('local')->makeDirectory(self::ROOT.'/exports');
        $outputPath = Storage::disk('local')->path($relativePath);

        try {
            (new ExcelFilterWorkbookWriter)->write($outputPath, $selectedHeaders, (function () use ($reader, $path, $info, $headers, $filterColumn, $value, $selectedColumns, $types, $converter) {
                foreach ($this->matchingRows($reader, $path, $info, count($headers), $filterColumn, $value) as $row) {
                    yield array_map(fn (int $index): array => $converter->convert($row[$index], $types[$index], $headers[$index]), $selectedColumns);
                }
            })());
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($relativePath);
            throw $exception;
        }

        return $outputPath;
    }

    public function purgeExpired(): void
    {
        $disk = Storage::disk('local');
        $cutoff = time() - self::LIFETIME_SECONDS;
        foreach ($disk->allFiles(self::ROOT) as $file) {
            if ($disk->lastModified($file) < $cutoff) {
                $disk->delete($file);
            }
        }
    }

    public function sourceRows(int $userId, string $token, string|array|null $numberHeader = null): \Generator
    {
        [$reader, $path, $info] = $this->open($userId, $token);
        $headers = $this->headers($reader, $path, $info);
        $numberHeaders = (array) ($numberHeader ?? 'real value');
        $normalizedHeaders = array_map(fn ($header) => Str::lower(Str::ascii(trim($header))), $headers);
        $numberColumns = [];
        foreach ($numberHeaders as $header) {
            $index = array_search(Str::lower(Str::ascii(trim($header))), $normalizedHeaders, true);
            if ($index !== false) {
                $numberColumns[] = $index;
            }
        }
        for ($start = 2; $start <= (int) $info['totalRows']; $start += self::CHUNK_SIZE) {
            $end = min($start + self::CHUNK_SIZE - 1, (int) $info['totalRows']);
            foreach ($this->readChunk($reader, $path, $info, $start, $end, count($headers), true, $numberColumns) as $row) {
                if ($this->hasData($row)) {
                    yield array_combine($headers, array_map(fn ($value) => (string) ($value ?? ''), $row));
                }
            }
        }
    }

    private function open(int $userId, string $token): array
    {
        if (! Str::isUuid($token)) {
            throw ValidationException::withMessages(['upload' => __('tools.error_reupload')]);
        }
        $disk = Storage::disk('local');
        foreach (['xlsx', 'xls'] as $extension) {
            $relativePath = self::ROOT."/sources/{$userId}/{$token}.{$extension}";
            if ($disk->exists($relativePath)) {
                if ($disk->lastModified($relativePath) < time() - self::LIFETIME_SECONDS) {
                    $disk->delete($relativePath);
                    break;
                }
                $path = $disk->path($relativePath);
                try {
                    $reader = IOFactory::createReaderForFile($path);
                    $worksheets = $reader->listWorksheetInfo($path);
                } catch (\Throwable $exception) {
                    throw ValidationException::withMessages(['upload' => __('tools.error_damaged')]);
                }
                if (count($worksheets) !== 1) {
                    throw ValidationException::withMessages(['upload' => __('tools.error_one_sheet')]);
                }

                return [$reader, $path, $worksheets[0]];
            }
        }

        throw ValidationException::withMessages(['upload' => __('tools.error_expired')]);
    }

    private function headers(IReader $reader, string $path, array $info): array
    {
        $raw = $this->readChunk($reader, $path, $info, 1, 1)[0] ?? [];
        $last = -1;
        foreach ($raw as $index => $value) {
            if (trim((string) $value) !== '') {
                $last = $index;
            }
        }
        $headers = array_map(fn ($value): string => trim((string) $value), array_slice($raw, 0, $last + 1));
        if ($headers === [] || in_array('', $headers, true) || count(array_unique(array_map('mb_strtolower', $headers))) !== count($headers)) {
            throw ValidationException::withMessages(['upload' => __('tools.error_headers')]);
        }

        return $headers;
    }

    private function validColumns(array $selected, int $columnCount, int $filterColumn, string $value): array
    {
        if ($filterColumn < 0 || $filterColumn >= $columnCount || trim($value) === '') {
            throw ValidationException::withMessages(['projectCode' => __('tools.error_filter_code')]);
        }
        $indices = array_values(array_filter(range(0, $columnCount - 1), fn (int $index): bool => in_array($index, array_map('intval', $selected), true)));
        if ($indices === []) {
            throw ValidationException::withMessages(['selectedColumns' => __('tools.error_columns')]);
        }

        return $indices;
    }

    private function validTypes(array $columnTypes, array $selectedColumns): array
    {
        $types = [];
        foreach ($selectedColumns as $index) {
            $type = $columnTypes[$index] ?? 'text';
            if (! in_array($type, ['text', 'number'], true)) {
                throw ValidationException::withMessages(['columnTypes' => __('tools.error_type')]);
            }
            $types[$index] = $type;
        }

        return $types;
    }

    private function matchingRows(IReader $reader, string $path, array $info, int $columnCount, int $filterColumn, string $value): \Generator
    {
        $wanted = trim($value);
        foreach ($this->rows($reader, $path, $info, $columnCount) as $row) {
            if (trim($row[$filterColumn]) === $wanted) {
                yield $row;
            }
        }
    }

    private function rows(IReader $reader, string $path, array $info, int $columnCount, int $firstRow = 2): \Generator
    {
        for ($start = $firstRow; $start <= (int) $info['totalRows']; $start += self::CHUNK_SIZE) {
            $end = min($start + self::CHUNK_SIZE - 1, (int) $info['totalRows']);
            foreach ($this->readChunk($reader, $path, $info, $start, $end, $columnCount) as $row) {
                $values = array_map(fn ($value): string => (string) ($value ?? ''), $row);
                if ($this->hasData($values)) {
                    yield $values;
                }
            }
        }
    }

    private function hasData(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    private function readChunk(IReader $reader, string $path, array $info, int $start, int $end, ?int $columnCount = null, bool $normalizeDates = false, array $numberColumns = []): array
    {
        if ($end < $start) {
            return [];
        }
        $reader->setLoadSheetsOnly($info['worksheetName']);
        $reader->setReadFilter(new ExcelChunkReadFilter($start, $end));
        $spreadsheet = $reader->load($path);
        try {
            $lastColumn = Coordinate::stringFromColumnIndex($columnCount ?? (int) $info['totalColumns']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->rangeToArray("A{$start}:{$lastColumn}{$end}", null, true, true, false);
            if ($normalizeDates) {
                foreach ($rows as $offset => &$row) {
                    foreach ($row as $index => &$value) {
                        $cell = $sheet->getCell([$index + 1, $start + $offset]);
                        if (in_array($index, $numberColumns, true) && is_numeric($cell->getCalculatedValue())) {
                            $value = $cell->getCalculatedValue();
                        } elseif (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell) && is_numeric($cell->getCalculatedValue())) {
                            $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $cell->getCalculatedValue())->format('Y-m-d');
                        }
                    }
                    unset($value);
                }
                unset($row);
            }
            return $rows;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }
}
