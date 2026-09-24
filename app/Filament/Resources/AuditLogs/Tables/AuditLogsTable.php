<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Models\AuditLog;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $companyIds = auth()->user()?->availableCompaniesQuery()
                    ->pluck('companies.id')
                    ->all() ?? [];

                return $query
                    ->where(function (Builder $query) use ($companyIds): void {
                        $query
                            ->whereNull('company_id')
                            ->orWhereIn('company_id', $companyIds);
                    })
                    ->with([
                        'user:id,name,email',
                        'company:id,company_name',
                        'project:id,name',
                    ]);
            })
            ->columns([
                TextColumn::make('created_at')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('sap.date'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('activity_control.user'))
                    ->placeholder(__('System'))
                    ->description(fn (AuditLog $record): ?string => $record->user?->email)
                    ->searchable(),
                TextColumn::make('event')->label(__('Event'))
                    ->formatStateUsing(fn (string $state): string => __($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('auditable_type')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('Module'))
                    ->formatStateUsing(fn (string $state): string => __(Str::headline(class_basename($state))))
                    ->searchable(),
                TextColumn::make('auditable_id')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('Record ID'))
                    ->sortable(),
                TextColumn::make('company.company_name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('Company'))
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('project.name')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('activity_control.project'))
                    ->placeholder('—')
                    ->limit(35)
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->extraCellAttributes(['data-mobile-secondary' => 'true'])
                    ->extraHeaderAttributes(['data-mobile-secondary' => 'true'])
                    ->label(__('IP'))
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options([
                        'created' => __('Created'),
                        'updated' => __('Updated'),
                        'deleted' => __('Deleted'),
                    ]),
                SelectFilter::make('user_id')
                    ->label(__('activity_control.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('auditable_type')
                    ->label(__('Module'))
                    ->options(fn (): array => AuditLog::query()
                        ->distinct()
                        ->pluck('auditable_type')
                        ->mapWithKeys(fn (string $type): array => [
                            $type => __(Str::headline(class_basename($type))),
                        ])
                        ->all()),
                SelectFilter::make('company_id')
                    ->label(__('Company'))
                    ->relationship('company', 'company_name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('project_id')
                    ->label(__('activity_control.project'))
                    ->relationship('project', 'name')
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('details')
                    ->label(__('Details'))
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (AuditLog $record): string =>
                        'Audit details #' . $record->getKey())
                    ->modalWidth(Width::SevenExtraLarge)
                    ->modalContent(fn (AuditLog $record) => view(
                        'filament.audit-log-details',
                        ['record' => $record],
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('Close')),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([5, 10, 20, 50, 100]);
    }
}
