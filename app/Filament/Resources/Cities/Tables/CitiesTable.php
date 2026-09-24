<?php

namespace App\Filament\Resources\Cities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('country.country_name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label('Country')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('City Name')
                    ->searchable(),
                TextColumn::make('city_code')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label('City Code')
                    ->searchable(),
                TextColumn::make('state')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label('State')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->button()->outlined()->labeledFrom('md'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
