<?php

namespace App\Filament\Resources\Milestones\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MilestoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(120),

                TextInput::make('code')
                    ->label(__('Code'))
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(
                        fn (?string $state): string => Str::upper(trim((string) $state))
                    )
                    ->helperText(__('Short unique identifier, for example: PO or WMAT.')),

                ColorPicker::make('color')
                    ->label(__('Export color'))
                    ->required()
                    ->default('#2563EB')
                    ->regex('/^#[0-9A-Fa-f]{6}$/'),

                ColorPicker::make('view_color')
                    ->label(__('View color'))
                    ->required()
                    ->default('#2563EB')
                    ->regex('/^#[0-9A-Fa-f]{6}$/'),
            ])
            ->columns(4);
    }
}
