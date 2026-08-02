<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),

                DateTimePicker::make('email_verified_at'),

                TextInput::make('password')
                    ->password()
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => Hash::make($state)),

                Toggle::make('is_active')
                    ->default(true)
                    ->disabled(fn ($record): bool => $record?->is(auth()->user()) ?? false)
                    ->required(),

                Toggle::make('can_access_admin')
                    ->label('Admin access')
                    ->helperText('Admin users can enter Filament. Normal users only access the application.')
                    ->default(false)
                    ->disabled(fn ($record): bool => $record?->is(auth()->user()) ?? false)
                    ->required(),

                Select::make('area_id')
                    ->label('Area')
                    ->relationship('area', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Select::make('roles')
                    ->label('Roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ]);
    }
}
