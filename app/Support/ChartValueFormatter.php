<?php

namespace App\Support;

class ChartValueFormatter
{
    public static function compactMoney(string $symbol): string
    {
        $encodedSymbol = json_encode($symbol, JSON_THROW_ON_ERROR);

        return "function(value) { const number = Number(value) || 0; const absolute = Math.abs(number); "
            ."const divisor = absolute >= 1000000 ? 1000000 : (absolute >= 1000 ? 1000 : 1); "
            ."const suffix = divisor === 1000000 ? ' M' : (divisor === 1000 ? ' K' : ''); "
            ."return {$encodedSymbol} + ' ' + (number / divisor).toLocaleString(undefined, "
            ."{minimumFractionDigits: 0, maximumFractionDigits: 2}) + suffix; }";
    }
}
