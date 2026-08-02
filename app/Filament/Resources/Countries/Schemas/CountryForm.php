<?php

namespace App\Filament\Resources\Countries\Schemas;

use App\Enums\CountryEnum;
use App\Models\Country;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CountryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('country_code')
                    ->label('Country')
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->options(function (?Country $record): array {
                        $excludedCodes = Country::query()
                            ->when(
                                $record,
                                fn ($query) => $query->whereKeyNot($record->getKey()),
                            )
                            ->pluck('country_code')
                            ->all();

                        return CountryEnum::options($excludedCodes);
                    })
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        $country = CountryEnum::tryFrom((string) $state);

                        $set('country_name', $country?->label());
                        $set('iso_code', $country?->value);
                        $set('phone_code', $country?->phoneCode());
                        $set('flag', $country?->flag());
                    })
                    ->unique(ignoreRecord: true),
                TextInput::make('country_name')
                    ->label('Country Name')
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('iso_code')
                    ->label('Country Code')
                    ->helperText('ISO 3166-1 Alpha-2')
                    ->formatStateUsing(fn (?Country $record): ?string => $record?->country_code)
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('flag')
                    ->label('Flag')
                    ->helperText('Generated automatically from the country code')
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('phone_code')
                    ->label('Phone Code')
                    ->helperText('Generated automatically from the selected country')
                    ->maxLength(10)
                    ->disabled()
                    ->dehydrated(),
            ]);
    }
}
