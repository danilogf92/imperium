<div wire:key="project-data-import-manager-root">
    @if ($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 px-3 py-3 sm:py-6"
            wire:key="project-data-import-modal-{{ $projectId }}"
            x-data="{ uploading: false, progress: 0 }"
            x-on:keydown.escape.window="$wire.close()"
            x-on:livewire-upload-start="uploading = true; progress = 0"
            x-on:livewire-upload-progress="progress = $event.detail.progress"
            x-on:livewire-upload-finish="progress = 100; uploading = false"
            x-on:livewire-upload-error="uploading = false; progress = 0">
            <div class="flex min-h-full cursor-pointer items-center justify-center" x-on:click.self="$wire.close()">
                <section class="flex max-h-[calc(100dvh-1.5rem)] w-full max-w-2xl cursor-default flex-col overflow-hidden rounded-xl bg-white shadow-2xl sm:max-h-[calc(100vh-3rem)]"
                    role="dialog" aria-modal="true" aria-labelledby="data-import-title" x-on:click.stop>
                    <header class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white p-4 sm:p-5">
                        <div class="min-w-0">
                            <h2 id="data-import-title" class="text-lg font-semibold text-slate-900">Import project data from Excel</h2>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $projectCode }}@if ($projectName !== '') · {{ $projectName }}@endif</p>
                        </div>
                        <button wire:click="close" type="button" class="inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 focus:ring-2 focus:ring-cyan-500" aria-label="Close modal">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor"><path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" /></svg>
                        </button>
                    </header>

                    <div class="min-h-0 flex-[0_1_auto] overflow-y-auto bg-slate-50 p-4 sm:p-5">
                        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                            Complete the approved template without changing its column names. The ID column is ignored.
                            <a href="{{ route('templates') }}" wire:navigate class="ml-1 cursor-pointer font-semibold underline underline-offset-2">Download template</a>
                        </div>

                        @if ($existingRows > 0)
                            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <p class="font-semibold text-amber-900">This project already has imported data</p>
                                <p class="mt-1 text-sm text-amber-800">{{ number_format($existingRows) }} rows exist. Delete those rows before importing a replacement workbook.</p>
                            </div>
                        @else
                            <div class="mt-5">
                                <input id="project-data-file-{{ $projectId }}" wire:model="file" type="file" accept=".xlsx,.xls" class="sr-only">
                                <label for="project-data-file-{{ $projectId }}"
                                    class="group relative flex min-w-0 cursor-pointer items-center gap-3 overflow-hidden rounded-xl border-2 border-dashed border-cyan-300 bg-cyan-50/50 p-4 hover:border-cyan-500 hover:bg-cyan-50">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-cyan-600 text-white shadow-sm">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 16V5m0 0L8 9m4-4 4 4M5 19h14" /></svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold text-slate-800">Select completed project data Excel</span>
                                        <span class="mt-1 block truncate text-xs text-slate-500">
                                            <span x-show="! uploading">{{ $file?->getClientOriginalName() ?? '.xlsx or .xls · maximum 20 MB · 5,000 rows' }}</span>
                                            <span x-show="uploading" x-cloak class="font-semibold text-cyan-700">Uploading... <span x-text="`${progress}%`">0%</span></span>
                                        </span>
                                    </span>
                                    <div x-show="uploading" x-cloak class="absolute inset-x-0 bottom-0 h-1 bg-cyan-100"><div class="h-full bg-cyan-600" x-bind:style="`width: ${progress}%`"></div></div>
                                </label>
                            </div>
                        @endif

                        @error('file')
                            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800">{{ $message }}</div>
                        @enderror

                        @if ($errors->has('dataImportFile'))
                            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800">{{ $errors->first('dataImportFile') }}</div>
                        @endif

                        @if ($errors->has('dataImportErrors'))
                            <div class="mt-4 rounded-xl border border-red-300 bg-red-50 p-4">
                                <h3 class="font-semibold text-red-900">Correct these cells and upload the workbook again</h3>
                                <ol class="mt-3 max-h-56 list-decimal space-y-1 overflow-y-auto pl-5 text-sm text-red-800">
                                    @foreach ($errors->get('dataImportErrors') as $importError)
                                        <li>{{ $importError }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        @endif

                        @if ($deleteConfirmation)
                            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4">
                                <p class="font-semibold text-red-900">Delete {{ number_format($existingRows) }} imported data rows?</p>
                                <p class="mt-1 text-sm text-red-800">The project, PDA and Project ideas files remain unchanged.</p>
                            </div>
                        @endif
                    </div>

                    <footer class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white p-4 sm:flex-row sm:justify-end sm:p-5">
                        <button wire:click="close" type="button" class="cursor-pointer rounded-lg border border-red-700 bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Cancel</button>
                        @if ($existingRows > 0 && ! $deleteConfirmation)
                            <button wire:click="$set('deleteConfirmation', true)" type="button" class="cursor-pointer rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Delete imported rows</button>
                        @elseif ($deleteConfirmation)
                            <button wire:click="deleteImportedRows" wire:loading.attr="disabled" type="button" class="cursor-pointer rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white disabled:cursor-wait disabled:opacity-60">Yes, delete rows</button>
                        @else
                            <button wire:click="importProjectDataWorkbook" wire:loading.attr="disabled" wire:target="importProjectDataWorkbook" type="button" class="cursor-pointer rounded-lg bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700 disabled:cursor-wait disabled:opacity-60">
                                <span wire:loading.remove wire:target="importProjectDataWorkbook">Import project data</span><span wire:loading wire:target="importProjectDataWorkbook">Validating and importing...</span>
                            </button>
                        @endif
                    </footer>
                </section>
            </div>
        </div>
    @endif
</div>
