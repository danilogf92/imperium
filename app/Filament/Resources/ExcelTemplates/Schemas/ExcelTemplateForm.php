<?php

namespace App\Filament\Resources\ExcelTemplates\Schemas;

use App\Models\ExcelTemplate;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExcelTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(120),

                Select::make('template_key')
                    ->label('System use')
                    ->options([
                        'order_export' => 'Orders - generated order file',
                        'project_data_import' => 'Project Data - import base file',
                        'project_ideas_template' => 'Project Ideas - downloadable template',
                    ])
                    ->placeholder('Library only (no system use)')
                    ->unique(ignoreRecord: true)
                    ->helperText('Optional. Select this only when the application must use this file automatically. Each system use accepts one template.'),

                // TextInput::make('category')
                //     ->label('Category')
                //     ->required()
                //     ->maxLength(40)
                //     ->datalist(fn (): array => ExcelTemplate::query()
                //         ->select('category')
                //         ->distinct()
                //         ->orderBy('category')
                //         ->pluck('category')
                //         ->all())
                //     ->helperText('Select an existing category or type a new one.'),

                Select::make('category')
                    ->label('Category')
                    ->options(function (?string $state): array {
                        $categories = ExcelTemplate::query()
                            ->whereNotNull('category')
                            ->select('category')
                            ->distinct()
                            ->orderBy('category')
                            ->pluck('category', 'category')
                            ->all();

                        if ($state && ! isset($categories[$state])) {
                            $categories[$state] = $state;
                        }

                        return $categories;
                    })
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('category')
                            ->label('New category')
                            ->required()
                            ->maxLength(40),
                    ])
                    ->createOptionUsing(
                        fn (array $data): string => trim($data['category'])
                    )
                    ->required(),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),

                FileUpload::make('file_path')
                    ->label('Excel file')
                    ->disk('local')
                    ->directory('excel-templates')
                    ->visibility('private')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->maxSize(20480)
                    ->storeFileNamesIn('original_file_name')
                    ->downloadable()
                    ->required()
                    ->helperText('Accepted formats: .xlsx and .xls. Maximum size: 20 MB.')
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Available for users')
                    ->default(true)
                    ->required(),

                TextInput::make('disk')
                    ->default('local')
                    ->hidden()
                    ->dehydrated(),
            ])
            ->columns(2);
    }
}
