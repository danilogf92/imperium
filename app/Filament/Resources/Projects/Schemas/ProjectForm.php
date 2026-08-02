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
                Select::make('company_id')
                    ->relationship('company', 'company_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('created_by')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('responsible_id')
                    ->relationship('responsible', 'name'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('pda_code')
                    ->required(),
                TextInput::make('rate')
                    ->required()
                    ->numeric()
                    ->minValue(fn(): float => (float) ProjectRateSetting::current()->min_rate)
                    ->maxValue(fn(): float => (float) ProjectRateSetting::current()->max_rate)
                    ->default(fn(): float => (float) ProjectRateSetting::current()->min_rate),
                Select::make('state')
                    ->options(ProjectStateEnum::class)
                    ->required(),
                Select::make('investments')
                    ->options(InvestmentEnum::class)
                    ->required(),
                Select::make('justification')
                    ->options(ProjectJustificationEnum::class)
                    ->required(),
                Select::make('classification_of_investments')
                    ->options(InvestmentClassificationEnum::class)
                    ->required(),
                Toggle::make('data_uploaded')
                    ->required(),
                DatePicker::make('quartile_date'),
                DatePicker::make('forecast_start_date'),
                DatePicker::make('forecast_end_date'),
                TextInput::make('file_name'),
                TextInput::make('upload_pda'),
                DatePicker::make('approve_date'),
                DatePicker::make('close_date'),
            ]);
    }
}
