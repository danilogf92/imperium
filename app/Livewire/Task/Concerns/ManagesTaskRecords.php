<?php

namespace App\Livewire\Task\Concerns;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;
use App\Models\Project;
use App\Services\Task\TaskTableQueryService;
use App\Validation\TaskDataUpdateValidation;

trait ManagesTaskRecords
{
    public function openEditModal(int $dataId): void
    {
        $data = $this->tasks()->authorizedData($dataId, ProjectPermissionEnum::Update);
        $this->editingDataId = (int) $data->id;
        $this->editData = ['percentage' => (int) $data->percentage];
        $this->resetValidation();
        $this->dispatch('open-modal', 'edit-task-data');
    }

    public function closeEditModal(): void
    {
        $this->reset(['editingDataId', 'editData']);
        $this->resetValidation();
        $this->dispatch('close-modal', 'edit-task-data');
    }

    public function updateData(): void
    {
        abort_unless($this->editingDataId, 404);
        $data = $this->tasks()->authorizedData($this->editingDataId, ProjectPermissionEnum::Update);
        $validated = $this->validate(
            TaskDataUpdateValidation::rules(), [], TaskDataUpdateValidation::attributes()
        );
        $data->update(['percentage' => $validated['editData']['percentage']]);
        $this->closeEditModal();
        $this->dispatch('alert', type: 'success', title: 'Task updated', position: 'center', timer: 1800);
    }

    public function openDeleteModal(int $dataId): void
    {
        $data = $this->tasks()->authorizedData($dataId, ProjectPermissionEnum::Delete);
        $this->deletingDataId = (int) $data->id;
        $this->deletingDataLabel = $data->description ?: "Task #{$data->id}";
        $this->dispatch('open-modal', 'delete-task-data');
    }

    public function closeDeleteModal(): void
    {
        $this->reset(['deletingDataId', 'deletingDataLabel']);
        $this->dispatch('close-modal', 'delete-task-data');
    }

    public function deleteData(): void
    {
        abort_unless($this->deletingDataId, 404);
        $data = $this->tasks()->authorizedData($this->deletingDataId, ProjectPermissionEnum::Delete);
        $projectId = (int) $data->project_id;
        $data->delete();

        if (! Data::query()->where('project_id', $projectId)->exists()) {
            Project::query()->whereKey($projectId)->update(['data_uploaded' => false]);
        }

        $this->closeDeleteModal();
        $this->resetPage();
        $this->dispatch('alert', type: 'success', title: 'Task deleted', position: 'center', timer: 1800);
    }

    private function tasks(): TaskTableQueryService
    {
        return app(TaskTableQueryService::class);
    }
}
