@if ($showFormModal)

    <div wire:key="planification-form-modal" class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4" x-data
        x-on:keydown.escape.window="$wire.closeForm()">

        {{-- ============================================================
            FONDO DEL MODAL

            Cualquier click fuera del formulario cierra el modal.
            ============================================================ --}}
        <div class="absolute inset-0 cursor-pointer bg-slate-900/60 backdrop-blur-sm" wire:click="closeForm"
            data-no-global-loading></div>

        {{-- ============================================================
            CONTENIDO DEL MODAL
            ============================================================ --}}
        <form wire:submit="saveMilestone" wire:click.stop
            class="relative z-10 flex max-h-[calc(100dvh-1rem)] w-full max-w-lg flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl sm:max-h-[calc(100vh-2rem)] sm:rounded-2xl">

            {{-- ====================================================
                CABECERA DEL MODAL
                ==================================================== --}}
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-6 py-4">

                <div class="flex items-center gap-3">

                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">

                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $editingId
                                    ? 'M16.862 4.487 18.55 2.8a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z'
                                    : 'M12 4v16m8-8H4' }}" />
                        </svg>
                    </span>

                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            {{ $editingId ? 'Edit milestone' : 'Add milestone' }}
                        </h2>

                        <p class="text-xs text-slate-500">
                            {{ $editingId ? 'Update its position in the project timeline.' : 'Place a milestone in the project timeline.' }}
                        </p>
                    </div>
                </div>

                {{-- Cerrar modal --}}
                <button type="button" wire:click="closeForm" data-no-global-loading
                    class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-200 hover:text-slate-700"
                    aria-label="Close">
                    ✕
                </button>
            </div>

            {{-- ====================================================
                CAMPOS DEL FORMULARIO
                ==================================================== --}}
            <div class="grid min-h-0 flex-1 gap-4 overflow-y-auto px-4 py-4 sm:grid-cols-2 sm:px-6 sm:py-5">

                {{-- Proyecto --}}
                <div class="sm:col-span-2">

                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Project
                    </label>

                    <select wire:model.change="projectId" data-no-global-loading @disabled($editingId)
                        class="block w-full cursor-pointer rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">

                        <option value="">
                            Select a project
                        </option>

                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" @disabled(!$editingId && $project->is_closed)>
                                {{ $project->name }}
                                {{ $project->is_closed ? ' (Closed)' : '' }}
                            </option>
                        @endforeach
                    </select>

                    @error('projectId')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Milestone --}}
                <div class="sm:col-span-2">

                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Milestone
                    </label>

                    <select wire:model="milestoneId"
                        class="block w-full cursor-pointer rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">

                        <option value="">
                            Select a milestone
                        </option>

                        @foreach ($milestones as $milestone)
                            <option value="{{ $milestone->id }}">
                                {{ $milestone->code }}
                                —
                                {{ $milestone->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('milestoneId')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Mes --}}
                <div>

                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Month
                    </label>

                    <select wire:model="month"
                        class="block w-full cursor-pointer rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">

                        <option value="">
                            Select a month
                        </option>

                        @foreach ($months as $number => $label)
                            <option value="{{ $number }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    @error('month')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Año --}}
                <div>

                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Year
                    </label>

                    <input type="number" wire:model="cycleYear" min="2000" max="2200"
                        class="block w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">

                    @error('cycleYear')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-1 text-xs text-gray-500">
                        Maximum two consecutive years per project.
                    </p>
                </div>

                {{-- Porcentaje --}}
                <div class="sm:col-span-2">

                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Project percentage
                    </label>

                    <div class="relative">

                        <input type="number" wire:model.blur="percentage" data-no-global-loading min="0"
                            max="100" step="0.01"
                            class="block w-full rounded-lg border-gray-300 pr-10 text-sm focus:border-indigo-500 focus:ring-indigo-500">

                        <span
                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm font-semibold text-slate-500">
                            %
                        </span>
                    </div>

                    @error('percentage')
                        <p class="mt-1 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    {{-- =================================================
                        PREVISUALIZACIÓN DEL VALOR DEL MILESTONE
                        ================================================= --}}
                    @php
                        $selectedPlanProject = $projects->firstWhere('id', (int) $projectId);

                        $selectedBudget =
                            (float) ($currency === 'eur'
                                ? $selectedPlanProject?->data_budgeted_euros ?? 0
                                : $selectedPlanProject?->data_budgeted ?? 0);

                        $previewValue = $selectedBudget * ((float) $percentage / 100);
                    @endphp

                    <p class="mt-1 text-xs text-slate-500">
                        Calculated value:

                        {{ $currency === 'eur' ? '€' : '$' }}{{ number_format($previewValue, 2) }}
                    </p>
                </div>
            </div>

            {{-- ====================================================
                PIE DEL MODAL
                ==================================================== --}}
            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:justify-end sm:gap-4 sm:px-6">

                {{-- Cancelar --}}
                <button type="button" wire:click="closeForm" data-no-global-loading
                    class="inline-flex h-10 cursor-pointer items-center rounded-lg border px-4 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    style="
                        background-color: #ef4444;
                        border-color: #dc2626;
                    "
                    onmouseenter="this.style.backgroundColor='#dc2626'"
                    onmouseleave="this.style.backgroundColor='#ef4444'">
                    Cancel
                </button>

                {{-- Guardar --}}
                <button type="submit" data-no-global-loading wire:loading.attr="disabled" wire:target="saveMilestone"
                    class="inline-flex h-10 cursor-pointer items-center gap-2 rounded-lg border px-4 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                    style="
                        background-color: #2563eb;
                        border-color: #1d4ed8;
                    "
                    onmouseenter="this.style.backgroundColor='#1d4ed8'"
                    onmouseleave="this.style.backgroundColor='#2563eb'">

                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ $editingId ? 'm5 13 4 4L19 7' : 'M12 4v16m8-8H4' }}" />
                    </svg>

                    <span wire:loading.remove wire:target="saveMilestone">
                        {{ $editingId ? 'Save changes' : 'Create milestone' }}
                    </span>

                    <span wire:loading wire:target="saveMilestone">
                        {{ $editingId ? 'Saving...' : 'Creating...' }}
                    </span>
                </button>
            </div>
        </form>
    </div>

@endif
