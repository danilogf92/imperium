<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class NormalizeLegacyPdaFiles extends Command
{
    protected $signature = 'projects:normalize-pda-files
        {source=storage/app/PDA_files : Carpeta que contiene los archivos PDA antiguos}
        {--dry-run : Muestra los cambios sin copiar archivos ni actualizar la base de datos}
        {--force : Sobrescribe un archivo de destino existente}';

    protected $description = 'Copia los PDA antiguos al disco public, los renombra por código PDA y actualiza projects';

    public function handle(): int
    {
        $sourceDirectory = $this->resolvePath((string) $this->argument('source'));

        if (! is_dir($sourceDirectory) || ! is_readable($sourceDirectory)) {
            $this->error("No se puede leer la carpeta de PDA: {$sourceDirectory}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $matched = 0;
        $alreadyNormalized = 0;
        $missing = 0;
        $failed = 0;
        $usedLegacyFiles = [];

        Project::query()
            ->where(function ($query): void {
                $query->whereNotNull('file_name')
                    ->orWhereNotNull('upload_pda');
            })
            ->orderBy('id')
            ->chunkById(100, function ($projects) use (
                $sourceDirectory,
                $dryRun,
                $force,
                &$matched,
                &$alreadyNormalized,
                &$missing,
                &$failed,
                &$usedLegacyFiles
            ): void {
                foreach ($projects as $project) {
                    if ($this->hasCurrentDocument($project->upload_pda)) {
                        $alreadyNormalized++;
                        continue;
                    }

                    $source = $this->findLegacyFile(
                        $sourceDirectory,
                        $project->file_name,
                        $project->upload_pda
                    );

                    if ($source === null) {
                        $missing++;
                        $this->warn("Sin archivo: #{$project->id} {$project->pda_code}");
                        continue;
                    }

                    $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION)) ?: 'pdf';
                    $normalizedName = (Str::slug((string) $project->pda_code) ?: "project-{$project->id}")
                        . ".{$extension}";
                    $relativePath = "projects/{$project->id}/documents/{$normalizedName}";
                    $displayName = trim((string) $project->pda_code) . ".{$extension}";

                    $this->line(basename($source) . " -> {$relativePath}");

                    if ($dryRun) {
                        $matched++;
                        $usedLegacyFiles[basename($source)] = true;
                        continue;
                    }

                    try {
                        $destination = Storage::disk('public')->path($relativePath);
                        File::ensureDirectoryExists(dirname($destination));

                        if (is_file($destination) && ! $force) {
                            throw new \RuntimeException(
                                "El destino ya existe: {$relativePath}. Usa --force para sobrescribirlo."
                            );
                        }

                        File::copy($source, $destination);

                        $project->update([
                            'upload_pda' => $relativePath,
                            'file_name' => $displayName,
                        ]);

                        $matched++;
                        $usedLegacyFiles[basename($source)] = true;
                    } catch (Throwable $exception) {
                        $failed++;
                        $this->error("Error en {$project->pda_code}: {$exception->getMessage()}");
                    }
                }
            });

        $orphanFiles = collect(File::files($sourceDirectory))
            ->reject(fn ($file): bool => isset($usedLegacyFiles[$file->getFilename()]))
            ->count();

        $this->newLine();
        $this->table(['Resultado', 'Cantidad'], [
            [$dryRun ? 'Coincidencias encontradas' : 'Archivos normalizados', $matched],
            ['Ya normalizados', $alreadyNormalized],
            ['Proyectos sin archivo coincidente', $missing],
            ['Errores', $failed],
            ['Archivos antiguos sin proyecto coincidente', $orphanFiles],
        ]);

        if ($dryRun) {
            $this->info('Simulación terminada. No se modificaron archivos ni registros.');
        } else {
            $this->info('Normalización terminada. La carpeta original se conservó como respaldo.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function hasCurrentDocument(?string $path): bool
    {
        return filled($path)
            && str_starts_with($path, 'projects/')
            && Storage::disk('public')->exists($path);
    }

    private function findLegacyFile(string $directory, ?string $fileName, ?string $uploadPath): ?string
    {
        foreach ([$fileName, $uploadPath] as $candidate) {
            $name = basename(trim((string) $candidate));

            if ($name === '' || $name === '.' || $name === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $name;

            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    private function resolvePath(string $path): string
    {
        if (
            str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1
        ) {
            return $path;
        }

        return base_path($path);
    }
}
