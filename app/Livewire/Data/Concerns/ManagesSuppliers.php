<?php

namespace App\Livewire\Data\Concerns;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

trait ManagesSuppliers
{
    public bool $showSupplierCreator = false;
    public string $newSupplierName = '';

    protected function supplierNames(): Collection
    {
        // Include subsequent Excel imports without changing the existing text field.
        $existing = Data::query()->whereHas('project', fn ($query) =>
            $query->where('company_id', $this->project->company_id))
            ->whereNotNull('supplier')->distinct()->pluck('supplier');

        return Supplier::where('company_id', $this->project->company_id)->pluck('name')
            ->merge($existing)->filter(fn ($name) => trim($name) !== '')
            ->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    public function toggleSupplierCreator(): void
    {
        $this->authorizeProjectData(ProjectPermissionEnum::Update);
        $this->showSupplierCreator = ! $this->showSupplierCreator;
        $this->newSupplierName = '';
        $this->resetValidation('newSupplierName');
    }

    public function createSupplier(): void
    {
        $this->authorizeProjectData(ProjectPermissionEnum::Update);
        abort_unless($this->creatingData || $this->editingDataId, 409);
        $this->newSupplierName = trim($this->newSupplierName);
        $this->validate([
            'newSupplierName' => ['required', 'string', 'max:255',
                Rule::unique('suppliers', 'name')->where('company_id', $this->project->company_id)],
        ]);
        if ($this->supplierNames()->contains(fn ($name) => mb_strtolower(trim($name)) === mb_strtolower($this->newSupplierName))) {
            $this->addError('newSupplierName', __('This supplier already exists. Select it from the list.'));
            return;
        }
        $supplier = Supplier::create([
            'company_id' => $this->project->company_id, 'name' => $this->newSupplierName,
        ]);
        $this->editData['supplier'] = $supplier->name;
        $this->resetSupplierCreator();
        $this->dispatch('alert', type: 'success', title: __('Supplier created and selected'), position: 'center', timer: 1800);
    }

    protected function validateSupplierSelection(): void
    {
        $this->validate(['editData.supplier' => ['nullable', 'string', Rule::in($this->supplierNames()->all())]]);
    }

    protected function resetSupplierCreator(): void
    {
        $this->reset(['showSupplierCreator', 'newSupplierName']);
        $this->resetValidation('newSupplierName');
    }
}
