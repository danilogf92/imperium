<?php

namespace App\Livewire\Project;

use App\Enums\ProjectPermissionEnum;
use App\Exports\ProjectDashboardExport;
use App\Exports\ProjectExport;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Actions extends Component
{
    /**
     * Texto utilizado para buscar proyectos.
     */
    public string $search = '';

    /**
     * Cantidad de proyectos mostrados por página.
     */
    public int $perPage = 10;

    /**
     * Envía el texto de búsqueda al componente Table.
     */
    public function updatedSearch(string $value): void
    {
        $this->dispatch(
            'project-search-updated',
            search: trim($value),
        );
    }

    /**
     * Envía la cantidad de registros al componente Table.
     */
    public function updatedPerPage(int|string $value): void
    {
        $allowedValues = [5, 10, 20, 50, 100];

        $perPage = (int) $value;

        if (!in_array($perPage, $allowedValues, true)) {
            $perPage = 10;
        }

        $this->perPage = $perPage;

        $this->dispatch(
            'project-per-page-updated',
            perPage: $this->perPage,
        );
    }

    /**
     * Limpia la búsqueda y solicita a Filters y Table reiniciar su estado.
     */
    public function resetAll(): void
    {
        $this->search = '';
        $this->perPage = 10;

        $this->dispatch('project-reset-all');
    }

    public function clearSearch(): void
    {
        $this->search = '';

        $this->dispatch(
            'project-search-updated',
            search: '',
        );
    }

    /**
     * Exporta la lista de proyectos.
     */
    public function export(): BinaryFileResponse
    {
        $user = auth()->user();

        abort_unless(
            $user?->companiesForPermissionQuery(
                ProjectPermissionEnum::Export
            )->exists(),
            403
        );

        return (new ProjectExport())->download($user);
    }

    public function exportDashboard(): BinaryFileResponse
    {
        $user = auth()->user();

        abort_unless(
            $user?->companiesForPermissionQuery(
                ProjectPermissionEnum::Export
            )->exists(),
            403
        );

        return (new ProjectDashboardExport())->download($user);
    }

    /**
     * Renderiza la barra de acciones.
     */
    public function render(): View
    {
        return view('livewire.project.actions', [
            'canExportProjects' => auth()->user()
                ?->companiesForPermissionQuery(
                    ProjectPermissionEnum::Export
                )
                ->exists() ?? false,
        ]);
    }
}
