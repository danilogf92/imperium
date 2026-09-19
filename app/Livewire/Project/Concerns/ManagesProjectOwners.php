<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Owner;
use Illuminate\Validation\Rule;

trait ManagesProjectOwners
{
    public bool $showOwnerCreator = false;

    public string $newOwnerName = '';

    /** @var array<int, int|string> */
    public array $newOwnerCompanyIds = [];

    public function toggleOwnerCreator(): void
    {
        $this->showOwnerCreator = ! $this->showOwnerCreator;

        if ($this->showOwnerCreator && $this->form->company_id) {
            $this->newOwnerCompanyIds = [(int) $this->form->company_id];
        }

        $this->resetValidation(['newOwnerName', 'newOwnerCompanyIds', 'newOwnerCompanyIds.*']);
    }

    public function createOwner(): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $allowedCompanyIds = $this->ownerCompanyIds();

        $this->newOwnerName = trim($this->newOwnerName);

        $validated = $this->validate([
            'newOwnerName' => ['required', 'string', 'max:255', Rule::unique('owners', 'name')],
            'newOwnerCompanyIds' => ['required', 'array', 'min:1'],
            'newOwnerCompanyIds.*' => ['integer', 'distinct', Rule::in($allowedCompanyIds)],
        ], [], [
            'newOwnerName' => 'owner name',
            'newOwnerCompanyIds' => 'companies',
            'newOwnerCompanyIds.*' => 'company',
        ]);

        if ($this->form->company_id
            && ! in_array((int) $this->form->company_id, array_map('intval', $validated['newOwnerCompanyIds']), true)) {
            $this->addError('newOwnerCompanyIds', 'Select the project company for this owner.');

            return;
        }

        $owner = Owner::create(['name' => $validated['newOwnerName']]);
        $owner->companies()->sync($validated['newOwnerCompanyIds']);

        $this->form->owner_ids = array_values(array_unique([
            ...array_map('intval', $this->form->owner_ids),
            (int) $owner->getKey(),
        ]));

        $this->resetOwnerCreator();
    }

    protected function resetOwnerCreator(): void
    {
        $this->showOwnerCreator = false;
        $this->newOwnerName = '';
        $this->newOwnerCompanyIds = [];
        $this->resetValidation(['newOwnerName', 'newOwnerCompanyIds', 'newOwnerCompanyIds.*']);
    }
}
