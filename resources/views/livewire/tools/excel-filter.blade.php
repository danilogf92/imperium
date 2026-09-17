<div class="dashboard-page-shell">
    <div class="dashboard-page-content space-y-6">
        <section class="module-accent-line overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="soft-title-surface border-b border-slate-200 px-5 py-4">
                <h1 class="text-xl font-bold text-slate-900">{{ __('tools.title') }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ __('tools.description') }}</p>
            </header>

            <div class="space-y-6 p-4 sm:p-6">
                <form wire:submit="analyze" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div>
                        <div class="min-w-0">
                            <span class="mb-2 block text-sm font-semibold text-slate-800">{{ __('tools.upload_title') }}</span>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div class="min-w-0 flex-1">
                                    <input id="tools-excel-upload" type="file" wire:model="upload" accept=".xlsx,.xls" class="peer sr-only">
                                    <label for="tools-excel-upload" class="flex min-h-14 cursor-pointer flex-wrap items-center gap-3 rounded-xl border border-slate-300 bg-white p-2 shadow-sm transition hover:border-blue-400 hover:bg-blue-50/40 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-blue-600">
                                        <span class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4 4 4M4 17v2a1 1 0 001 1h14a1 1 0 001-1v-2"/></svg>
                                            {{ __('tools.choose_file') }}
                                        </span>
                                        <span class="min-w-0 flex-1 truncate text-sm text-slate-600" title="{{ $upload?->getClientOriginalName() }}">{{ $upload?->getClientOriginalName() ?? __('tools.no_file') }}</span>
                                    </label>
                                </div>
                                <button type="submit" wire:loading.attr="disabled" wire:target="upload,analyze" data-no-global-loading
                                    class="inline-flex h-11 shrink-0 items-center justify-center self-start rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60 sm:self-auto">
                                    <span wire:loading.remove wire:target="upload,analyze">{{ __('tools.analyze') }}</span>
                                    <span wire:loading wire:target="upload,analyze">{{ __('tools.processing') }}</span>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ __('tools.upload_hint') }}</p>
                        </div>
                    </div>
                    <div wire:loading wire:target="upload" class="mt-2 text-xs font-medium text-blue-700">{{ __('tools.uploading') }}</div>
                    @error('upload') <p class="mt-3 text-sm font-medium text-red-700">{{ $message }}</p> @enderror
                </form>

                @if ($sourceToken !== '')
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        <span class="font-semibold">{{ __('tools.analyzed') }}</span> {{ $sourceName }} · {{ count($headers) }} {{ __('tools.columns') }}
                    </div>

                    <section aria-labelledby="tools-sample-title">
                        <div class="mb-3">
                            <h2 id="tools-sample-title" class="text-base font-bold text-slate-900">{{ __('tools.structure') }}</h2>
                            <p class="text-xs text-slate-500">{{ __('tools.sample', ['count' => count($sampleRows)]) }}</p>
                        </div>
                        <div class="max-w-full overflow-x-auto rounded-lg border border-slate-200">
                            <table class="min-w-full whitespace-nowrap text-left text-xs">
                                <thead>
                                    <tr>
                                        @foreach ($headers as $header)
                                            <th scope="col" class="min-w-36 border-r border-blue-500 bg-blue-600 px-4 py-3 text-left font-bold text-white" style="background-color: #2563EB; color: #fff">{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($sampleRows as $row)
                                        <tr>
                                            @foreach ($row as $value)
                                                <td class="max-w-64 truncate border-r border-slate-100 px-3 py-2 text-slate-700" title="{{ $value }}">{{ $value }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>

                    @if (! $previewReady)
                        <section class="grid gap-5 lg:grid-cols-2">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <h2 class="text-base font-bold text-slate-900">{{ __('tools.select_columns') }}</h2>
                                        <p class="text-xs text-slate-500">{{ __('tools.selected', ['selected' => count($selectedColumns), 'total' => count($headers)]) }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="selectAll" data-no-global-loading class="rounded-lg border border-blue-300 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">{{ __('tools.select_all') }}</button>
                                        <button type="button" wire:click="selectNone" data-no-global-loading class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ __('tools.clear_all') }}</button>
                                    </div>
                                </div>
                                <div class="mt-4 grid max-h-64 gap-2 overflow-y-auto sm:grid-cols-2">
                                    @foreach ($headers as $index => $header)
                                        <div wire:key="tools-column-{{ $index }}" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                            <label class="flex cursor-pointer items-start gap-2">
                                                <input type="checkbox" wire:model="selectedColumns" value="{{ $index }}" class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                <span class="min-w-0 break-words font-medium">{{ $header }}</span>
                                            </label>
                                            <select wire:model="columnTypes.{{ $index }}" aria-label="{{ __('tools.column_type', ['column' => $header]) }}" class="mt-2 block w-full rounded-lg border-slate-300 bg-white text-xs focus:border-blue-500 focus:ring-blue-500">
                                                <option value="text">{{ __('tools.text') }}</option>
                                                <option value="number">{{ __('tools.number') }}</option>
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selectedColumns') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                                @error('columnTypes') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <h2 class="text-base font-bold text-slate-900">{{ __('tools.filter_project') }}</h2>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="tools-filter-column" class="mb-1 block text-sm font-semibold text-slate-700">{{ __('tools.field') }}</label>
                                        <select id="tools-filter-column" wire:model="filterColumn" class="block h-11 w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">{{ __('tools.choose_header') }}</option>
                                            @foreach ($headers as $index => $header)
                                                <option value="{{ $index }}">{{ $header }}</option>
                                            @endforeach
                                        </select>
                                        @error('filterColumn') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="tools-project-code" class="mb-1 block text-sm font-semibold text-slate-700">{{ __('tools.project') }}</label>
                                        <input id="tools-project-code" type="text" wire:model="projectCode" maxlength="150" autocomplete="off" placeholder="102407, 000123, SEAF-26-01"
                                            class="block h-11 w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                        @error('projectCode') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <button type="button" wire:click="preview" wire:loading.attr="disabled" wire:target="preview" data-no-global-loading
                                    class="mt-5 inline-flex h-11 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60">
                                    <span wire:loading.remove wire:target="preview">{{ __('tools.preview_button') }}</span>
                                    <span wire:loading wire:target="preview">{{ __('tools.searching') }}</span>
                                </button>
                            </div>
                        </section>
                    @else
                        <section class="rounded-xl border border-slate-200" aria-labelledby="tools-preview-title">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <h2 id="tools-preview-title" class="text-base font-bold text-slate-900">{{ __('tools.preview') }}</h2>
                                    <p class="text-xs text-slate-600">{{ __('tools.found', ['count' => number_format($matchCount), 'columns' => count($previewHeaders)]) }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" wire:click="modify" data-no-global-loading class="h-10 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-100">{{ __('tools.modify') }}</button>
                                    <button type="button" wire:click="startOver" wire:loading.attr="disabled" wire:target="startOver" data-no-global-loading class="h-10 rounded-lg border border-red-200 bg-white px-4 text-sm font-semibold text-red-700 hover:bg-red-50 disabled:opacity-60">{{ __('tools.start_over') }}</button>
                                    @if ($matchCount > 0)
                                        <button type="button" wire:click="download" wire:loading.attr="disabled" wire:target="download" data-no-global-loading
                                            class="h-10 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60">
                                            <span wire:loading.remove wire:target="download">{{ __('tools.download') }}</span>
                                            <span wire:loading wire:target="download">{{ __('tools.generating') }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            @if ($matchCount === 0)
                                <p class="px-4 py-8 text-center text-sm text-amber-800">{{ __('tools.no_results', ['project' => $projectCode]) }}</p>
                            @else
                                <div class="max-w-full overflow-x-auto">
                                    <table class="min-w-full whitespace-nowrap text-left text-xs">
                                        <thead><tr>
                                            @foreach ($previewHeaders as $header)
                                                <th scope="col" class="min-w-36 border-r border-blue-500 bg-blue-600 px-4 py-3 text-left font-bold text-white" style="background-color: #2563EB; color: #fff">{{ $header }}</th>
                                            @endforeach
                                        </tr></thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach ($previewRows as $row)
                                                <tr wire:key="tools-preview-{{ $previewPage }}-{{ $loop->index }}" class="even:bg-slate-50">
                                                    @foreach ($row as $value)
                                                        <td class="max-w-64 truncate border-r border-slate-100 px-3 py-2 text-slate-700" title="{{ $value }}">{{ $value }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if ($lastPage > 1)
                                    <div class="flex items-center justify-between gap-3 border-t border-slate-200 px-4 py-3 text-sm text-slate-600">
                                        <span>{{ __('tools.page', ['current' => $previewPage, 'total' => $lastPage]) }}</span>
                                        <div class="flex gap-2">
                                            <button type="button" wire:click="goToPage({{ $previewPage - 1 }})" wire:loading.attr="disabled" wire:target="goToPage" @disabled($previewPage <= 1) data-no-global-loading class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold hover:bg-slate-50 disabled:opacity-40">{{ __('tools.previous') }}</button>
                                            <button type="button" wire:click="goToPage({{ $previewPage + 1 }})" wire:loading.attr="disabled" wire:target="goToPage" @disabled($previewPage >= $lastPage) data-no-global-loading class="rounded-lg border border-slate-300 px-3 py-1.5 font-semibold hover:bg-slate-50 disabled:opacity-40">{{ __('tools.next') }}</button>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </section>
                    @endif
                @endif
            </div>
        </section>
    </div>
</div>
