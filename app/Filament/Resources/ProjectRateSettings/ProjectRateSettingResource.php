<?php

namespace App\Filament\Resources\ProjectRateSettings;

use App\Filament\Resources\ProjectRateSettings\Pages\EditProjectRateSetting;
use App\Filament\Resources\ProjectRateSettings\Pages\ListProjectRateSettings;
use App\Filament\Resources\ProjectRateSettings\Schemas\ProjectRateSettingForm;
use App\Filament\Resources\ProjectRateSettings\Tables\ProjectRateSettingsTable;
use App\Models\ProjectRateSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProjectRateSettingResource extends Resource
{
    protected static ?string $model = ProjectRateSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Project rate limits';

    protected static ?string $modelLabel = 'project rate limits';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return 'Project Configuration';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return ProjectRateSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectRateSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjectRateSettings::route('/'),
            'edit' => EditProjectRateSetting::route('/{record}/edit'),
        ];
    }
}
