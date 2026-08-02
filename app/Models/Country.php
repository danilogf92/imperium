<?php

namespace App\Models;

use App\Enums\CountryEnum;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'country_name',
        'country_code',
        'flag',
        'phone_code',
    ];

    protected static function booted(): void
    {
        static::saving(function (Country $country): void {
            $country->country_code = strtoupper(trim($country->country_code));

            if ($catalogCountry = CountryEnum::tryFrom($country->country_code)) {
                $country->country_name = $catalogCountry->label();
                $country->phone_code = $catalogCountry->phoneCode();
                $country->flag = $catalogCountry->flag();
            } else {
                $country->flag = self::flagFromCode($country->country_code);
            }
        });
    }

    public static function flagFromCode(?string $countryCode): ?string
    {
        $countryCode = strtoupper(trim((string) $countryCode));

        if (preg_match('/^[A-Z]{2}$/', $countryCode) !== 1) {
            return null;
        }

        return implode('', array_map(
            static fn (string $letter): string => mb_chr(127397 + ord($letter)),
            str_split($countryCode),
        ));
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
