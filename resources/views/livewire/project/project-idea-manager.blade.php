<div wire:key="project-idea-manager-root">
    @if ($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 px-3 py-3 sm:py-6"
            wire:key="project-idea-modal-{{ $projectIdeaProjectId }}"
            x-data
            x-on:keydown.escape.window="$wire.closeProjectIdeaModal()">
            <div class="flex min-h-full cursor-pointer items-center justify-center" x-on:click.self="$wire.closeProjectIdeaModal()">
                <section class="flex max-h-[calc(100dvh-1.5rem)] w-full max-w-lg cursor-default flex-col overflow-hidden rounded-xl bg-white shadow-2xl sm:max-h-[calc(100vh-3rem)]"
                    role="dialog" aria-modal="true" aria-labelledby="project-idea-title" x-on:click.stop>
                    <header class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white p-4 sm:p-5">
                        <div class="min-w-0">
                            <h2 id="project-idea-title" class="text-lg font-semibold text-slate-900">Project ideas (Excel)</h2>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $projectIdeaProjectCode }}@if ($projectIdeaProjectName !== '') · {{ $projectIdeaProjectName }}@endif</p>
                        </div>
                        <button wire:click="closeProjectIdeaModal" type="button"
                            class="inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            aria-label="Close modal">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" /></svg>
                        </button>
                    </header>

                    <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 p-4 sm:p-5">
                        @if ($currentProjectIdeaFileName)
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Current Project ideas file</p>
                                <p class="mt-1 break-all text-sm font-semibold text-slate-900">{{ $currentProjectIdeaFileName }}</p>
                                <button wire:click="downloadProjectIdea" wire:loading.attr="disabled" wire:target="downloadProjectIdea" type="button"
                                    class="mt-3 inline-flex h-10 cursor-pointer items-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60">
                                    <span wire:loading.remove wire:target="downloadProjectIdea">Download Excel</span>
                                    <span wire:loading wire:target="downloadProjectIdea">Preparing...</span>
                                </button>
                            </div>
                        @else
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">No Project ideas file has been uploaded.</div>
                        @endif

                        @if ($projectIdeaCanManage)
                            <div class="mt-5" wire:key="project-idea-upload-{{ $projectIdeaProjectId }}">
                                <label for="project-idea-file-{{ $projectIdeaProjectId }}" class="mb-2 block cursor-pointer text-sm font-semibold text-slate-700">
                                    {{ $currentProjectIdeaFileName ? 'Replace Excel file' : 'Upload Excel file' }}
                                </label>
                                <input id="project-idea-file-{{ $projectIdeaProjectId }}" wire:model="projectIdeaFile" type="file" accept=".xlsx,.xls"
                                    class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white p-2 text-sm file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-emerald-600 file:px-3 file:py-2 file:font-semibold file:text-white">
                                <p class="mt-2 text-xs text-slate-500">One Excel file (.xlsx or .xls), maximum 10 MB.</p>
                                @error('projectIdeaFile')<p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>@enderror
                            </div>

                            @if ($projectIdeaDeleteConfirmation)
                                <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4">
                                    <p class="font-semibold text-red-900">Delete this Project ideas Excel file?</p>
                                    <p class="mt-1 text-sm text-red-800">Only this file will be removed. The project and its imported project data remain unchanged.</p>
                                    <div class="mt-3 flex flex-wrap justify-end gap-3">
                                        <button wire:click="cancelProjectIdeaDeletion" type="button" class="cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">Keep file</button>
                                        <button wire:click="deleteProjectIdea" wire:loading.attr="disabled" type="button" class="cursor-pointer rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-wait disabled:opacity-60">Yes, delete Excel</button>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    <footer class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white p-4 sm:flex-row sm:flex-wrap sm:justify-end sm:p-5">
                        <button wire:click="closeProjectIdeaModal" type="button" class="cursor-pointer rounded-lg border border-red-700 bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Cancel</button>
                        @if ($projectIdeaCanManage && $currentProjectIdeaFileName && ! $projectIdeaDeleteConfirmation)
                            <button wire:click="requestProjectIdeaDeletion" type="button" class="cursor-pointer rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">Delete Excel</button>
                        @endif
                        @if ($projectIdeaCanManage)
                            <button wire:click="saveProjectIdea" wire:loading.attr="disabled" wire:target="saveProjectIdea,projectIdeaFile" type="button"
                                class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60">
                                <span wire:loading.remove wire:target="saveProjectIdea">{{ $currentProjectIdeaFileName ? 'Replace Excel' : 'Upload Excel' }}</span>
                                <span wire:loading wire:target="saveProjectIdea">Saving...</span>
                            </button>
                        @endif
                    </footer>
                </section>
            </div>
        </div>
    @endif
</div>
