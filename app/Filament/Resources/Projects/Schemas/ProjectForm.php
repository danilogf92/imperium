<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\InvestmentClassificationEnum;
use App\Enums\InvestmentEnum;
use App\Enums\ProjectJustificationEnum;
use App\Enums\ProjectStateEnum;
use App\Models\ProjectRateSetting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')->label(__('Company'))
                    ->relationship('company', 'company_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('order')
                    ->label(__('Order'))
                    ->helperText(__('Unique within the selected plant. Examples: 1, 5a, 100b.'))
                    ->regex('/^\d+[a-z]*$/i')
                    ->dehydrateStateUsing(fn (?string $state): string => strtolower(trim((string) $state)))
                    ->maxLength(20)
                    ->required(),
                Select::make('created_by')->label(__('planification_activities.created_by'))
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('responsible_id')->label(__('Responsible'))
                    ->relationship('responsible', 'name'),
                TextInput::make('name')->label(__('Name'))
                    ->required(),
                TextInput::make('pda_code')->label(__('PDA code'))
                    ->required(),
                TextInput::make('rate')->label(__('Rate'))
                    ->required()
                    ->numeric()
                    ->minValue(fn(): float => (float) ProjectRateSetting::current()->min_rate)
                    ->maxValue(fn(): float => (float) ProjectRateSetting::current()->max_rate)
                    ->default(fn(): float => (float) ProjectRateSetting::current()->min_rate),
                Select::make('state')->label(__('State'))
                    ->options(ProjectStateEnum::class)
                    ->required(),
                Select::make('investments')->label(__('Investments'))
                    ->options(InvestmentEnum::class)
                    ->required(),
                Select::make('justification')->label(__('Justification'))
                    ->options(ProjectJustificationEnum::class)
                    ->required(),
                Select::make('classification_of_investments')->label(__('Investment classification'))
                    ->options(InvestmentClassificationEnum::class)
                    ->required(),
                Toggle::make('data_uploaded')->label(__('Data uploaded'))
                    ->required(),
                DatePicker::make('quartile_date')->label(__('Quartile date')),
                DatePicker::make('forecast_start_date')->label(__('Forecast start date')),
                DatePicker::make('forecast_end_date')->label(__('Forecast end date')),
                TextInput::make('file_name')->label(__('File name')),
                TextInput::make('upload_pda')->label(__('Upload pda')),
                DatePicker::make('approve_date')->label(__('Approve date')),
                DatePicker::make('close_date')->label(__('Close date')),
            ]);
    }
}
