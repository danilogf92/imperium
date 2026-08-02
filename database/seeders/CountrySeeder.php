<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            [
                'country_name' => 'Ecuador',
                'country_code' => 'EC',
                'phone_code' => '+593',
            ],
            [
                'country_name' => 'Colombia',
                'country_code' => 'CO',
                'phone_code' => '+57',
            ],
            [
                'country_name' => 'United States',
                'country_code' => 'US',
                'phone_code' => '+1',
            ],
            [
                'country_name' => 'Spain',
                'country_code' => 'ES',
                'phone_code' => '+34',
            ],
        ];

        foreach ($countries as $country) {
            Country::query()->updateOrCreate(
                ['country_code' => $country['country_code']],
                $country,
            );
        }
    }
}
