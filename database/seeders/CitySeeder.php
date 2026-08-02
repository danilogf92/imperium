<?php

namespace Database\Seeders;

use App\Enums\CityEnum;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            CityEnum::Quito,
            CityEnum::Guayaquil,
            CityEnum::Bogota,
            CityEnum::Medellin,
            CityEnum::NewYork,
            CityEnum::Miami,
            CityEnum::Madrid,
            CityEnum::Barcelona,
        ];

        foreach ($cities as $catalogCity) {
            $country = Country::query()
                ->where('country_code', $catalogCity->countryCode())
                ->first();

            if (! $country) {
                continue;
            }

            City::query()->updateOrCreate(
                [
                    'country_id' => $country->getKey(),
                    'city_code' => $catalogCity->cityCode(),
                ],
                [
                    'name' => $catalogCity->label(),
                    'state' => $catalogCity->state(),
                ],
            );
        }
    }
}
