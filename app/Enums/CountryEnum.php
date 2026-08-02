<?php

namespace App\Enums;

enum CountryEnum: string
{
    case Argentina = 'AR';
    case Bolivia = 'BO';
    case Brazil = 'BR';
    case Canada = 'CA';
    case Chile = 'CL';
    case Colombia = 'CO';
    case CostaRica = 'CR';
    case DominicanRepublic = 'DO';
    case Ecuador = 'EC';
    case ElSalvador = 'SV';
    case France = 'FR';
    case Germany = 'DE';
    case Guatemala = 'GT';
    case Honduras = 'HN';
    case Italy = 'IT';
    case Mexico = 'MX';
    case Netherlands = 'NL';
    case Nicaragua = 'NI';
    case Panama = 'PA';
    case Paraguay = 'PY';
    case Peru = 'PE';
    case Portugal = 'PT';
    case Spain = 'ES';
    case UnitedKingdom = 'GB';
    case UnitedStates = 'US';
    case Uruguay = 'UY';
    case Venezuela = 'VE';

    public function label(): string
    {
        return match ($this) {
            self::CostaRica => 'Costa Rica',
            self::DominicanRepublic => 'Dominican Republic',
            self::ElSalvador => 'El Salvador',
            self::UnitedKingdom => 'United Kingdom',
            self::UnitedStates => 'United States',
            default => $this->name,
        };
    }

    public function phoneCode(): string
    {
        return match ($this) {
            self::Argentina => '+54',
            self::Bolivia => '+591',
            self::Brazil => '+55',
            self::Canada, self::UnitedStates => '+1',
            self::Chile => '+56',
            self::Colombia => '+57',
            self::CostaRica => '+506',
            self::DominicanRepublic => '+1',
            self::Ecuador => '+593',
            self::ElSalvador => '+503',
            self::France => '+33',
            self::Germany => '+49',
            self::Guatemala => '+502',
            self::Honduras => '+504',
            self::Italy => '+39',
            self::Mexico => '+52',
            self::Netherlands => '+31',
            self::Nicaragua => '+505',
            self::Panama => '+507',
            self::Paraguay => '+595',
            self::Peru => '+51',
            self::Portugal => '+351',
            self::Spain => '+34',
            self::UnitedKingdom => '+44',
            self::Uruguay => '+598',
            self::Venezuela => '+58',
        };
    }

    public function flag(): string
    {
        return implode('', array_map(
            static fn (string $letter): string => mb_chr(127397 + ord($letter)),
            str_split($this->value),
        ));
    }

    /**
     * @param array<int, string> $excludedCodes
     * @return array<string, string>
     */
    public static function options(array $excludedCodes = []): array
    {
        $excludedCodes = array_map('strtoupper', $excludedCodes);

        return collect(self::cases())
            ->reject(fn (self $country): bool => in_array(
                $country->value,
                $excludedCodes,
                true,
            ))
            ->mapWithKeys(fn (self $country): array => [
                $country->value => "{$country->flag()} {$country->label()}",
            ])
            ->all();
    }
}
