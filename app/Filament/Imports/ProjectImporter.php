<?php

namespace App\Filament\Imports;

use App\Models\Project;
use App\Models\ProjectRateSetting;
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
        $rateSettings = ProjectRateSetting::current();

        return [
            ImportColumn::make('company')
                ->label('Company code')
                ->requiredMapping()
                ->relationship(resolveUsing: 'company_code')
                ->rules(['required']),
            ImportColumn::make('order')
                ->rules(['nullable', 'regex:/^\d+[a-z]*$/i', 'max:20']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('pda_code')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('rate')
                ->numeric()
                ->rules([
                    'nullable',
                    'numeric',
                    'min:' . $rateSettings->min_rate,
                    'max:' . $rateSettings->max_rate,
                ]),
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

        $this->record->order = filled($this->record->order)
            ? strtolower(trim((string) $this->record->order))
            : null;
        $this->record->created_by ??= $userId;
        $this->record->rate ??= ProjectRateSetting::current()->min_rate;
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
