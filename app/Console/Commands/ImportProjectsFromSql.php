<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ImportProjectsFromSql extends Command
{
    /**
     * Ejemplos:
     *
     * php artisan projects:import-sql storage/app/imports/database.sql
     * php artisan projects:import-sql storage/app/imports/database.sql --update
     * php artisan projects:import-sql storage/app/imports/database.sql --keep-legacy
     */
    protected $signature = 'projects:import-sql
        {file : Ruta absoluta o relativa del archivo SQL}
        {--update : Actualiza los proyectos existentes cuando coincide el pda_code}
        {--keep-legacy : No elimina la tabla temporal legacy_projects al terminar}';

    protected $description = 'Importa proyectos desde un dump SQL antiguo y adapta sus campos al esquema actual';

    public function handle(): int
    {
        $path = $this->resolvePath((string) $this->argument('file'));

        if (! is_file($path) || ! is_readable($path)) {
            $this->error("No se puede leer el archivo SQL: {$path}");

            return self::FAILURE;
        }

        if (! Schema::hasTable('projects')) {
            $this->error('No existe la tabla projects en la base de datos actual.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('companies')) {
            $this->error('No existe la tabla companies en la base de datos actual.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('users')) {
            $this->error('No existe la tabla users en la base de datos actual.');

            return self::FAILURE;
        }

        try {
            $this->info('Leyendo archivo SQL...');
            $sql = file_get_contents($path);

            if ($sql === false || trim($sql) === '') {
                throw new RuntimeException('El archivo SQL está vacío o no pudo leerse.');
            }

            $legacySql = $this->extractLegacySql($sql);

            $this->info('Creando y cargando la tabla temporal legacy_projects...');
            DB::unprepared($legacySql);

            if (! Schema::hasTable('legacy_projects')) {
                throw new RuntimeException(
                    'El archivo no creó la tabla legacy_projects. Verifica que corresponda al dump esperado.'
                );
            }

            $companyIds = $this->getCompanyIds();
            $createdBy = DB::table('users')->orderBy('id')->value('id');

            if ($createdBy === null) {
                throw new RuntimeException(
                    'No existe ningún usuario. Debes crear al menos uno antes de importar los proyectos.'
                );
            }

            $missingCompanies = collect($companyIds)
                ->filter(fn($id) => $id === null)
                ->keys()
                ->values()
                ->all();

            if ($missingCompanies !== []) {
                throw new RuntimeException(
                    'No se encontraron las siguientes empresas en companies.company_code: '
                        . implode(', ', $missingCompanies)
                );
            }

            $duplicateCodes = DB::table('legacy_projects')
                ->select('pda_code')
                ->groupBy('pda_code')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('pda_code')
                ->flip();

            $total = DB::table('legacy_projects')->count();
            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $created = 0;
            $updated = 0;
            $skipped = 0;
            $withoutCompany = 0;
            $updateExisting = (bool) $this->option('update');

            DB::table('legacy_projects')
                ->orderBy('id')
                ->chunkById(100, function ($rows) use (
                    $companyIds,
                    $createdBy,
                    $duplicateCodes,
                    $updateExisting,
                    &$created,
                    &$updated,
                    &$skipped,
                    &$withoutCompany,
                    $bar
                ): void {
                    foreach ($rows as $legacy) {
                        $companyId = $this->resolveCompanyId(
                            (string) $legacy->pda_code,
                            $companyIds
                        );

                        if ($companyId === null) {
                            $withoutCompany++;
                            $bar->advance();
                            continue;
                        }

                        $pdaCode = trim((string) $legacy->pda_code);

                        if ($duplicateCodes->has($legacy->pda_code)) {
                            $pdaCode .= '-' . $legacy->id;
                        }

                        $project = [
                            'company_id' => $companyId,
                            'created_by' => $createdBy,
                            'responsible_id' => null,
                            'name' => $legacy->name,
                            'pda_code' => $pdaCode,
                            'rate' => $legacy->rate,
                            'state' => $legacy->state === 'Planification'
                                ? 'Planning'
                                : $legacy->state,
                            'investments' => $legacy->investments,
                            'justification' => $legacy->justification,
                            'classification_of_investments' => $legacy->classification_of_investments,
                            'data_uploaded' => (bool) $legacy->data_uploaded,
                            'quartile_date' => $legacy->quartile_date,

                            // Cambio de nombres entre el sistema anterior y el actual.
                            'forecast_start_date' => $legacy->start_date,
                            'forecast_end_date' => $legacy->finish_date,

                            'file_name' => $legacy->file_name,
                            'upload_pda' => (bool) $legacy->upload_pda
                                ? $legacy->file_name
                                : null,
                            'approve_date' => $legacy->approve_date,
                            'close_date' => $legacy->close_date,
                            'created_at' => $legacy->created_at ?? now(),
                            'updated_at' => $legacy->updated_at ?? now(),
                        ];

                        $exists = DB::table('projects')
                            ->where('pda_code', $pdaCode)
                            ->exists();

                        if ($exists && ! $updateExisting) {
                            $skipped++;
                            $bar->advance();
                            continue;
                        }

                        if ($exists) {
                            DB::table('projects')
                                ->where('pda_code', $pdaCode)
                                ->update($project);

                            $updated++;
                        } else {
                            DB::table('projects')->insert($project);
                            $created++;
                        }

                        $bar->advance();
                    }
                }, 'id');

            $bar->finish();
            $this->newLine(2);

            if (! $this->option('keep-legacy')) {
                Schema::dropIfExists('legacy_projects');
                $this->line('La tabla temporal legacy_projects fue eliminada.');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info('Importación terminada correctamente.');
            $this->table(
                ['Resultado', 'Cantidad'],
                [
                    ['Proyectos creados', $created],
                    ['Proyectos actualizados', $updated],
                    ['Proyectos omitidos por existir', $skipped],
                    ['Omitidos sin empresa reconocida', $withoutCompany],
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->newLine();
            $this->error('No se pudo completar la importación.');
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function resolvePath(string $file): string
    {
        if ($this->isAbsolutePath($file)) {
            return $file;
        }

        return base_path($file);
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
    }

    private function extractLegacySql(string $sql): string
    {
        $marker = '-- Transform the legacy dump to the current Laravel projects schema.';
        $position = strpos($sql, $marker);

        if ($position !== false) {
            $sql = substr($sql, 0, $position);
        }

        if (! str_contains($sql, 'legacy_projects')) {
            throw new RuntimeException(
                'El SQL no contiene la tabla legacy_projects requerida por este importador.'
            );
        }

        return $sql;
    }

    /**
     * @return array<string, int|null>
     */
    private function getCompanyIds(): array
    {
        return [
            'CIESA' => DB::table('companies')
                ->whereRaw('UPPER(TRIM(company_code)) = ?', ['CIESA'])
                ->value('id'),

            'GRALCO' => DB::table('companies')
                ->whereRaw('UPPER(TRIM(company_code)) = ?', ['GRALCO'])
                ->value('id'),

            'SEAF' => DB::table('companies')
                ->whereRaw('UPPER(TRIM(company_code)) = ?', ['SEAF'])
                ->value('id'),

            'COLD STORAGE' => DB::table('companies')
                ->whereRaw('UPPER(TRIM(company_code)) = ?', ['COLD'])
                ->value('id'),
        ];
    }

    /**
     * @param array<string, int|null> $companyIds
     */
    private function resolveCompanyId(string $pdaCode, array $companyIds): ?int
    {
        $code = strtoupper(trim($pdaCode));

        return match (true) {
            str_contains($code, 'CIESA') => $companyIds['CIESA'],
            str_contains($code, 'GRALCO') => $companyIds['GRALCO'],
            str_contains($code, 'SEAF') => $companyIds['SEAF'],
            str_contains($code, 'COLD') => $companyIds['COLD STORAGE'],
            default => null,
        };
    }
}
