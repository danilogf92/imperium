<?php

namespace App\Exports;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;
use App\Models\Project;
use App\Models\User;
use App\Support\Project\ProjectTableDefinition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectExport
{
    private const HEADERS = [
        ...ProjectTableDefinition::COLUMN_OPTIONS,
        'id' => 'ID', 'order' => 'Order', 'plant' => 'Plant', 'pda_code' => 'PDA Code',
        'forecast_start_year' => 'Forecast Start Year',
        'forecast_start_date' => 'Forecast Start Date', 'investments' => 'Investments',
        'state' => 'State', 'budgeted_euros' => 'Budgeted Euros',
        'forecast_end_date' => 'Forecast End Date', 'real_euros' => 'Real Euros',
        'rate' => 'Rate', 'budgeted_dollars' => 'Budgeted Dollars',
        'real_dollars' => 'Real Dollars', 'upload_pda' => 'Upload PDA',
        'handover_certificate' => 'Project Handover Certificate',
        'name' => 'Name', 'links' => 'Links', 'classification' => 'Classification',
        'justification' => 'Justification', 'creator' => 'Created By',
        'responsible' => 'Responsible', 'data_uploaded' => 'Data Uploaded',
        'quartile_date' => 'Quartile Date', 'approve_date' => 'Approved Date',
        'close_date' => 'Close Date', 'file_name' => 'Document Name',
        'created_at' => 'Created At', 'updated_at' => 'Updated At',
    ];

    public function download(User $user, array $filters = []): BinaryFileResponse
    {
        $columns = array_values(array_intersect(
            ($filters['columns'] ?? []) ?: ProjectTableDefinition::DEFAULT_COLUMNS,
            array_diff(array_keys(self::HEADERS), ['actions'])
        ));
        $query = Project::query()
            ->with(['company:id,company_code,company_name', 'creator:id,name', 'responsible:id,name'])
            ->withSum('data as budgeted_euros', 'global_price_euros')
            ->withSum('data as real_euros', 'real_value_euros')
            ->withSum('data as executed_euros', 'executed_euros')
            ->withSum('data as booked_euros', 'booked_euros')
            ->withSum('data as budgeted_dollars', 'global_price')
            ->withSum('data as real_dollars', 'real_value')
            ->withSum('data as executed_dollars', 'executed_dollars')
            ->withSum('data as booked', 'booked')
            ->whereIn('company_id', $user->companiesForPermissionQuery(ProjectPermissionEnum::Export)
                ->select('companies.id')->reorder())
            ->when(filled($filters['search'] ?? ''), function (Builder $query) use ($filters): void {
                $search = '%'.trim($filters['search']).'%';
                $query->where(fn (Builder $query) => $query
                    ->where('name', 'like', $search)->orWhere('order', 'like', $search)
                    ->orWhere('pda_code', 'like', $search)
                    ->orWhere('state', 'like', $search)->orWhere('classification_of_investments', 'like', $search)
                    ->orWhere('investments', 'like', $search)->orWhere('justification', 'like', $search));
            })
            ->when(($filters['years'] ?? []) !== [], function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    foreach ($filters['years'] as $year) {
                        $query->orWhereYear('forecast_start_date', $year);
                    }
                });
            })
            ->when(($filters['states'] ?? []) !== [], fn (Builder $query) => $query->whereIn('state', $filters['states']))
            ->when(($filters['classifications'] ?? []) !== [], fn (Builder $query) => $query->whereIn('classification_of_investments', $filters['classifications']))
            ->when(($filters['investments'] ?? []) !== [], fn (Builder $query) => $query->whereIn('investments', $filters['investments']))
            ->when(($filters['plants'] ?? []) !== [], fn (Builder $query) => $query->whereHas('company',
                fn (Builder $query) => $query->whereIn('company_code', $filters['plants'])));

        if ($filters['orderByProject'] ?? false) {
            $query->where('state', '!=', 'Finished')->where('data_uploaded', true)->whereHas('data')
                ->addSelect(['rest' => Data::query()->selectRaw(
                    'COALESCE(SUM(global_price_euros), 0) - COALESCE(SUM(booked_euros), 0)'
                )->whereColumn('project_id', 'projects.id')])->orderByDesc('rest');
        } elseif (($filters['sortBy'] ?? 'order') === 'order') {
            $this->applyNaturalOrder($query, $filters['sortDir'] ?? 'ASC');
        } else {
            $requestedSort = ($filters['sortBy'] ?? '') === 'classification'
                ? 'classification_of_investments' : ($filters['sortBy'] ?? 'order');
            $sortBy = in_array($requestedSort, ProjectTableDefinition::SORTABLE_COLUMNS, true)
                ? $requestedSort
                : 'order';
            $query->orderBy($sortBy, strtoupper($filters['sortDir'] ?? 'ASC') === 'ASC' ? 'ASC' : 'DESC');
        }

        $projects = $query->get();
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Projects');
        $sheet->fromArray(array_map(fn (string $column) => self::HEADERS[$column], $columns), null, 'A1');
        foreach ($projects as $index => $project) {
            $sheet->fromArray(array_map(fn (string $column) => $this->value($project, $column), $columns), null, 'A'.($index + 2));
        }
        $lastColumn = Coordinate::stringFromColumnIndex(max(count($columns), 1));
        $lastRow = max($projects->count() + 1, 2);
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        foreach ($columns as $index => $column) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index + 1))
                ->setWidth(in_array($column, ['name', 'links', 'file_name'], true) ? 35 : 18);
        }
        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
        $sheet->freezePane('A2');

        $directory = storage_path('app/private/exports');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $path = $directory.'/projects-'.uniqid('', true).'.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()->download($path, 'projects.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function value(Project $project, string $column): mixed
    {
        return match ($column) {
            'links' => route('projects.data', ['project' => $project->slug]),
            'upload_pda' => filled($project->upload_pda) ? 'Yes' : 'No',
            'handover_certificate' => filled($project->handover_certificate_path) ? 'Yes' : 'No',
            'project_ideas' => filled($project->project_idea_path) ? 'Yes' : 'No',
            'state', 'investments', 'justification' => $project->{$column}?->value,
            'classification' => $project->classification_of_investments?->value,
            'plant' => $project->company?->company_name,
            'creator' => $project->creator?->name,
            'responsible' => $project->responsible?->name,
            'data_uploaded' => $project->data_uploaded ? 'Yes' : 'No',
            'forecast_start_year' => $project->forecast_start_date?->format('Y'),
            'forecast_start_date' => $project->forecast_start_date?->format('Y-m-d'),
            'forecast_end_date', 'quartile_date', 'approve_date', 'close_date' => $project->{$column}?->format('Y-m-d'),
            'created_at', 'updated_at' => $project->{$column}?->format('Y-m-d H:i:s'),
            default => $project->{$column},
        };
    }

    private function applyNaturalOrder(Builder $query, string $direction): void
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $driver = DB::connection()->getDriverName();
        $quotedColumn = $driver === 'mysql' ? '`projects`.`order`' : 'projects."order"';
        $integerType = $driver === 'mysql' ? 'UNSIGNED' : 'INTEGER';

        $query
            ->orderByRaw("CASE WHEN {$quotedColumn} IS NULL THEN 1 ELSE 0 END")
            ->orderByRaw("CAST({$quotedColumn} AS {$integerType}) {$direction}")
            ->orderByRaw("{$quotedColumn} {$direction}");
    }
}
