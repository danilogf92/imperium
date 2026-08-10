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
                                @disabled($form->state === 'Postponed')
                                @class([
                                    'block w-full rounded-lg px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500',
                                    'border-red-300 bg-red-50/40' => $errors->has('form.forecast_start_date'),
                                    'border-gray-300' => !$errors->has('form.forecast_start_date'),
                                    'cursor-not-allowed bg-gray-100 text-gray-500' => $form->state === 'Postponed',
                                ])>

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
                                @disabled($form->state === 'Postponed')
                                @class([
                                    'block w-full rounded-lg px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500',
                                    'border-red-300 bg-red-50/40' => $errors->has('form.forecast_end_date'),
                                    'border-gray-300' => !$errors->has('form.forecast_end_date'),
                                    'cursor-not-allowed bg-gray-100 text-gray-500' => $form->state === 'Postponed',
                                ])>

                            @error('form.forecast_end_date')
                                <p class="mt-1.5 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        @if ($form->state === 'Postponed')
                            <p class="lg:col-span-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                Forecast dates are not required while the project is Postponed. Previous values will be restored if another state is selected before saving.
                            </p>
                        @endif

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
