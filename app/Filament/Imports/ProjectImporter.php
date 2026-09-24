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
                ->label(__('Company code'))
                ->requiredMapping()
                ->relationship(resolveUsing: 'company_code')
                ->rules(['required']),
            ImportColumn::make('order')
                ->label(__('Order'))
                ->rules(['nullable', 'regex:/^\d+[a-z]*$/i', 'max:20']),
            ImportColumn::make('name')
                ->label(__('Name'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('pda_code')
                ->label(__('PDA Code'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('rate')
                ->label(__('Rate'))
                ->numeric()
                ->rules([
                    'nullable',
                    'numeric',
                    'min:' . $rateSettings->min_rate,
                    'max:' . $rateSettings->max_rate,
                ]),
            ImportColumn::make('state')
                ->label(__('State'))
                ->rules(['nullable', 'in:Capex,Planning,Execution,Finished']),
            ImportColumn::make('investments')
                ->label(__('Investments'))
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('justification')
                ->label(__('Justification'))
                ->rules(['nullable', 'in:Normal Capex,Special Project']),
            ImportColumn::make('classification_of_investments')
                ->label(__('Classification Of Investments'))
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('quartile_date')
                ->label(__('Quartile Date'))
                ->rules(['date']),
            ImportColumn::make('forecast_start_date')
                ->label(__('Forecast Start Date'))
                ->rules(['date']),
            ImportColumn::make('forecast_end_date')
                ->label(__('Forecast End Date'))
                ->rules(['date']),
            ImportColumn::make('file_name')
                ->label(__('File Name'))
                ->rules(['max:255']),
            ImportColumn::make('upload_pda')
                ->label(__('Upload PDA'))
                ->rules(['max:255']),
            ImportColumn::make('approve_date')
                ->label(__('Approve Date'))
                ->rules(['date']),
            ImportColumn::make('close_date')
                ->label(__('Close Date'))
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
        $body = __('Import completed: :count projects processed.', ['count' => Number::format($import->successful_rows)]);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . __(':count rows could not be imported.', ['count' => Number::format($failedRowsCount)]);
        }

        return $body;
    }

    public function getJobConnection(): ?string
    {
        return 'sync';
    }
}
