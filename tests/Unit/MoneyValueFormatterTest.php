<?php

namespace Tests\Unit;

use App\Support\MoneyValueFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyValueFormatterTest extends TestCase
{
    #[DataProvider('thousandsValues')]
    public function test_only_residual_decimals_are_rounded_in_k(float|int $value, string $expected): void
    {
        $this->assertSame($expected, MoneyValueFormatter::thousands($value));
        $this->assertSame('$ '.$expected, MoneyValueFormatter::thousands($value, '$'));
    }

    public static function thousandsValues(): array
    {
        return [
            'residual at threshold' => [11_999_990, '12 000 K'],
            'residual inside threshold' => [11_999_999.99, '12 000 K'],
            'outside threshold' => [11_999_989.99, '11 999 K'],
            'ordinary decimals' => [11_999_500, '11 999 K'],
            'exact integer K' => [11_999_000, '11 999 K'],
            'fifty K away' => [11_950_000, '11 950 K'],
            'six hundred K away' => [11_400_000, '11 400 K'],
            'clean value' => [12_000_000, '12 000 K'],
            'negative residual' => [-11_999_990, '-12 000 K'],
            'negative outside threshold' => [-11_999_989.99, '-11 999 K'],
            'small residual' => [999.99, '1 K'],
            'small ordinary value' => [950, '0 K'],
            'zero' => [0, '0 K'],
        ];
    }
}
