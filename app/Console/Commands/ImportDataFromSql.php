<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ImportDataFromSql extends Command
{
    /**
     * Ejemplos:
     *
     * php artisan data:import-sql storage/app/imports/data.sql
     * php artisan data:import-sql storage/app/imports/data.sql --replace
     * php artisan data:import-sql storage/app/imports/data.sql --keep-legacy
     */
    protected $signature = 'data:import-sql
        {file : Ruta absoluta o relativa del archivo SQL}
        {--replace : Elimina primero los datos actuales de los proyectos incluidos en el archivo}
        {--keep-legacy : No elimina la tabla temporal legacy_data al terminar}';

    protected $description = 'Importa datos desde un dump SQL antiguo y recalcula los valores en euros con la tasa del proyecto';

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

        if (! Schema::hasTable('data')) {
            $this->error('No existe la tabla data en la base de datos actual.');

            return self::FAILURE;
        }

        try {
            $this->validateTargetColumns();

            $this->info('Leyendo archivo SQL...');
            $sql = file_get_contents($path);

            if ($sql === false || trim($sql) === '') {
                throw new RuntimeException('El archivo SQL está vacío o no pudo leerse.');
            }

            $legacySql = $this->extractLegacySql($sql);

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            Schema::dropIfExists('legacy_data');

            $this->info('Creando y cargando la tabla temporal legacy_data...');
            DB::unprepared($legacySql);

            if (! Schema::hasTable('legacy_data')) {
                throw new RuntimeException(
                    'El archivo no creó la tabla legacy_data. Verifica que corresponda al dump esperado.'
                );
            }

            $legacyProjectIds = DB::table('legacy_data')
                ->select('project_id')
                ->distinct()
                ->pluck('project_id')
                ->map(fn($id) => (int) $id)
                ->values();

            $projects = DB::table('projects')
                ->whereIn('id', $legacyProjectIds)
                ->select(['id', 'rate'])
                ->get()
                ->keyBy('id');

            $missingProjectIds = $legacyProjectIds
                ->reject(fn(int $projectId) => $projects->has($projectId))
                ->values()
                ->all();

            if ($missingProjectIds !== []) {
                throw new RuntimeException(
                    'No existen en projects los siguientes project_id del archivo antiguo: '
                        . implode(', ', $missingProjectIds)
                        . '. Los proyectos deben conservar los mismos ID usados por legacy_data.'
                );
            }

            $invalidRateProjects = $projects
                ->filter(fn($project) => ! is_numeric($project->rate) || (float) $project->rate <= 0)
                ->keys()
                ->values()
                ->all();

            if ($invalidRateProjects !== []) {
                throw new RuntimeException(
                    'Los siguientes proyectos no tienen una tasa rate válida mayor que cero: '
                        . implode(', ', $invalidRateProjects)
                );
            }

            if ((bool) $this->option('replace')) {
                $this->warn('Eliminando datos actuales de los proyectos incluidos en el archivo...');

                DB::table('data')
                    ->whereIn('project_id', $legacyProjectIds)
                    ->delete();
            }

            $total = DB::table('legacy_data')->count();

            if ($total === 0) {
                throw new RuntimeException('La tabla legacy_data no contiene registros.');
            }

            $this->info("Importando {$total} registros...");

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $inserted = 0;

            DB::table('legacy_data')
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($projects, &$inserted, $bar): void {
                    $batch = [];

                    foreach ($rows as $legacy) {
                        $project = $projects->get((int) $legacy->project_id);
                        $rate = (float) $project->rate;

                        $globalPrice = $this->decimal($legacy->global_price);
                        $realValue = $this->decimal($legacy->real_value);
                        $booked = $this->decimal($legacy->booked);
                        $executedDollars = $this->decimal($legacy->executed_dollars);

                        $batch[] = [
                            'project_id' => (int) $legacy->project_id,
                            'area' => $legacy->area,
                            'group_1' => $legacy->group_1,
                            'group_2' => $legacy->group_2,
                            'description' => $legacy->description,
                            'general_classification' => $legacy->general_classification,
                            'item_type' => $legacy->item_type,
                            'unit' => $legacy->unit,
                            'qty' => $this->decimal($legacy->qty),

                            // Valores base en dólares.
                            'unit_price' => $this->decimal($legacy->unit_price),
                            'global_price' => $globalPrice,
                            'real_value' => $realValue,
                            'committed' => $this->decimal($legacy->committed ?? 0),
                            'executed_dollars' => $executedDollars,
                            'booked' => $booked,

                            // Valores recalculados en euros:
                            // euros = dólares / rate del proyecto.
                            'global_price_euros' => $this->toEuros($globalPrice, $rate),
                            'real_value_euros' => $this->toEuros($realValue, $rate),
                            'executed_euros' => $this->toEuros($executedDollars, $rate),
                            'booked_euros' => $this->toEuros($booked, $rate),

                            'stage' => $legacy->stage,
                            'percentage' => $this->decimal($legacy->percentage),
                            'supplier' => $legacy->supplier,
                            'code' => $legacy->code,
                            'order_no' => $legacy->order_no,
                            'input_num' => $legacy->input_num,
                            'observations' => $legacy->observations,

                            // Fechas históricas disponibles en el sistema anterior.
                            'real_value_changed_at' => $this->parseLegacyDate(
                                $legacy->real_updated_at ?? null
                            ),
                            'booked_changed_at' => $this->parseLegacyDate(
                                $legacy->booked_updated_at ?? null
                            ),
                            'committed_changed_at' => null,
                            'percentage_changed_at' => null,
                            'executed_changed_at' => null,

                            'created_at' => $legacy->created_at ?? now(),
                            'updated_at' => $legacy->updated_at ?? now(),
                        ];

                        $bar->advance();
                    }

                    DB::table('data')->insert($batch);
                    $inserted += count($batch);
                }, 'id');

            $bar->finish();
            $this->newLine(2);

            if (! $this->option('keep-legacy')) {
                Schema::dropIfExists('legacy_data');
                $this->line('La tabla temporal legacy_data fue eliminada.');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info('Importación terminada correctamente.');
            $this->table(
                ['Resultado', 'Cantidad'],
                [
                    ['Registros importados', $inserted],
                    ['Proyectos relacionados', $legacyProjectIds->count()],
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

    private function validateTargetColumns(): void
    {
        $requiredColumns = [
            'project_id',
            'area',
            'group_1',
            'group_2',
            'description',
            'general_classification',
            'item_type',
            'unit',
            'qty',
            'unit_price',
            'global_price',
            'stage',
            'real_value',
            'committed',
            'percentage',
            'executed_dollars',
            'executed_euros',
            'supplier',
            'code',
            'order_no',
            'input_num',
            'observations',
            'booked',
            'global_price_euros',
            'real_value_euros',
            'booked_euros',
            'real_value_changed_at',
            'committed_changed_at',
            'percentage_changed_at',
            'executed_changed_at',
            'booked_changed_at',
            'created_at',
            'updated_at',
        ];

        $missingColumns = collect($requiredColumns)
            ->reject(fn(string $column) => Schema::hasColumn('data', $column))
            ->values()
            ->all();

        if ($missingColumns !== []) {
            throw new RuntimeException(
                'Faltan las siguientes columnas en la tabla data: '
                    . implode(', ', $missingColumns)
            );
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
        if (! str_contains($sql, 'legacy_data')) {
            throw new RuntimeException(
                'El SQL no contiene la tabla legacy_data requerida por este importador.'
            );
        }

        return $sql;
    }

    private function decimal(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return round((float) $value, 2);
    }

    private function toEuros(float $dollars, float $rate): float
    {
        if ($dollars == 0.0) {
            return 0.0;
        }

        return round($dollars / $rate, 2);
    }

    private function parseLegacyDate(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $value = trim((string) $value);

        foreach (['d/m/Y', 'Y-m-d', 'Y-m-d H:i:s'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)
                    ->startOfDay()
                    ->toDateTimeString();
            } catch (Throwable) {
                // Continúa probando los demás formatos.
            }
        }

        return null;
    }
}
