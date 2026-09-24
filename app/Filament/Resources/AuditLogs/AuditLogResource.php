<?php

namespace App\Filament\Resources\AuditLogs;

use App\Enums\AuditPermissionEnum;
use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Filament\Resources\AuditLogs\Tables\AuditLogsTable;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationLabel(): string
    {
        return __('Audit Logs');
    }

    public static function getPluralModelLabel(): string
    {
        return __('audit logs');
    }

    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): ?string
    {
        return __('System');
    }

    public static function table(Table $table): Table
    {
        return AuditLogsTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(AuditPermissionEnum::View->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditLogs::route('/'),
        ];
    }
    public static function getModelLabel(): string
    {
        return __('Audit Log');
    }

}
