<?php

namespace App\Services\Tools;

use Illuminate\Validation\ValidationException;

final class ExcelFilterValueConverter
{
    public function convert(string $value, string $type, string $header): array
    {
        if ($type === 'text' || trim($value) === '') {
            return ['type' => 'text', 'value' => $value, 'display' => $value];
        }

        $input = str_replace(["\xc2\xa0", ' '], '', trim($value));
        if (! preg_match('/^[+-]?(?:\d{1,3}(?:[,.]\d{3})+|\d+)(?:[,.]\d+)?$/', $input)) {
            throw ValidationException::withMessages(['columnTypes' => "La columna {$header} contiene un valor que no es numérico: {$value}."]);
        }

        $lastDot = strrpos($input, '.');
        $lastComma = strrpos($input, ',');
        if ($lastDot !== false && $lastComma !== false) {
            $decimalSeparator = $lastDot > $lastComma ? '.' : ',';
        } elseif ($lastComma !== false) {
            $decimalSeparator = substr_count($input, ',') === 1 && strlen($input) - $lastComma - 1 !== 3 ? ',' : null;
        } else {
            $decimalSeparator = $lastDot !== false && substr_count($input, '.') === 1 ? '.' : null;
        }

        $decimals = $decimalSeparator === null ? 0 : strlen($input) - strrpos($input, $decimalSeparator) - 1;
        if ($decimals > 10) {
            throw ValidationException::withMessages(['columnTypes' => "La columna {$header} tiene más de 10 decimales."]);
        }
        $integer = $decimalSeparator === null ? $input : substr($input, 0, strrpos($input, $decimalSeparator));
        $fraction = $decimalSeparator === null ? '' : substr($input, strrpos($input, $decimalSeparator) + 1);
        $canonical = str_replace([',', '.'], '', $integer).($decimals > 0 ? '.'.$fraction : '');
        if (! is_numeric($canonical) || strlen(ltrim(str_replace('.', '', $canonical), '+-0')) > 15) {
            throw ValidationException::withMessages(['columnTypes' => "La columna {$header} contiene un número que Excel no puede representar con precisión: {$value}."]);
        }

        return [
            'type' => 'number',
            'value' => $canonical,
            'display' => number_format((float) $canonical, $decimals, '.', ','),
            'decimals' => $decimals,
        ];
    }
}
