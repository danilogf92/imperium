<?php

namespace App\Support;

final class MoneyValueFormatter
{
    public static function thousands(float|int $value, string $symbol = ''): string
    {
        $absoluteValue = abs($value);
        $nextThousand = ceil($absoluteValue / 1_000) * 1_000;

        // Correct only residuals within 0.01 K (10 currency units) of the next integer K.
        $displayValue = $nextThousand - $absoluteValue <= 10
            ? ($value < 0 ? -$nextThousand : $nextThousand) / 1_000
            : (int) ($value / 1_000);

        return ($symbol !== '' ? $symbol.' ' : '').number_format($displayValue, 0, '.', ' ').' K';
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
