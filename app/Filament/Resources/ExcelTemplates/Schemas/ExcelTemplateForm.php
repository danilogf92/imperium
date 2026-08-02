<?php

namespace App\Filament\Resources\ExcelTemplates\Schemas;

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
                    ])
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Only one active configuration is required for each system use.'),

                Select::make('category')
                    ->label('Category')
                    ->options([
                        'orders' => 'Orders',
                        'project_data' => 'Project Data',
                    ])
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
