<div wire:key="project-handover-certificate-manager-root">
    @if ($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 px-3 py-3 sm:py-6"
            wire:key="project-handover-certificate-modal-{{ $projectId }}" x-data="{ uploading: false, progress: 0 }"
            x-on:keydown.escape.window="$wire.close()"
            x-on:livewire-upload-start="uploading = true; progress = 0"
            x-on:livewire-upload-progress="progress = $event.detail.progress"
            x-on:livewire-upload-finish="progress = 100; uploading = false"
            x-on:livewire-upload-error="uploading = false; progress = 0"
            x-on:livewire-upload-cancel="uploading = false; progress = 0">
            <div class="flex min-h-full cursor-pointer items-center justify-center" x-on:click.self="$wire.close()">
                <section class="flex max-h-[calc(100dvh-1.5rem)] w-full max-w-lg cursor-default flex-col overflow-hidden rounded-xl bg-white shadow-2xl sm:max-h-[calc(100vh-3rem)]"
                    role="dialog" aria-modal="true" aria-labelledby="handover-certificate-modal-title" x-on:click.stop>
                    <header class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white p-4 sm:p-5">
                        <div class="min-w-0">
                            <h2 id="handover-certificate-modal-title" class="text-lg font-semibold text-slate-900">Project Handover Certificate (PDF)</h2>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $projectCode }}@if ($projectName !== '') · {{ $projectName }}@endif</p>
                        </div>
                        <button wire:click="close" type="button"
                            class="inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 focus:ring-2 focus:ring-violet-500"
                            aria-label="Close modal">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor"><path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" /></svg>
                        </button>
                    </header>

                    <div class="min-h-0 flex-[0_1_auto] overflow-y-auto bg-slate-50 p-4 sm:p-5">
                        @if ($currentDocumentName)
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Current Project Handover Certificate</p>
                                <p class="mt-1 break-all text-sm font-semibold text-slate-900">{{ $currentDocumentName }}</p>
                                <button wire:click="download" wire:loading.attr="disabled" wire:target="download" type="button"
                                    class="mt-3 cursor-pointer rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60">
                                    <span wire:loading.remove wire:target="download">Download PDF</span>
                                    <span wire:loading wire:target="download">Preparing...</span>
                                </button>
                            </div>

                            @unless ($deleteConfirmation)
                                <div class="mt-4">
                                    <label for="handover-replacement-{{ $projectId }}" class="mb-2 block cursor-pointer text-sm font-semibold text-slate-700">Replace certificate</label>
                                    <input id="handover-replacement-{{ $projectId }}" wire:model="document" type="file" accept=".pdf,application/pdf" class="sr-only">
                                    <label for="handover-replacement-{{ $projectId }}"
                                        class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border-2 border-dashed border-violet-300 bg-violet-50 p-4 hover:border-violet-500">
                                        <span class="min-w-0 truncate text-sm font-semibold text-slate-700">{{ $document?->getClientOriginalName() ?? 'Select a replacement PDF' }}</span>
                                        <span class="shrink-0 rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold text-white">Choose file</span>
                                    </label>
                                    @error('document')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                                </div>
                            @endunless
                        @else
                            <label for="handover-file-{{ $projectId }}" class="mb-2 block cursor-pointer text-sm font-semibold text-slate-700">Upload certificate</label>
                            <input id="handover-file-{{ $projectId }}" wire:model="document" type="file" accept=".pdf,application/pdf" class="sr-only">
                            <label for="handover-file-{{ $projectId }}"
                                class="group relative flex min-w-0 cursor-pointer items-center gap-3 overflow-hidden rounded-xl border-2 border-dashed border-violet-300 bg-violet-50/40 p-4 transition hover:border-violet-500 hover:bg-violet-50">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-violet-600 text-white shadow-md">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 16V5m0 0L8 9m4-4 4 4M5 19h14" /></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-slate-800">Upload Project Handover Certificate</span>
                                    <span class="mt-1 block truncate text-xs text-slate-500">
                                        <span x-show="! uploading">{{ $document?->getClientOriginalName() ?? 'Click to select one PDF · maximum 10 MB' }}</span>
                                        <span x-show="uploading" x-cloak class="font-semibold text-violet-700">Uploading... <span x-text="`${progress}%`">0%</span></span>
                                    </span>
                                </span>
                                <div x-show="uploading" x-cloak class="absolute inset-x-0 bottom-0 h-1 bg-violet-100">
                                    <div class="h-full bg-violet-600 transition-all duration-200" x-bind:style="`width: ${progress}%`"></div>
                                </div>
                            </label>
                            <p class="mt-2 text-xs text-slate-500">One PDF file, maximum 10 MB.</p>
                            @error('document')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                        @endif

                        @if ($deleteConfirmation)
                            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4">
                                <p class="font-semibold text-red-900">Delete this Project Handover Certificate?</p>
                                <p class="mt-1 text-sm text-red-800">Only this PDF will be permanently removed.</p>
                                <div class="mt-3 flex justify-end gap-3">
                                    <button wire:click="$set('deleteConfirmation', false)" type="button" class="cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold">Keep PDF</button>
                                    <button wire:click="delete" wire:loading.attr="disabled" wire:target="delete" type="button" class="cursor-pointer rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-wait disabled:opacity-60">
                                        <span wire:loading.remove wire:target="delete">Yes, delete PDF</span>
                                        <span wire:loading wire:target="delete">Deleting...</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <footer class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white p-4 sm:flex-row sm:justify-end sm:p-5">
                        <button wire:click="close" type="button" class="cursor-pointer rounded-lg border border-red-700 bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Cancel</button>
                        @if ($currentDocumentName && ! $deleteConfirmation)
                            <button wire:click="$set('deleteConfirmation', true)" type="button" class="cursor-pointer rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Delete PDF</button>
                        @endif
                        @if (! $deleteConfirmation && $document)
                            <button wire:click="saveDocument" wire:loading.attr="disabled"
                                wire:target="saveDocument,document" type="button"
                                class="cursor-pointer rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700 disabled:cursor-wait disabled:opacity-60">
                                <span wire:loading.remove wire:target="saveDocument">{{ $currentDocumentName ? 'Replace PDF' : 'Upload PDF' }}</span>
                                <span wire:loading wire:target="saveDocument">Saving PDF...</span>
                            </button>
                        @endif
                    </footer>
                </section>
            </div>
        </div>
    @endif
</div>
