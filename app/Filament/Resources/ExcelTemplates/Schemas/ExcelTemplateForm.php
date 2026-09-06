<?php

namespace App\Filament\Resources\ExcelTemplates\Schemas;

use App\Models\ExcelTemplate;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
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
                    ->live()
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

                Toggle::make('is_global')
                    ->label('All users')
                    ->helperText('Enabled: available in General for every user. Disabled: only the selected plants can see and download the file.')
                    ->default(true)
                    ->live()
                    ->required(),

                Select::make('companies')
                    ->label('Allowed plants')
                    ->helperText('Users can see and download this file when their assigned roles include any of the selected companies.')
                    ->relationship('companies', 'company_name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->visible(fn (Get $get): bool => ! $get('is_global'))
                    ->required(fn (Get $get): bool => ! $get('is_global')),

                FileUpload::make('file_path')
                    ->label('File')
                    ->disk('local')
                    ->directory('excel-templates')
                    ->visibility('private')
                    ->acceptedFileTypes(fn (Get $get): array => in_array($get('template_key'), ['order_export', 'project_data_import'], true)
                        ? [ExcelTemplate::FILE_TYPES['xlsx'], ExcelTemplate::FILE_TYPES['xls']]
                        : array_values(ExcelTemplate::FILE_TYPES))
                    ->rules(fn (Get $get): array => [in_array($get('template_key'), ['order_export', 'project_data_import'], true)
                        ? 'extensions:xlsx,xls' : 'extensions:pdf,xlsx,xls,ppt,pptx'])
                    ->maxSize(10240)
                    ->storeFileNamesIn('original_file_name')
                    ->downloadable()
                    ->required()
                    ->helperText('PDF, Excel (.xlsx, .xls), PowerPoint (.pptx, .ppt). Maximum: 10 MB. System order and data templates must remain Excel files.')
                    ->columnSpanFull(),

                Hidden::make('is_active')
                    ->default(true)
                    ->dehydrateStateUsing(fn (): bool => true),

                TextInput::make('disk')
                    ->default('local')
                    ->hidden()
                    ->dehydrated(),
            ])
            ->columns(2);
    }
}
