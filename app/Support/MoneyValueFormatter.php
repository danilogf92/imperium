<?php

namespace App\Support;

final class MoneyValueFormatter
{
    public static function thousands(float|int $value, string $symbol = ''): string
    {
        return ($symbol !== '' ? $symbol.' ' : '').number_format((int) ($value / 1_000), 0, '.', '').' K';
    }

    public static function compact(float|int $value, string $symbol = ''): string
    {
        $absoluteValue = abs((float) $value);

        if ($absoluteValue >= 1_000_000) {
            return $symbol.number_format((float) $value / 1_000_000, 2).'M';
        }

        if ($absoluteValue >= 1_000) {
            return $symbol.number_format((float) $value / 1_000, 2).'K';
        }

        return $symbol.number_format((float) $value, 2);
    }
}
