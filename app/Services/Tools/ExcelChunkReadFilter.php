<?php

namespace App\Services\Tools;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

final class ExcelChunkReadFilter implements IReadFilter
{
    public function __construct(
        private readonly int $firstRow,
        private readonly int $lastRow,
    ) {}

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->firstRow && $row <= $this->lastRow;
    }
}
