<?php

namespace App\Livewire\Data\Concerns;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;
use App\Support\Data\DataTableDefinition;
use App\Validation\DataCreateValidation;
use App\Validation\DataUpdateValidation;
use Illuminate\Support\Facades\DB;

trait ManagesDataRecords
{
    public function openEditModal(int $dataId): void
    {
        $data = $this->authorizedData(
            $dataId,
            ProjectPermissionEnum::Update
        );

        $this->editingDataId = (int) $data->id;
        $this->creatingData = false;

        $this->editData = $data->only(
            array_values(
                array_diff(
                    array_keys(
                        DataTableDefinition::COLUMN_OPTIONS
                    ),
                    ['actions']
                )
            )
        );

        $this->initializeBookedCalculator();
        $this->resetValidation();

        $this->dispatch(
            'open-modal',
            'edit-project-data'
        );
    }

    public function openCreateModal(): void
    {
        $this->authorizeProjectData(
            ProjectPermissionEnum::Update
        );

        $this->editingDataId = null;
        $this->creatingData = true;

        $this->editData = collect(
            array_keys(
                DataTableDefinition::COLUMN_OPTIONS
            )
        )
            ->reject(
                fn (string $column) =>
                    $column === 'actions'
            )
            ->mapWithKeys(
                fn (string $column) => [
                    $column => $column === 'order_year'
                        ? null
                        : (in_array(
                        $column,
                        DataTableDefinition::NUMERIC_COLUMNS,
                        true
                    )
                        ? 0
                        : null),
                ]
            )
            ->all();

        $this->initializeBookedCalculator();
        $this->resetValidation();

        $this->dispatch(
            'open-modal',
            'edit-project-data'
        );
    }

    public function closeEditModal(): void
    {
        $this->reset([
            'editingDataId',
            'editData',
            'creatingData',
            'bookedBase',
            'bookedMultiplier',
        ]);

        $this->resetValidation();

        $this->dispatch(
            'close-modal',
            'edit-project-data'
        );
    }

    public function createData(): void
    {
        $this->authorizeProjectData(
            ProjectPermissionEnum::Update
        );

        abort_unless(
            $this->creatingData,
            409
        );

        if (! $this->synchronizeEuroValues()) {
            return;
        }

        $validated = $this->validate(
            DataCreateValidation::rules(),
            [],
            DataCreateValidation::attributes()
        );

        DB::transaction(function () use ($validated): void {
            Data::query()->create([
                'project_id' => $this->project->id,
                ...$validated['editData'],
            ]);

            $this->project->update([
                'data_uploaded' => true,
            ]);
        });

        $this->closeEditModal();
        $this->resetPage();

        $this->dispatch(
            'alert',
            type: 'success',
            title: 'Data row created',
            position: 'center',
            timer: 1800
        );
    }

    public function updateData(): void
    {
        abort_unless(
            $this->editingDataId,
            404
        );

        $data = $this->authorizedData(
            $this->editingDataId,
            ProjectPermissionEnum::Update
        );

        if (! $this->synchronizeEuroValues()) {
            return;
        }

        $validated = $this->validate(
            DataUpdateValidation::rules(),
            [],
            DataUpdateValidation::attributes()
        );

        $data->update(
            $validated['editData']
        );

        $this->closeEditModal();
        $this->resetPage();

        $this->dispatch(
            'alert',
            type: 'success',
            title: 'Data updated',
            position: 'center',
            timer: 1800
        );
    }

    public function openDeleteModal(int $dataId): void
    {
        $data = $this->authorizedData(
            $dataId,
            ProjectPermissionEnum::Delete
        );

        $this->deletingDataId =
            (int) $data->id;

        $this->deletingDataLabel =
            $data->description
            ?: $data->code
            ?: "Record #{$data->id}";

        $this->dispatch(
            'open-modal',
            'delete-project-data'
        );
    }

    public function closeDeleteModal(): void
    {
        $this->reset([
            'deletingDataId',
            'deletingDataLabel',
        ]);

        $this->dispatch(
            'close-modal',
            'delete-project-data'
        );
    }

    public function deleteData(): void
    {
        abort_unless(
            $this->deletingDataId,
            404
        );

        $data = $this->authorizedData(
            $this->deletingDataId,
            ProjectPermissionEnum::Delete
        );

        $data->delete();

        if (! Data::query()
            ->where(
                'project_id',
                $this->project->id
            )
            ->exists()
        ) {
            $this->project->update([
                'data_uploaded' => false,
            ]);
        }

        $this->closeDeleteModal();
        $this->resetPage();

        $this->dispatch(
            'alert',
            type: 'success',
            title: 'Data deleted',
            position: 'center',
            timer: 1800
        );
    }
}
