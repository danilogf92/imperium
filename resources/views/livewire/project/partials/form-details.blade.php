                <div x-show="activeSection === 'details'" class="space-y-5">
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

                        <div class="grid grid-cols-1 gap-6 p-5 lg:grid-cols-3">

                            {{-- Columna 1 --}}
                            <div>

                                {{-- Company --}}
                                <div class="mb-5">
                                    <label for="company-id{{ $fieldSuffix }}"
                                        class="mb-2 block text-sm font-medium text-gray-700">
                                        Company
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select id="company-id{{ $fieldSuffix }}" wire:model.live="form.company_id"
                                        data-no-global-loading data-no-global-loading
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

                            <div>
                                <div class="mb-5">
                                    <label for="project-order{{ $fieldSuffix }}"
                                        class="mb-2 block text-sm font-medium text-gray-700">
                                        Order
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <input id="project-order{{ $fieldSuffix }}" type="text" wire:model="form.order"
                                        inputmode="text" maxlength="20"
                                        class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Examples: 1, 5a, 100b">

                                    <p class="mt-1 text-xs text-gray-500">Must be unique within the selected plant.</p>

                                    @error('form.order')
                                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Columna 3 --}}
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
                                <label for="state{{ $fieldSuffix }}"
                                    class="mb-2 block text-sm font-medium text-gray-700">
                                    State
                                    <span class="text-red-500">*</span>
                                </label>

                                <select id="state{{ $fieldSuffix }}" wire:model.live="form.state"
                                    data-no-global-loading
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
                                <label for="rate{{ $fieldSuffix }}"
                                    class="mb-2 block text-sm font-medium text-gray-700">
                                    Rate
                                </label>

                                <input id="rate{{ $fieldSuffix }}" type="number" wire:model="form.rate"
                                    step="0.01" min="{{ $rateLimits->min_rate }}" max="{{ $rateLimits->max_rate }}"
                                    class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="0.00">

                                <p class="mt-1 text-xs text-gray-500">
                                    Allowed range:
                                    {{ (float) $rateLimits->min_rate }}of{{ (float) $rateLimits->max_rate }}.
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

                </div>
