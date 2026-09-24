<?php

namespace App\Filament\Resources\Permissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label('Guard')
                    ->sortable(),

                TextColumn::make('created_at')
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
