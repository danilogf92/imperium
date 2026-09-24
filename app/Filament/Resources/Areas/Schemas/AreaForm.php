<?php

namespace App\Filament\Resources\Areas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label(__('Company'))
                    ->relationship('company', 'company_name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('manager_id')
                    ->label(__('Manager'))
                    ->relationship('manager', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),


                TextInput::make('name')->label(__('Name'))
                    ->required(),
                Textarea::make('description')->label(__('Description'))
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
