<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label('Role')
                    ->badge()
                    ->separator(','),

                TextColumn::make('email')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),

                IconColumn::make('can_access_admin')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label('Admin access')
                    ->boolean(),


                TextColumn::make('area.name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label('Area')
                    ->sortable()
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
