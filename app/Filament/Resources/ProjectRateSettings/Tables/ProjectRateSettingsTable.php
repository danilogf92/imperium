<?php

namespace App\Filament\Resources\ProjectRateSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectRateSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('min_rate')->label('Minimum rate')->numeric(decimalPlaces: 4),
                TextColumn::make('max_rate')->label('Maximum rate')->numeric(decimalPlaces: 4),
                TextColumn::make('updated_at')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])->label('Last updated')->dateTime(),
            ])
            ->recordActions([
                EditAction::make()->button()->outlined()->labeledFrom('md'),
            ]);
    }
}
