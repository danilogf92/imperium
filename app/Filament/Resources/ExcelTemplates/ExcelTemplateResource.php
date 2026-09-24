<?php

namespace App\Filament\Resources\ExcelTemplates;

use App\Filament\Resources\ExcelTemplates\Pages\CreateExcelTemplate;
use App\Filament\Resources\ExcelTemplates\Pages\EditExcelTemplate;
use App\Filament\Resources\ExcelTemplates\Pages\ListExcelTemplates;
use App\Filament\Resources\ExcelTemplates\Schemas\ExcelTemplateForm;
use App\Filament\Resources\ExcelTemplates\Tables\ExcelTemplatesTable;
use App\Models\ExcelTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExcelTemplateResource extends Resource
{
    protected static ?string $model = ExcelTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    public static function getNavigationLabel(): string
    {
        return __('Files & Templates');
    }

    public static function getModelLabel(): string
    {
        return __('file or template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Files & Templates');
    }

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Project Configuration');
    }

    public static function form(Schema $schema): Schema
    {
        return ExcelTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExcelTemplatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExcelTemplates::route('/'),
            'create' => CreateExcelTemplate::route('/create'),
            'edit' => EditExcelTemplate::route('/{record}/edit'),
        ];
    }
}
