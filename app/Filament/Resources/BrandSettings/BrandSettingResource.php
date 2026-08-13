<?php

namespace App\Filament\Resources\BrandSettings;

use App\Filament\Resources\BrandSettings\Pages\EditBrandSetting;
use App\Filament\Resources\BrandSettings\Pages\ListBrandSettings;
use App\Models\BrandSetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
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
    protected static ?string $navigationLabel = 'Logo and brand';
    protected static ?string $modelLabel = 'brand settings';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'System Configuration';
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
            TextInput::make('name')->label('Application name')->required()->maxLength(80),
            FileUpload::make('logo_path')
                ->label('Application logo')
                ->disk('public')
                ->directory('branding')
                ->image()
                ->imageEditor()
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'])
                ->maxSize(4096)
                ->helperText('PNG, JPG, WEBP or SVG. Recommended: transparent background and horizontal format.')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('logo_path')->label('Logo')->disk('public')->height(48),
            TextColumn::make('name')->label('Application name'),
            TextColumn::make('updated_at')->label('Last update')->dateTime(),
        ])->recordUrl(fn (BrandSetting $record): string => self::getUrl('edit', ['record' => $record]));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBrandSettings::route('/'),
            'edit' => EditBrandSetting::route('/{record}/edit'),
        ];
    }
}
