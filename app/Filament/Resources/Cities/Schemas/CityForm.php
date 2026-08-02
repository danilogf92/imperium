<?php

namespace App\Filament\Resources\Cities\Schemas;

use App\Enums\CityEnum;
use App\Models\City;
use App\Models\Country;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'country_name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('city_catalog', null))
                    ->required(),
                Select::make('city_catalog')
                    ->label('City')
                    ->searchable()
                    ->native(false)
                    ->required()
                    ->dehydrated(false)
                    ->options(function (Get $get, ?City $record): array {
                        $country = Country::query()->find($get('country_id'));

                        if (! $country) {
                            return [];
                        }

                        $excludedCodes = City::query()
                            ->where('country_id', $country->getKey())
                            ->when(
                                $record,
                                fn ($query) => $query->whereKeyNot($record->getKey()),
                            )
                            ->whereNotNull('city_code')
                            ->pluck('city_code')
                            ->all();

                        return CityEnum::optionsForCountry(
                            $country->country_code,
                            $excludedCodes,
                        );
                    })
                    ->formatStateUsing(function (?City $record): ?string {
                        if (! $record) {
                            return null;
                        }

                        return CityEnum::find(
                            $record->country?->country_code,
                            $record->city_code,
                        )?->value;
                    })
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        $city = CityEnum::tryFrom((string) $state);

                        $set('name', $city?->label());
                        $set('city_code', $city?->cityCode());
                        $set('state', $city?->state());
                    }),
                TextInput::make('name')
                    ->label('City Name')
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                TextInput::make('city_code')
                    ->label('City Code')
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('state')
                    ->label('State / Province')
                    ->disabled()
                    ->dehydrated(),
            ]);
    }
}
