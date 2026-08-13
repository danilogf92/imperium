<div
                x-data="{ open: true }"
                class="border-b border-slate-200">

                {{-- Cabecera del área de filtros --}}
                <div
                    class="soft-title-surface flex flex-col items-start gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 shadow-sm">

                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.9">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3.75 5.25h16.5l-6.375 7.125v5.25l-3.75 1.875v-7.125L3.75 5.25Z" />
                            </svg>
                        </div>

                        <div>
                            <div class="flex items-center gap-2">

                                <h2 class="text-sm font-bold text-slate-900">
                                    {{ __('Planification filters') }}
                                </h2>

                                {{-- Indicador de filtros activos --}}
                                @if (
                                    count($plantFilter) ||
                                    count($statusFilter) ||
                                    count($creationYearFilter) ||
                                    $activityWeekFilter !== '' ||
                                    $milestoneExecutionFilter !== '' ||
                                    $activityExecutionFilter !== '' ||
                                    $onlyWithMilestones ||
                                    $currency !== 'usd' ||
                                    $cellDisplay !== 'combined'
                                )
                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-white">
                                        {{ __('Active') }}
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-slate-500">
                                {{ __('Refine the projects displayed in the timeline') }}
                            </p>
                        </div>
                    </div>

                    {{-- Abrir/cerrar panel de filtros --}}
                    <button
                        type="button"
                        x-on:click="open = !open"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">

                        <svg
                            class="h-5 w-5 transition-transform"
                            x-bind:class="{ 'rotate-180': !open }"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m18 15-6-6-6 6" />
                        </svg>
                    </button>
                </div>

                {{-- Contenido de filtros --}}
                <div
                    x-show="open"
                    x-collapse>

                    <div class="flex flex-wrap items-center gap-3 bg-white px-4 py-4">

                        {{-- Planta --}}
                        <x-dashboard-filter-dropdown
                            label="Plants"
                            model="plantFilter"
                            :options="$plantOptions->map(
                                fn($plant) => [
                                    'value' => $plant['id'],
                                    'label' => $plant['name'],
                                ],
                            )"
                            :selected="$plantFilter"
                            multiple />

                        {{-- Estado --}}
                        <x-dashboard-filter-dropdown
                            label="Status"
                            model="statusFilter"
                            :options="collect($statusOptions)->map(
                                fn($status) => [
                                    'value' => $status,
                                    'label' => $status,
                                ],
                            )"
                            :selected="$statusFilter"
                            multiple />

                        {{-- Año --}}
                        <x-dashboard-filter-dropdown
                            label="Years"
                            model="creationYearFilter"
                            :options="$creationYearOptions->map(
                                fn($year) => [
                                    'value' => $year,
                                    'label' => $year,
                                ],
                            )"
                            :selected="$creationYearFilter"
                            multiple />

                        <div class="flex items-center gap-1">
                            <x-dashboard-filter-dropdown
                                label="Activity week"
                                model="activityWeekFilter"
                                :options="$activityWeekOptions"
                                :selected="$activityWeekFilter"
                                default=""
                                show-selection />
                            @if ($activityWeekFilter !== '')
                                <button type="button" wire:click="resetActivityWeekFilter" data-no-global-loading
                                    title="Return to current week" aria-label="Return to current week"
                                    class="inline-flex h-11 w-11 cursor-pointer items-center justify-center rounded-lg bg-red-600 text-lg font-bold text-white shadow-sm hover:bg-red-700">
                                    ×
                                </button>
                            @endif
                        </div>

                        <label class="shrink-0">
                            <span class="sr-only">Milestone execution</span>
                            <select wire:model.change="milestoneExecutionFilter" data-no-global-loading
                                class="h-11 rounded-lg border-slate-300 bg-white text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All milestones</option>
                                <option value="completed">Completed milestones</option>
                                <option value="incomplete">Incomplete milestones</option>
                            </select>
                        </label>

                        <label class="shrink-0">
                            <span class="sr-only">Activity execution</span>
                            <select wire:model.change="activityExecutionFilter" data-no-global-loading
                                class="h-11 rounded-lg border-slate-300 bg-white text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">All activities</option>
                                <option value="completed">Completed activities</option>
                                <option value="incomplete">Incomplete activities</option>
                            </select>
                        </label>

                        {{--
                            Moneda.
                            wire:model.change dispara una petición únicamente
                            cuando cambia la selección.
                        --}}
                        <label class="shrink-0">
                            <span class="sr-only">
                                {{ __('Currency') }}
                            </span>

                            <select
                                wire:model.change="currency"
                                data-no-global-loading
                                class="h-11 rounded-lg border-slate-300 bg-white text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                <option value="usd">USD ($)</option>
                                <option value="eur">EUR (&euro;)</option>
                            </select>
                        </label>

                        {{-- Contenido que aparece dentro de cada celda --}}
                        <label class="shrink-0">
                            <span class="sr-only">
                                {{ __('Cell content') }}
                            </span>

                            <select
                                wire:model.change="cellDisplay"
                                data-no-global-loading
                                class="h-11 rounded-lg border-slate-300 bg-white text-sm font-semibold text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                <option value="combined">
                                    {{ __('Milestone | Value') }}
                                </option>

                                <option value="milestone">
                                    {{ __('Milestone only') }}
                                </option>

                                <option value="value">
                                    {{ __('Value only') }}
                                </option>
                            </select>
                        </label>

                        {{-- Solo proyectos con milestones --}}
                        <button
                            type="button"
                            wire:click="toggleOnlyWithMilestones"
                            data-no-global-loading
                            wire:loading.attr="disabled"
                            wire:target="toggleOnlyWithMilestones"
                            class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg border px-4 text-sm font-semibold shadow-sm transition hover:-translate-y-px hover:shadow-md disabled:opacity-60
                                {{ $onlyWithMilestones
                                    ? 'border-blue-600 bg-blue-600 text-white hover:bg-blue-500'
                                    : 'border-slate-300 bg-white text-slate-700 hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700' }}">

                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8">

                                @if ($onlyWithMilestones)
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m5 12 4 4L19 6" />
                                @else
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 7h16M7 12h10m-7 5h4" />
                                @endif
                            </svg>

                            {{ __('With milestones') }}
                        </button>

                        {{-- Limpiar filtros --}}
                        <x-clear-filters-button
                            method="clearFilters"
                            :active="
                                $plantFilter !== [] ||
                                $statusFilter !== [] ||
                                $creationYearFilter !== [] ||
                                $activityWeekFilter !== '' ||
                                $milestoneExecutionFilter !== '' ||
                                $activityExecutionFilter !== '' ||
                                $onlyWithMilestones ||
                                $currency !== 'usd' ||
                                $cellDisplay !== 'combined'
                            " />
                    </div>
                </div>
</div>
