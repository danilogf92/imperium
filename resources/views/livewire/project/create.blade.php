<div>
    @php
        $editing = $isEdit ?? false;
        $formModalName = $editing ? $modalName : 'create-project';
        $saveMethod = $editing ? 'updateProject' : 'createProject';
        $closeMethod = $editing ? 'closeModal' : 'closeCreateModal';
        $fieldSuffix = $editing ? '-' . $projectId : '';
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
            <x-button wire:click="openCreateModal" data-no-global-loading>
                {{ __('Create project') }}
            </x-button>
        @endif
    @endif

    <x-dialog-modal :name="$formModalName" maxWidth="3xl">
        <x-slot name="title">
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    {{ $editing ? 'Edit project' : 'Create project' }}
                </h2>

                <p class="mt-1 text-sm font-normal text-gray-500">
                    {{ $editing
                        ? 'Update the general information, classification and project dates.'
                        : 'Complete the general information, classification and project dates.' }}
                </p>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="max-h-[70vh] space-y-6 overflow-y-auto pr-2 py-2">
                {{-- Identification --}}
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
                        <h3 class="font-semibold text-gray-900">
                            Project identification
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Select the company and enter the project identification.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 p-5 lg:grid-cols-2">

                        {{-- Columna 1 --}}
                        <div>

                            {{-- Company --}}
                            <div class="mb-5">
                                <label for="company-id{{ $fieldSuffix }}"
                                    class="mb-2 block text-sm font-medium text-gray-700">
                                    Company
                                    <span class="text-red-500">*</span>
                                </label>

                                <select id="company-id{{ $fieldSuffix }}" wire:model.live="form.company_id" data-no-global-loading
                                    data-no-global-loading
                                    class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select a company</option>

                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">
                                            {{ $company->company_name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('form.company_id')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>

                        {{-- Columna 2 --}}
                        <div>
                            {{-- Project name --}}
                            <div class="mb-5">
                                <label for="project-name{{ $fieldSuffix }}"
                                    class="mb-2 block text-sm font-medium text-gray-700">
                                    Project name
                                    <span class="text-red-500">*</span>
                                </label>

                                <input id="project-name{{ $fieldSuffix }}" type="text" wire:model="form.name"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Enter the project name">

                                @error('form.name')
                                    <p class="mt-1.5 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>

                        {{-- PDA code --}}
                        <div class="col-span-full">
                            <label for="pda-code{{ $fieldSuffix }}"
                                class="mb-2 block text-sm font-medium text-gray-700">
                                PDA code
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="flex w-full rounded-lg shadow-sm">

                                {{-- Prefijo no editable --}}
                                <div
                                    class="flex items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-100 px-4 py-2.5 font-mono text-sm font-semibold uppercase text-gray-700">
                                    @if ($form->company_code)
                                        {{ $form->company_code }}_
                                    @else
                                        COMPANY_
                                    @endif
                                </div>

                                {{-- Parte editable --}}
                                <input id="pda-code{{ $fieldSuffix }}" type="text" wire:model="form.pda_code"
                                    class="block min-w-0 flex-1 rounded-r-lg border-gray-300 bg-white px-3 py-2.5 font-mono text-sm uppercase focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Enter project code" @disabled(!$form->company_id)>
                            </div>

                            @if (!$form->company_id)
                                <p class="mt-1.5 text-xs text-gray-500">
                                    Select a company before entering the PDA code.
                                </p>
                            @else
                                <p class="mt-1.5 text-xs text-gray-500">
                                    The company code is automatically added as a locked prefix.
                                </p>
                            @endif

                            @error('form.pda_code')
                                <p class="mt-1.5 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                </section>

                {{-- Classification --}}
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
                        <h3 class="font-semibold text-gray-900">
                            Classification and investment
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Define the current state and financial classification.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 p-5 lg:grid-cols-2">

                        {{-- State --}}
                        <div>
                            <label for="state{{ $fieldSuffix }}" class="mb-2 block text-sm font-medium text-gray-700">
                                State
                                <span class="text-red-500">*</span>
                            </label>

                            <select id="state{{ $fieldSuffix }}" wire:model="form.state"
                                class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                @foreach ($stateOptions as $stateOption)
                                    <option value="{{ $stateOption->value }}">
                                        {{ $stateOption->value }}
                                    </option>
                                @endforeach

                            </select>

                            @error('form.state')
                                <p class="mt-1.5 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Rate --}}
                        <div>
                            <label for="rate{{ $fieldSuffix }}" class="mb-2 block text-sm font-medium text-gray-700">
                                Rate
                            </label>

                            <input id="rate{{ $fieldSuffix }}" type="number" wire:model="form.rate" step="0.01"
                                min="{{ $rateLimits->min_rate }}" max="{{ $rateLimits->max_rate }}"
                                class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="0.00">

                            <p class="mt-1 text-xs text-gray-500">
                                Allowed range: {{ (float) $rateLimits->min_rate }}–{{ (float) $rateLimits->max_rate }}.
                            </p>

                            @error('form.rate')
                                <p class="mt-1.5 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Investment --}}
                        <div>
                            <label for="investments{{ $fieldSuffix }}"
                                class="mb-2 block text-sm font-medium text-gray-700">
                                Investment
                            </label>

                            <select id="investments{{ $fieldSuffix }}" wire:model="form.investments"
                                class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                <option value="">
                                    Select investment
                                </option>

                                @foreach ($investmentOptions as $investment)
                                    <option value="{{ $investment->value }}">
                                        {{ $investment->value }}
                                    </option>
                                @endforeach

                            </select>

                            @error('form.investments')
                                <p class="mt-1.5 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Investment classification --}}
                        <div>
                            <label for="classification_of_investments{{ $fieldSuffix }}"
                                class="mb-2 block text-sm font-medium text-gray-700">
                                Investment classification
                            </label>

                            <select id="classification_of_investments{{ $fieldSuffix }}"
                                wire:model="form.classification_of_investments"
                                class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                <option value="">
                                    Select classification
                                </option>

                                @foreach ($classificationOptions as $classificationOption)
                                    <option value="{{ $classificationOption->value }}">
                                        {{ $classificationOption->value }}
                                    </option>
                                @endforeach

                            </select>

                            @error('form.classification_of_investments')
                                <p class="mt-1.5 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Justification --}}
                        <div>
                            <label for="justification{{ $fieldSuffix }}"
                                class="mb-2 block text-sm font-medium text-gray-700">
                                Justification
                            </label>

                            <select id="justification{{ $fieldSuffix }}" wire:model="form.justification"
                                class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                <option value="">
                                    Select justification
                                </option>

                                @foreach ($justificationOptions as $justificationOption)
                                    <option value="{{ $justificationOption->value }}">
                                        {{ $justificationOption->value }}
                                    </option>
                                @endforeach

                            </select>

                            @error('form.justification')
                                <p class="mt-1.5 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                </section>

                {{-- Dates --}}
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
                        <h3 class="font-semibold text-gray-900">
                            Project dates
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Define the planning and execution dates.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 p-5 lg:grid-cols-2">
                        <div>
                            <label for="start-date{{ $fieldSuffix }}"
                                class="mb-2 block text-sm font-medium text-gray-700">
                                Forecast Start date
                            </label>

                            <input id="start-date{{ $fieldSuffix }}" type="date"
                                wire:model="form.forecast_start_date"
                                class="block w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                            @error('form.forecast_start_date')
                                <p class="mt-1.5 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="finish-date{{ $fieldSuffix }}"
                                class="mb-2 block text-sm font-medium text-gray-700">
                                Forecast End Date
                            </label>

                            <input id="finish-date{{ $fieldSuffix }}" type="date"
                                wire:model="form.forecast_end_date"
                                class="block w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                            @error('form.forecast_end_date')
                                <p class="mt-1.5 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="approve-date{{ $fieldSuffix }}"
                                class="mb-2 block text-sm font-medium text-gray-700">
                                Approve date
                            </label>

                            <input id="approve-date{{ $fieldSuffix }}" type="date"
                                wire:model="form.approve_date"
                                class="block w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                            @error('form.approve_date')
                                <p class="mt-1.5 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>


                        <div>
                            <label for="close-date{{ $fieldSuffix }}"
                                class="mb-2 block text-sm font-medium text-gray-700">
                                Close date
                            </label>

                            <input id="close-date{{ $fieldSuffix }}" type="date" wire:model="form.close_date"
                                class="block w-full rounded-lg border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                            @error('form.close_date')
                                <p class="mt-1.5 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                </section>
            </div>

        </x-slot>

        <x-slot name="footer">
            <div class="flex w-full items-center justify-end gap-4">
                <x-secondary-button wire:click="{{ $closeMethod }}" data-no-global-loading
                    wire:loading.attr="disabled" wire:target="{{ $saveMethod }}"
                    style="background-color: #ef4444; border-color: #dc2626; color: #ffffff;"
                    onmouseenter="this.style.backgroundColor='#dc2626'"
                    onmouseleave="this.style.backgroundColor='#ef4444'">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button wire:click="{{ $saveMethod }}" data-global-loading wire:loading.attr="disabled"
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
