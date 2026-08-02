<?php

namespace Tests\Unit;

use App\Enums\CityEnum;
use PHPUnit\Framework\TestCase;

class CityEnumTest extends TestCase
{
    public function test_every_city_has_complete_catalog_data(): void
    {
        foreach (CityEnum::cases() as $city) {
            $this->assertSame(2, strlen($city->countryCode()));
            $this->assertNotSame('', $city->cityCode());
            $this->assertNotSame('', $city->label());
            $this->assertNotNull($city->state());
        }
    }
}
