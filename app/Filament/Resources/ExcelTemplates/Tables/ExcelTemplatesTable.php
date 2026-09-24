<?php

namespace App\Filament\Resources\ExcelTemplates\Tables;

use Filament\Actions\DeleteAction;
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
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_global')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('All users'))
                    ->boolean(),

                TextColumn::make('companies.company_name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('Allowed plants'))
                    ->badge(),

                TextColumn::make('category')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('Category'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'orders' => __('Orders'),
                        'project_data' => __('Project Data'),
                        'project_ideas' => __('Project Ideas'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'orders' => 'warning',
                        'project_ideas' => 'success',
                        default => 'info',
                    }),

                TextColumn::make('original_file_name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('File'))
                    ->icon('heroicon-o-document-text')
                    ->limit(25)
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label(__('Available'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('uploader.name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('Uploaded by'))
                    ->placeholder(__('System'))
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('notes.updated'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([5, 10, 20, 50, 100])
            ->recordActions([
                EditAction::make()->button()->outlined()->labeledFrom('md'),
                DeleteAction::make(),
            ]);
    }
}
