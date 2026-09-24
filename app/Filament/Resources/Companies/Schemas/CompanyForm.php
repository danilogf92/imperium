<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label(__('Company Name'))
                    ->required(),
                Select::make('city_id')
                    ->label(__('City'))
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('company_code')
                    ->label(__('Company Code'))
                    ->required(),
                TextInput::make('multiplier')
                    ->label(__('Budget Multiplier'))
                    ->numeric()
                    ->minValue(0)
                    ->step(0.000001)
                    ->default(1)
                    ->required()
                    ->helperText(__('Project Budgeted values are calculated as Base × Multiplier.')),
            ]);
    }
}
