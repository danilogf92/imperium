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
                ->label(__('Minimum rate'))
                ->numeric()
                ->minValue(0.3)
                ->maxValue(2)
                ->step(0.0001)
                ->required(),
            TextInput::make('max_rate')
                ->label(__('Maximum rate'))
                ->numeric()
                ->minValue(0.3)
                ->maxValue(2)
                ->step(0.0001)
                ->required(),
        ]);
    }
}
