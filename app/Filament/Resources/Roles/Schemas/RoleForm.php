<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Role Name')
                    ->required(),

                Select::make('company_id')
                    ->label('Company')
                    ->relationship(
                        name: 'company',
                        titleAttribute: 'company_name'
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('permissions')
                    ->label('Permissions')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ]);
    }
}
