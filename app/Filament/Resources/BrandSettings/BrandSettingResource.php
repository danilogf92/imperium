<?php

namespace App\Filament\Resources\BrandSettings;

use App\Filament\Resources\BrandSettings\Pages\EditBrandSetting;
use App\Filament\Resources\BrandSettings\Pages\ListBrandSettings;
use App\Models\BrandSetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BrandSettingResource extends Resource
{
    protected static ?string $model = BrandSetting::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;
    public static function getNavigationLabel(): string
    {
        return __('Logo and brand');
    }
    public static function getModelLabel(): string
    {
        return __('brand settings');
    }
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('System Configuration');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label(__('Application name'))->required()->maxLength(80),
            FileUpload::make('logo_path')
                ->label(__('Application logo'))
                ->disk('public')
                ->directory('branding')
                ->image()
                ->imageEditor()
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                ->maxSize(4096)
                ->helperText(__('PNG, JPG, WEBP or SVG. Recommended: transparent background and horizontal format.'))
                ->columnSpanFull(),
            ColorPicker::make('accent_color')
                ->label(__('Main accent color'))
                ->default('#7DB9F1')
                ->required()
                ->helperText(__('Controls module top lines and PNG chart export buttons.')),
            ColorPicker::make('excel_color')
                ->label(__('Excel export color'))
                ->default('#FDBA74')
                ->required()
                ->helperText(__('Controls chart Excel export buttons.')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('logo_path')->label(__('Logo'))->disk('public')->height(48),
            TextColumn::make('name')->label(__('Application name')),
            TextColumn::make('updated_at')->label(__('Last update'))->dateTime(),
        ])->recordUrl(fn (BrandSetting $record): string => self::getUrl('edit', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBrandSettings::route('/'),
            'edit' => EditBrandSetting::route('/{record}/edit'),
        ];
    }
    public static function getPluralModelLabel(): string
    {
        return __('Brand Settings');
    }

}
