<?php

namespace Tests\Feature;

use App\Enums\CityEnum;
use App\Models\City;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_is_normalized_from_its_country_and_catalog_code(): void
    {
        $country = Country::query()->create([
            'country_name' => 'Ecuador',
            'country_code' => 'EC',
        ]);

        $city = City::query()->create([
            'country_id' => $country->getKey(),
            'name' => 'Incorrect name',
            'city_code' => CityEnum::Quito->cityCode(),
            'state' => 'Incorrect state',
        ]);

        $this->assertSame('Quito', $city->name);
        $this->assertSame('UIO', $city->city_code);
        $this->assertSame('Pichincha', $city->state);
        $this->assertTrue($city->country->is($country));
    }

    public function test_same_city_cannot_be_registered_twice_for_one_country(): void
    {
        $country = Country::query()->create([
            'country_name' => 'Ecuador',
            'country_code' => 'EC',
        ]);

        City::query()->create([
            'country_id' => $country->getKey(),
            'name' => 'Quito',
            'city_code' => 'UIO',
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        City::query()->create([
            'country_id' => $country->getKey(),
            'name' => 'Quito',
            'city_code' => 'UIO',
        ]);
    }
}
