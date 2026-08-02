<?php

namespace App\Filament\Resources\ProjectRateSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProjectRateSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('min_rate')
                ->label('Minimum rate')
                ->numeric()
                ->minValue(0.3)
                ->maxValue(2)
                ->step(0.0001)
                ->required()
                ->rule('lt:max_rate'),
            TextInput::make('max_rate')
                ->label('Maximum rate')
                ->numeric()
                ->minValue(0.3)
                ->maxValue(2)
                ->step(0.0001)
                ->required()
                ->rule('gt:min_rate'),
        ]);
    }
}
