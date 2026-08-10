                    <tbody>
                        @forelse ($projects as $project)
                            @php
                                $projectName = \Illuminate\Support\Str::limit($project->name, 50);
                            @endphp

                            <tr wire:key="project-row-{{ $project->id }}"
                                @if (in_array((int) $project->company_id, $updateCompanyIds, true)) x-on:click="if (!$event.target.closest('a, button, input, select, textarea, [role=button]')) $dispatch('open-project-edit', { projectId: {{ $project->id }} })" @endif
                                @class([
                                    'border-b border-gray-200 bg-white transition hover:bg-gray-50',
                                    'cursor-pointer' => in_array(
                                        (int) $project->company_id,
                                        $updateCompanyIds,
                                        true),
                                ])>

                                {{-- ID --}}
                                <th scope="row" class="whitespace-nowrap px-2 py-2 font-medium text-gray-900">
                                    {{ $project->id }}
                                </th>

                                {{-- Nombre --}}
                                <td class="whitespace-nowrap px-2 py-2 font-bold text-gray-900">
                                    @if ($project->data_uploaded)
                                        <a href="{{ route('projects.data', ['project' => $project->slug]) }}"
                                            class="text-red-500 hover:text-red-600 hover:underline">
                                            {{ $projectName }}
                                        </a>
                                    @else
                                        <span>
                                            {{ $projectName }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Dashboard y órdenes --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    <div class="flex items-center gap-2">
                                        @if ($project->data_uploaded)
                                            <a href="{{ route('projects.dashboard', ['project' => $project->slug]) }}"
                                                wire:navigate class="text-red-500 hover:text-red-600 hover:underline">
                                                Dashboard
                                            </a>

                                            <span class="text-slate-300">|</span>
                                        @endif

                                        @if ($project->has_orders)
                                            <a href="{{ route('projects.orders', ['project' => $project->slug]) }}"
                                                wire:navigate
                                                class="font-semibold text-blue-600 hover:text-blue-500 hover:underline">
                                                Orders
                                            </a>

                                            @if ($project->data_uploaded)
                                                <span class="text-slate-300">|</span>
                                            @endif
                                        @endif

                                        @if ($project->data_uploaded)
                                            <a href="{{ route('projects.data', ['project' => $project->slug]) }}"
                                                wire:navigate
                                                class="text-emerald-600 hover:text-emerald-500 hover:underline">
                                                Data
                                            </a>
                                        @endif
                                    </div>
                                </td>

                                {{-- Código PDA --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->pda_code }}
                                </td>

                                {{-- Archivo --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    <div class="flex items-center gap-2">
                                        @if (in_array((int) $project->company_id, $updateCompanyIds, true))
                                            @if (filled($project->upload_pda))
                                                <button
                                                    wire:click="$dispatch('open-project-document-manager', { projectId: {{ $project->id }} })"
                                                    data-no-global-loading type="button"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-white shadow-sm transition hover:-translate-y-px hover:brightness-110 hover:shadow-md active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                                    style="background-color: #16a34a" title="Manage document"
                                                    aria-label="Manage document">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.25 3.75H6.75A2.25 2.25 0 0 0 4.5 6v12a2.25 2.25 0 0 0 2.25 2.25h10.5A2.25 2.25 0 0 0 19.5 18V9m-5.25-5.25L19.5 9m-5.25-5.25V9h5.25M8.25 15.75h7.5" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button
                                                    wire:click="$dispatch('open-project-document-manager', { projectId: {{ $project->id }} })"
                                                    type="button" data-no-global-loading
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-white shadow-sm transition hover:-translate-y-px hover:brightness-110 hover:shadow-md active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                                                    style="background-color: #eab308" title="Upload document"
                                                    aria-label="Upload document">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14.25 3.75H6.75A2.25 2.25 0 0 0 4.5 6v12a2.25 2.25 0 0 0 2.25 2.25h10.5A2.25 2.25 0 0 0 19.5 18V9m-5.25-5.25L19.5 9m-5.25-5.25V9h5.25M12 12v5m0-5-2 2m2-2 2 2" />
                                                    </svg>
                                                </button>
                                            @endif
                                        @else
                                            <span @class([
                                                'inline-flex h-8 w-8 items-center justify-center rounded-md text-white',
                                            ])
                                                style="background-color: {{ filled($project->upload_pda) ? '#16a34a' : '#eab308' }}">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14.25 3.75H6.75A2.25 2.25 0 0 0 4.5 6v12a2.25 2.25 0 0 0 2.25 2.25h10.5A2.25 2.25 0 0 0 19.5 18V9m-5.25-5.25L19.5 9m-5.25-5.25V9h5.25" />
                                                </svg>
                                            </span>
                                        @endif

                                    </div>
                                </td>

                                {{-- Project ideas --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    <button
                                        wire:click="$dispatch('open-project-idea-manager', { projectId: {{ $project->id }} })"
                                        type="button" data-no-global-loading
                                        title="{{ filled($project->project_idea_path) ? 'Manage project ideas' : 'Upload project ideas' }}"
                                        class="inline-flex h-8 items-center gap-1.5 rounded-md px-2.5 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-px hover:brightness-110"
                                        style="background-color: {{ filled($project->project_idea_path) ? '#16a34a' : '#d97706' }}">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 3.75h8.25L18 7.5v12.75H6V3.75Zm8.25 0V7.5H18M9 11.25l6 6m0-6-6 6" />
                                        </svg>
                                        {{ filled($project->project_idea_path) ? 'Manage' : 'Upload' }}
                                    </button>
                                </td>

                                {{-- Project Handover Certificate --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    @if (in_array((int) $project->company_id, $updateCompanyIds, true))
                                        <button
                                            wire:click="$dispatch('open-project-handover-certificate-manager', { projectId: {{ $project->id }} })"
                                            type="button" data-no-global-loading
                                            title="{{ filled($project->handover_certificate_path) ? 'Manage Project Handover Certificate' : 'Upload Project Handover Certificate' }}"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-md px-2.5 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-px hover:brightness-110"
                                            style="background-color: {{ filled($project->handover_certificate_path) ? '#16a34a' : '#7c3aed' }}">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 3.75h8.25L18 7.5v12.75H6V3.75Zm8.25 0V7.5H18M9 14.25l2 2 4-4" />
                                            </svg>
                                            {{ filled($project->handover_certificate_path) ? 'Manage' : 'Upload' }}
                                        </button>
                                    @else
                                        <span class="text-sm text-slate-500">
                                            {{ filled($project->handover_certificate_path) ? 'Uploaded' : '—' }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Rate --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->rate }}
                                </td>

                                {{-- Estado --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    @if ($project->state)
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                            style="background-color: {{ $project->state->softColor() }}; color: {{ $project->state->textColor() }};">
                                            {{ $project->state->value }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                {{-- Inversión --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->investments?->value }}
                                </td>

                                {{-- Clasificación --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->classification_of_investments?->value }}
                                </td>

                                {{-- Justificación --}}
                                <td class="px-2 py-2">
                                    <div class="max-w-xs">
                                        {{ \Illuminate\Support\Str::limit($project->justification?->value, 100) }}
                                    </div>
                                </td>

                                {{-- Fecha inicio --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->forecast_start_date?->format('Y') ?? '—' }}
                                </td>

                                {{-- Fecha inicio --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->forecast_start_date?->format('Y-m-d') ?? '—' }}
                                </td>

                                {{-- Fecha final --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->forecast_end_date?->format('Y-m-d') }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2 font-semibold text-slate-700">
                                    {{ $project->order ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->company?->company_name ?? ($project->company?->company_code ?? '—') }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->creator?->name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->responsible?->name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2 text-center">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                        style="background-color: {{ $project->data_uploaded ? '#D1FAE5' : '#F1F5F9' }}; color: {{ $project->data_uploaded ? '#065F46' : '#475569' }};">
                                        {{ $project->data_uploaded ? 'Yes' : 'No' }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->quartile_date?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->approve_date?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->close_date?->format('Y-m-d') ?? '—' }}
                                </td>

                                <td class="max-w-56 truncate px-2 py-2" title="{{ $project->file_name }}">
                                    {{ $project->file_name ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->created_at?->format('Y-m-d H:i') }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2">
                                    {{ $project->updated_at?->format('Y-m-d H:i') }}
                                </td>

                                <td class="whitespace-nowrap px-2 py-2 text-right font-semibold text-slate-700">
                                    {{ \App\Support\MoneyValueFormatter::compact((float) $project->budgeted_euros, '€ ') }}
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 text-right font-semibold text-slate-700">
                                    {{ \App\Support\MoneyValueFormatter::compact((float) $project->real_euros, '€ ') }}
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 text-right font-semibold text-slate-700">
                                    {{ \App\Support\MoneyValueFormatter::compact((float) $project->budgeted_dollars, '$ ') }}
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 text-right font-semibold text-slate-700">
                                    {{ \App\Support\MoneyValueFormatter::compact((float) $project->real_dollars, '$ ') }}
                                </td>

                                {{-- Editar y borrar --}}
                                <td class="whitespace-nowrap px-2 py-2">
                                    <div class="flex items-center justify-center gap-1">
                                        @if (in_array((int) $project->company_id, $updateCompanyIds, true))
                                            <button
                                                wire:click="$dispatch('open-project-data-import-manager', { projectId: {{ $project->id }}, confirmDelete: {{ $project->data_uploaded ? 'true' : 'false' }} })"
                                                data-no-global-loading type="button"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md text-white shadow-sm transition hover:-translate-y-px hover:brightness-110 hover:shadow-md active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-1"
                                                style="background-color: {{ $project->data_uploaded ? '#dc2626' : '#0891b2' }}"
                                                title="{{ $project->data_uploaded ? 'Delete imported project data' : 'Import project data from Excel' }}"
                                                aria-label="{{ $project->data_uploaded ? 'Delete imported project data' : 'Import project data from Excel' }}">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.8">
                                                    @if ($project->data_uploaded)
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 6l12 12M18 6L6 18" />
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4.5 19.5h15M12 4v11m0-11L8.5 7.5M12 4l3.5 3.5" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6.75 12.75v4.5h10.5v-4.5" />
                                                    @endif
                                                </svg>
                                            </button>


                                            <livewire:project.edit wire:key="edit-project-{{ $project->id }}"
                                                :project="$project" />
                                        @endif

                                        @if (in_array((int) $project->company_id, $deleteCompanyIds, true))
                                            <livewire:project.delete wire:key="delete-project-{{ $project->id }}"
                                                :project="$project" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="project-empty-row unified-empty-row">
                                <td colspan="{{ $columnCount }}" class="px-6 py-12 text-center">

                                    <div class="flex flex-col items-center justify-center text-gray-500">

                                        <svg xmlns="http://www.w3.org/2000/svg" class="mb-3 h-10 w-10" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>

                                        <p class="font-semibold">
                                            No projects found
                                        </p>

                                        <p class="mt-1 text-sm">
                                            Change the search or selected filters.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
