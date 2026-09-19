<?php

namespace App\Services\Tools;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date;

final class SapDataImporter
{
    public const COLUMNS = [
        'texto de pedido' => 'description',
        'sap order' => 'sap_order',
        'denominacion cuenta contrapartida' => 'supplier',
        'actual usd' => 'real_value',
        'fecha contable' => 'accounting_date',
        'fecha documento' => 'document_date',
        'quantity' => 'qty',
    ];

    public function mapping(User $user, string $token, ?array $mapping = null): array
    {
        $headers = app(ExcelFilterService::class)->analyze($user->id, $token)['headers'];
        if ($mapping === null) {
            $mapping = $this->suggestMapping($headers);
        }
        $validated = [];
        foreach (self::COLUMNS as $field) {
            $header = $mapping[$field] ?? null;
            if (! is_string($header) || ! in_array($header, $headers, true)) {
                throw ValidationException::withMessages(["sapMapping.{$field}" => __('sap.error_column')]);
            }
            $validated[$field] = $header;
        }
        if (count(array_unique($validated)) !== count($validated)) {
            throw ValidationException::withMessages(['sapMapping' => __('sap.error_unique')]);
        }

        return $validated;
    }

    public function suggestMapping(array $headers): array
    {
        $aliases = [
            'description' => ['texto de pedido', 'texto pedido', 'order text', 'description'],
            'sap_order' => ['sap order', 'sap orden', 'orden sap', 'orden'],
            'supplier' => ['denominacion cuenta contrapartida', 'denominacion', 'supplier'],
            'real_value' => ['actual usd', 'real value', 'real_value', 'real $', 'real usd', 'valor/mon.inf.'],
            'accounting_date' => ['accounting date', 'fecha contable', 'fe.contab.'],
            'document_date' => ['document date', 'fecha documento', 'fecha doc.'],
            'qty' => ['quantity', 'cantidad', 'qty'],
        ];
        $normalized = array_map(fn ($header) => Str::lower(Str::ascii(trim($header))), $headers);
        $mapping = [];
        foreach ($aliases as $field => $names) {
            $mapping[$field] = '';
            foreach ($names as $name) {
                $index = array_search($name, $normalized, true);
                if ($index !== false) {
                    $mapping[$field] = $headers[$index];
                    break;
                }
            }
        }

        return $mapping;
    }

    public function matches(User $user, int $companyId, string $token, array $mapping): array
    {
        return array_values(array_filter($this->matchReport($user, $companyId, $token, $mapping), fn ($project) => $project['count'] > 0));
    }

    public function matchReport(User $user, int $companyId, string $token, array $mapping): array
    {
        abort_unless($user->hasPermissionInCompany(ProjectPermissionEnum::Update, $companyId), 403);
        $mapping = $this->mapping($user, $token, $mapping);
        $projects = Project::where('company_id', $companyId)->whereNotNull('sap_order')->where('sap_order', '<>', '')->orderBy('name')->get();
        $orders = $projects->map(fn ($project) => trim((string) $project->sap_order))->all();
        $counts = [];
        foreach (app(ExcelFilterService::class)->sourceRows($user->id, $token, $mapping['real_value']) as $row) {
            $order = trim($row[$mapping['sap_order']]);
            if (! in_array($order, $orders, true)) {
                continue;
            }
            $counts[$order] = ($counts[$order] ?? 0) + 1;
        }

        return $projects->filter(fn ($project) => isset($counts[trim((string) $project->sap_order)]))
            ->map(function ($project) use ($counts) {
                return ['id' => $project->id, 'name' => $project->name, 'sap_order' => $project->sap_order,
                    'count' => $counts[trim((string) $project->sap_order)]];
            })->values()->all();
    }

    public function importSelected(User $user, int $companyId, string $token, array $mapping, array $projectIds): int
    {
        abort_unless($user->hasPermissionInCompany(ProjectPermissionEnum::Update, $companyId), 403);
        if ($projectIds === []) {
            throw ValidationException::withMessages(['selectedProjects' => __('sap.error_select')]);
        }


        return DB::transaction(function () use ($user, $companyId, $token, $mapping, $projectIds) {
            $ids = array_values(array_unique(array_map('intval', $projectIds)));
            $projects = Project::where('company_id', $companyId)->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
            abort_unless($projects->count() === count($ids), 403);
            $count = 0;
            foreach ($projects as $project) {
                $count += $this->import($user, $project, $token, $mapping);
            }

            return $count;
        });
    }

