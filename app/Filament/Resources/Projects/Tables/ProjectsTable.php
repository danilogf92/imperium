<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.company_name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label('Company')
                    ->searchable(),
                TextColumn::make('creator.name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label('Created by')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('responsible.name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('pda_code')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->searchable(),
                TextColumn::make('rate')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('state')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->badge(),
                TextColumn::make('investments')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->badge(),
                TextColumn::make('justification')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('classification_of_investments')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('data_uploaded')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->boolean(),
                TextColumn::make('quartile_date')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('forecast_start_date')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->date()
                    ->sortable(),
                TextColumn::make('forecast_end_date')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->date()
                    ->sortable(),
                TextColumn::make('file_name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('upload_pda')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approve_date')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('close_date')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
