<div>
    @php
        $editing = $isEdit ?? false;
        $formModalName = $editing ? $modalName : 'create-project';
        $saveMethod = $editing ? 'updateProject' : 'createProject';
        $closeMethod = $editing ? 'closeModal' : 'closeCreateModal';
        $fieldSuffix = $editing ? '-' . $projectId : '';
        $documentErrors = $errors->hasAny(['pdaDocument', 'projectIdea', 'handoverCertificate']);
        $dateErrors = $errors->hasAny([
            'form.forecast_start_date',
            'form.forecast_end_date',
            'form.approve_date',
            'form.close_date',
            'form.quartile_date',
        ]);
        $initialSection = $documentErrors ? 'documents' : ($dateErrors ? 'dates' : 'details');
    @endphp

    @if ($editing)
        <button type="button" wire:click="openModal" data-no-global-loading title="Edit project"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-blue-600 text-white shadow-sm transition duration-150 hover:-translate-y-px hover:bg-blue-500 hover:shadow-md active:translate-y-0 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
            <span class="sr-only">Edit project</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m16.862 3.487 1.651-1.65a2.121 2.121 0 1 1 3 3L10.582 15.768 6.75 16.5l.732-3.832L18.413 1.737M15.75 5.25l3 3M5.25 5.25H4.5A2.25 2.25 0 0 0 2.25 7.5v12A2.25 2.25 0 0 0 4.5 21.75h12a2.25 2.25 0 0 0 2.25-2.25v-.75" />
            </svg>
        </button>
    @else
        @if ($canCreate ?? false)
            <x-ui-button :text="__('Create project')" icon="plus" color="#EBB352" hover-opacity="0.75" text-color="#FFFFFF"
                wire:click="openCreateModal" />
        @endif
    @endif

    <x-dialog-modal :name="$formModalName" maxWidth="90vw" :close-method="$closeMethod">
        <x-slot name="title">
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    {{ $editing ? 'Edit project' : 'Create project' }}
                </h2>

                <p class="mt-1 text-sm font-normal text-gray-500">
                    {{ $editing
                        ? 'Update project details, dates and documents.'
                        : 'Complete the project details, dates and documents.' }}
                </p>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="min-w-0" x-data="{ activeSection: @js($initialSection) }">
                <nav class="mb-5 grid grid-cols-3 gap-2 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm"
                    aria-label="Project form sections">
                    <button type="button" x-on:click="activeSection = 'details'"
                        x-bind:class="activeSection === 'details' ? 'bg-blue-600 text-white shadow-sm' :
                            'text-slate-600 hover:bg-slate-100'"
                        class="inline-flex min-h-10 cursor-pointer items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition">
                        <span class="hidden sm:inline">Project</span> Details
                    </button>
                    <button type="button" x-on:click="activeSection = 'dates'"
                        x-bind:class="activeSection === 'dates' ? 'bg-blue-600 text-white shadow-sm' :
                            'text-slate-600 hover:bg-slate-100'"
                        class="inline-flex min-h-10 cursor-pointer items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition">
                        Dates
                        @if ($dateErrors)
                            <span class="h-2 w-2 rounded-full bg-red-500" aria-label="Dates contain errors"></span>
                        @endif
                    </button>
                    <button type="button" x-on:click="activeSection = 'documents'"
                        x-bind:class="activeSection === 'documents' ? 'bg-blue-600 text-white shadow-sm' :
                            'text-slate-600 hover:bg-slate-100'"
                        class="inline-flex min-h-10 cursor-pointer items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition">
                        Documents
                        @if ($documentErrors)
                            <span class="h-2 w-2 rounded-full bg-red-500" aria-label="Documents contain errors"></span>
                        @endif
                    </button>
                </nav>

                @include('livewire.project.partials.form-details')
                @include('livewire.project.partials.form-dates')
                @include('livewire.project.partials.form-documents')
            </div>
        </x-slot>

        <x-slot name="footer">
            <div
                class="flex w-full flex-col-reverse items-stretch justify-end gap-3 sm:flex-row sm:items-center sm:gap-4">
                <x-secondary-button wire:click="{{ $closeMethod }}" data-no-global-loading wire:loading.attr="disabled"
                    wire:target="{{ $saveMethod }}"
                    style="background-color: #ef4444; border-color: #dc2626; color: #ffffff;"
                    onmouseenter="this.style.backgroundColor='#dc2626'"
                    onmouseleave="this.style.backgroundColor='#ef4444'">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button wire:click="{{ $saveMethod }}" data-no-global-loading wire:loading.attr="disabled"
                    wire:target="{{ $saveMethod }}"
                    style="background-color: #2563eb; border-color: #1d4ed8; color: #ffffff;"
                    onmouseenter="this.style.backgroundColor='#1d4ed8'"
                    onmouseleave="this.style.backgroundColor='#2563eb'">
                    <span wire:loading.remove wire:target="{{ $saveMethod }}">
                        {{ $editing ? __('Save changes') : __('Create project') }}
                    </span>

                    <span wire:loading wire:target="{{ $saveMethod }}">
                        {{ $editing ? __('Saving...') : __('Creating...') }}
                    </span>
                </x-button>
            </div>
        </x-slot>
    </x-dialog-modal>
</div>
