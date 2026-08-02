<?php

namespace App\Models;

use App\Enums\CityEnum;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class City extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'country_id',
        'name',
        'city_code',
        'state',
    ];

    protected static function booted(): void
    {
        static::saving(function (City $city): void {
            $countryCode = Country::query()
                ->whereKey($city->country_id)
                ->value('country_code');
            $catalogCity = CityEnum::find($countryCode, $city->city_code);

            if ($catalogCity) {
                $city->name = $catalogCity->label();
                $city->city_code = $catalogCity->cityCode();
                $city->state = $catalogCity->state();
            }

            $duplicateExists = self::query()
                ->where('country_id', $city->country_id)
                ->where('city_code', $city->city_code)
                ->when(
                    $city->exists,
                    fn ($query) => $query->whereKeyNot($city->getKey()),
                )
                ->exists();

            if ($duplicateExists) {
                throw ValidationException::withMessages([
                    'city_catalog' => 'This city is already registered for the selected country.',
                ]);
            }
        });
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
