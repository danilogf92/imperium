<?php

namespace App\Filament\Resources\ExcelTemplates\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExcelTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'orders' => 'Orders',
                        'project_data' => 'Project Data',
                        default => $state,
                    })
                    ->color(fn (string $state): string => $state === 'orders' ? 'warning' : 'info'),

                TextColumn::make('original_file_name')
                    ->label('File')
                    ->icon('heroicon-o-document-text')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('uploader.name')
                    ->label('Uploaded by')
                    ->placeholder('System')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([5, 10, 20, 50, 100])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
