<?php

namespace App\Filament\Imports;

use App\Models\Project;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProjectImporter extends Importer
{
    protected static ?string $model = Project::class;

    protected static bool $shouldPreventFormulaInjection = true;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('company')
                ->label('Company code')
                ->requiredMapping()
                ->relationship(resolveUsing: 'company_code')
                ->rules(['required']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('pda_code')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('rate')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('base_budgeted')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('base_budgeted_euros')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
            ImportColumn::make('state')
                ->rules(['nullable', 'in:Capex,Planning,Execution,Finished']),
            ImportColumn::make('investments')
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('justification')
                ->rules(['nullable', 'in:Normal Capex,Special Project']),
            ImportColumn::make('classification_of_investments')
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('quartile_date')
                ->rules(['date']),
            ImportColumn::make('forecast_start_date')
                ->rules(['date']),
            ImportColumn::make('forecast_end_date')
                ->rules(['date']),
            ImportColumn::make('file_name')
                ->rules(['max:255']),
            ImportColumn::make('upload_pda')
                ->rules(['max:255']),
            ImportColumn::make('approve_date')
                ->rules(['date']),
            ImportColumn::make('close_date')
                ->rules(['date']),
        ];
    }

    public function resolveRecord(): Project
    {
        return Project::query()->firstOrNew([
            'pda_code' => $this->data['pda_code'],
        ]);
    }

    protected function beforeSave(): void
    {
        $userId = $this->import->user->getAuthIdentifier();

        $this->record->created_by ??= $userId;
        $this->record->rate ??= 0;
        $this->record->base_budgeted ??= 0;
        $this->record->base_budgeted_euros ??= 0;
        $this->record->state ??= 'Capex';
        $this->record->investments ??= 'Innovation';
        $this->record->justification ??= 'Normal Capex';
        $this->record->classification_of_investments ??= 'Buildings';
        $this->record->data_uploaded = false;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importacion termino: ' . Number::format($import->successful_rows) . ' proyectos procesados.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' filas no pudieron importarse.';
        }

        return $body;
    }

    public function getJobConnection(): ?string
    {
        return 'sync';
    }
}