    public function rows(User $user, Project $project, string $token, ?array $mapping = null): array
    {
        $this->authorize($user, $project);
        if (trim((string) $project->sap_order) === '') {
            throw ValidationException::withMessages(['sapImport' => __('sap.error_project')]);
        }
        $excel = app(ExcelFilterService::class);
        $mapping = $this->mapping($user, $token, $mapping);
        $result = [];
        foreach ($excel->sourceRows($user->id, $token, [$mapping['real_value'], $mapping['qty']]) as $source) {
            if (trim($source[$mapping['sap_order']]) !== trim((string) $project->sap_order)) {
                continue;
            }
            $accounting = $this->date($source[$mapping['accounting_date']]);
            $row = [];
            foreach ($mapping as $field => $header) {
                $row[$field] = trim($source[$header]);
            }
            if (mb_strlen($row['description']) > 10000) {
                throw ValidationException::withMessages(['sapImport' => __('sap.error_text_length', ['max' => 10000])]);
            }
            if (mb_strlen($row['supplier']) > 255) {
                throw ValidationException::withMessages(['sapImport' => __('sap.error_supplier_length')]);
            }
            $quantity = (new ExcelFilterValueConverter)->convert($row['qty'], 'number', __('sap.qty'));
            if ($quantity['type'] !== 'number' || abs((float) $quantity['value']) > 99999999.99) {
                throw ValidationException::withMessages(['sapImport' => __('sap.error_quantity')]);
            }
            $row['qty'] = $quantity['value'];
            $row['accounting_date'] = $accounting;
            $row['document_date'] = $this->date($source[$mapping['document_date']]);
            $value = (new ExcelFilterValueConverter)->convert($row['real_value'], 'number', __('sap.real_value'));
            if ($value['type'] !== 'number' || abs((float) $value['value']) > 99999999.99 || strlen($row['sap_order']) > 255) {
                throw ValidationException::withMessages(['sapImport' => __('sap.error_value')]);
            }
            $row['real_value'] = $value['value'];
            if ((float) $project->rate <= 0) {
                throw ValidationException::withMessages(['sapImport' => __('sap.error_rate')]);
            }
            $row['real_value_euros'] = round((float) $row['real_value'] / (float) $project->rate, 2);
            if (abs($row['real_value_euros']) > 99999999.99) {
                throw ValidationException::withMessages(['sapImport' => __('sap.error_euros')]);
            }
            $result[] = [...$row, 'project_id' => $project->id, 'order_year' => (int) substr($accounting, 0, 4)];
        }
        if ($result === []) {
            throw ValidationException::withMessages(['sapImport' => __('sap.error_no_rows')]);
        }

        return $result;
    }

    public function import(User $user, Project $project, string $token, ?array $mapping = null): int
    {
        return DB::transaction(function () use ($user, $project, $token, $mapping) {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            $rows = $this->rows($user, $project, $token, $mapping);
            Data::where('project_id', $project->id)
                ->whereRaw('TRIM(sap_order) = ?', [trim((string) $project->sap_order)])
                ->delete();
            foreach ($rows as $row) {
                Data::create($row);
            }
            $project->update(['data_uploaded' => true]);

            return count($rows);
        });
    }

    private function authorize(User $user, Project $project): void
    {
        abort_unless($user->hasPermissionInCompany(ProjectPermissionEnum::Update, (int) $project->company_id), 403);
    }

    private function date(string $value): string
    {
        $value = trim($value);
        if (is_numeric($value) && (float) $value > 0 && (float) $value < 100000) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        foreach (['!Y-m-d', '!d/m/Y', '!d.m.Y', '!d-m-Y', '!m/d/Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date->format('Y-m-d');
            }
        }
        throw ValidationException::withMessages(['sapImport' => __('sap.error_date', ['value' => $value])]);
    }
}
