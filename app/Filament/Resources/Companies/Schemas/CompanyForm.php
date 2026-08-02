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
                    ->label('Company Name')
                    ->required(),
                Select::make('city_id')
                    ->label('City')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('company_code')
                    ->label('Company Code')
                    ->required(),
                TextInput::make('multiplier')
                    ->label('Budget Multiplier')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.000001)
                    ->default(1)
                    ->required()
                    ->helperText('Project Budgeted values are calculated as Base × Multiplier.'),
            ]);
    }
}
