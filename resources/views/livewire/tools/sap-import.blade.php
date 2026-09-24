<section class="space-y-5">
    @if ($importMessage !== '')
        <div wire:key="sap-import-success" x-data x-init="$nextTick(() => { $el.focus({ preventScroll: true }); $el.scrollIntoView({ behavior: 'smooth', block: 'center' }); })"
            tabindex="-1" role="status" aria-live="polite"
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 focus:outline-none sm:p-8">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-xl font-bold text-emerald-900">{{ __('sap.import_complete') }}</h2>
                    <p class="mt-2 text-base font-semibold text-emerald-800">{{ $importMessage }}</p>
                    <p class="mt-2 break-all text-sm text-emerald-800">{{ $sourceName }}</p>
                    <p class="mt-2 text-sm text-emerald-700">{{ __('sap.import_complete_hint') }}</p>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3 border-t border-emerald-200 pt-5">
                <x-tools-button wire:click="startOver" wire:target="startOver" variant="primary">
                    <span wire:loading.remove wire:target="startOver">{{ __('tools.start_over') }}</span>
                    <span wire:loading wire:target="startOver">{{ __('tools.processing') }}</span>
                </x-tools-button>
            </div>
        </div>
    @else
    @php($step = $sourceToken === '' ? 1 : ($mappingConfirmed && $matchesReady ? 3 : 2))
    <ol class="grid grid-cols-3 gap-2 rounded-xl bg-slate-50 p-3 text-sm sm:grid-cols-3" aria-label="{{ __('sap.title') }}">
        @foreach (['file', 'mapping', 'projects'] as $index => $label)
            <li @if ($step === $index + 1) aria-current="step" @endif class="flex items-center gap-2 {{ $step === $index + 1 ? 'font-semibold text-blue-700' : 'text-slate-500' }}">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $step === $index + 1 ? 'bg-blue-600 text-white' : 'bg-slate-200' }}">{{ $index + 1 }}</span><span class="{{ $step === $index + 1 ? '' : 'sr-only sm:not-sr-only' }}">{{ __('sap.'.$label) }}</span>
            </li>
        @endforeach
    </ol>
    <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
        <div class="grid items-end gap-4">
            <label class="block text-sm font-semibold text-slate-700">{{ __('sap.plant') }}
                <select wire:model.live="importPlant" class="mt-1 block h-11 w-full rounded-lg border-slate-300 text-sm">
                    <option value="">{{ __('sap.choose_plant') }}</option>
                    @foreach ($plants as $plant)<option value="{{ $plant->id }}">{{ $plant->company_name }}</option>@endforeach
                </select>
                @error('importPlant') <span class="text-red-700">{{ $message }}</span> @enderror
            </label>
        </div>
        <p class="mt-3 text-xs text-slate-500">{{ __('sap.plant_hint') }}</p>
        @if ($sourceToken === '')
            <div class="mt-5">@include('livewire.tools.excel-upload')</div>
        @else
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <span class="min-w-0 truncate text-sm font-medium text-slate-700" title="{{ $sourceName }}">{{ $sourceName }}</span>
                <x-tools-button wire:click="startOver" variant="link">{{ __('sap.replace_file') }}</x-tools-button>
            </div>
        @endif
    </div>
    @if ($sourceToken !== '' && ! $mappingConfirmed)
        <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
            <div><h2 class="font-semibold text-slate-900">{{ __('sap.mapping') }}</h2><p class="mt-1 text-sm text-slate-500">{{ __('sap.mapping_hint') }}</p></div>
            @if ($hasSavedMapping && ! $editingMapping)<p class="rounded-lg bg-blue-50 p-3 text-sm text-blue-800">{{ __('sap.saved') }}</p>@endif
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600"><tr><th class="p-3">{{ __('sap.source') }}</th><th class="p-3">{{ __('sap.target') }}</th></tr></thead>
                    <tbody>
                        @foreach (\App\Services\Tools\SapDataImporter::COLUMNS as $field)
                            <tr><td class="border-t border-slate-100 p-3">
                                <label class="block">{{ __($field === 'description' ? 'sap.order_text' : 'sap.'.$field) }}
                                    @if ($editingMapping)
                                        <select wire:model.live="sapMapping.{{ $field }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                                            <option value="">{{ __('tools.choose_header') }}</option>
                                            @foreach ($headers as $header)<option value="{{ $header }}">{{ $header }}</option>@endforeach
                                        </select>
                                    @else
                                        <span class="mt-1 block font-semibold">{{ $sapMapping[$field] ?? __('sap.unassigned') }}</span>
                                        @if (! in_array($sapMapping[$field] ?? '', $headers, true))<span class="text-red-700">{{ __('sap.missing') }}</span>@endif
                                    @endif
                                    @error("sapMapping.$field") <span class="text-red-700">{{ $message }}</span> @enderror
                                </label>
                            </td><td class="border-t border-slate-100 p-3">
                                <span class="font-semibold">{{ $field === 'real_value' ? __('Real $') : ($field === 'description' ? __('Description') : __('sap.'.$field)) }}</span>
                                @if ($field === 'real_value')
                                    <span class="mt-1 block text-xs text-slate-500"><code>real_value</code> (USD)</span>
                                @else
                                    <span class="mt-1 block text-xs text-slate-500">{{ in_array($field, ['accounting_date', 'document_date']) ? __('sap.date') : ($field === 'qty' ? __('tools.number') : __('tools.text')) }}</span>
                                @endif
                            </td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                @if (! $editingMapping)<x-tools-button wire:click="editSapMapping">{{ __('sap.edit') }}</x-tools-button>@else<span></span>@endif
                <x-tools-button wire:click="confirmSapMapping" variant="primary">{{ $editingMapping ? __('sap.save') : __('sap.reuse') }}</x-tools-button>
            </div>
        </div>
    @endif
    @foreach (['sapMapping', 'sapImport', 'columnTypes', 'selectedProjects'] as $errorField)
        @error($errorField)<p role="alert" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $message }}</p>@enderror
    @endforeach
    @if ($mappingConfirmed)
        <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-semibold text-slate-900">{{ __('sap.found', ['count' => count($matchedProjects)]) }}</h2>
                <x-tools-button wire:click="editSapMapping" variant="link">{{ __('sap.back') }} · {{ __('sap.mapping') }}</x-tools-button>
            </div>
            @if (! $matchesReady)
                <x-tools-button wire:click="findSapMatches" variant="primary">{{ __('sap.continue') }}</x-tools-button>
            @else
                @if ($matchedProjects === [])<p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800">{{ __('sap.none') }}</p>@endif
                @if ($projectReport !== [])
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-600"><tr>
                                <th class="p-3">{{ __('sap.select') }}</th><th class="p-3">{{ __('tools.project') }}</th><th class="p-3">{{ __('sap.all_rows') }}</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($projectReport as $project)
                                    <tr wire:key="sap-project-{{ $project['id'] }}" class="border-t border-slate-100">
                                        <td class="p-3"><input type="checkbox" wire:model.live="selectedProjects" value="{{ $project['id'] }}" @disabled($project['count'] === 0) aria-label="{{ __('sap.select') }} {{ $project['name'] }}" class="rounded border-slate-300 text-blue-600"></td>
                                        <td class="p-3 font-medium">{{ $project['name'] }}</td>
                                        <td class="p-3 tabular-nums">{{ $project['count'] }}
                                            @if ($project['count'] > 0)<x-tools-button wire:click="previewSapProject({{ $project['id'] }})" variant="link">{{ __('sap.view_rows') }}</x-tools-button>@endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                @if ($sapPreviewRows !== [])
                    <div class="rounded-lg border border-slate-200 p-3">
                        <div class="mb-3 flex items-center justify-between gap-3"><h3 class="text-sm font-semibold">{{ __('sap.row_preview', $sapPreviewProject) }}</h3><x-tools-button wire:click="closeSapPreview">{{ __('sap.back') }}</x-tools-button></div>
                        <div class="overflow-x-auto"><table class="w-full text-left text-xs">
                            <thead><tr>@foreach (array_diff(\App\Services\Tools\SapDataImporter::COLUMNS, ['sap_order']) as $field)<th class="p-2">{{ $field === 'real_value' ? __('Real $') : __($field === 'description' ? 'sap.order_text' : 'sap.'.$field) }}</th>@endforeach</tr></thead>
                            <tbody>@foreach ($sapPreviewRows as $row)<tr>@foreach (array_diff(\App\Services\Tools\SapDataImporter::COLUMNS, ['sap_order']) as $field)<td class="border-t p-2">{{ $row[$field] ?? '' }}</td>@endforeach</tr>@endforeach</tbody>
                        </table></div>
                    </div>
                @endif
                <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-4">
                    <div><p class="text-sm font-semibold">{{ __('sap.selection', ['count' => count($selectedProjects)]) }}</p><p class="mt-1 max-w-xl text-xs text-slate-500">{{ __('sap.scope') }}</p></div>
                    <x-tools-button wire:click="importSap" wire:target="importSap" variant="primary" :disabled="$selectedProjects === []">
                        <span wire:loading.remove wire:target="importSap">{{ __('sap.import') }}</span>
                        <span wire:loading wire:target="importSap" role="status">{{ __('sap.importing') }}</span>
                    </x-tools-button>
                </div>
            @endif
        </div>
    @endif
    <p wire:loading wire:target="confirmSapMapping,findSapMatches,importSap,previewSapProject" role="status" class="text-sm text-blue-700">{{ __('tools.processing') }}</p>
    @endif
</section>
