<div class="min-w-0" wire:key="project-ideas-field{{ $fieldSuffix }}"
    x-data="{ uploading: false, progress: 0 }"
    x-on:livewire-upload-start="uploading = true; progress = 0"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
    x-on:livewire-upload-finish="progress = 100; uploading = false"
    x-on:livewire-upload-error="uploading = false; progress = 0"
    x-on:livewire-upload-cancel="uploading = false; progress = 0">
    <label for="project-ideas{{ $fieldSuffix }}" class="mb-2 block text-sm font-medium text-gray-700">
        Project ideas (Excel)
    </label>

    @if ($editing && filled($currentProjectIdeaName ?? null))
        <div class="mb-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Current Project ideas file</p>
            <p class="mt-1 truncate text-sm font-semibold text-slate-900" title="{{ $currentProjectIdeaName }}">{{ $currentProjectIdeaName }}</p>
            <button wire:click="downloadCurrentProjectIdea" wire:loading.attr="disabled" wire:target="downloadCurrentProjectIdea"
                type="button" data-no-global-loading
                class="mt-3 inline-flex h-9 cursor-pointer items-center rounded-lg bg-emerald-600 px-3 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">
                <span wire:loading.remove wire:target="downloadCurrentProjectIdea">Download Excel</span>
                <span wire:loading wire:target="downloadCurrentProjectIdea">Preparing...</span>
            </button>
        </div>
    @endif

    <input id="project-ideas{{ $fieldSuffix }}" type="file" wire:model="projectIdea"
        accept=".xlsx,.xls" data-no-global-loading class="sr-only">
    <label for="project-ideas{{ $fieldSuffix }}"
        class="group relative flex min-w-0 cursor-pointer items-center gap-3 overflow-hidden rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50/40 p-4 transition hover:border-emerald-500 hover:bg-emerald-50">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white shadow-sm">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 16V5m0 0L8 9m4-4 4 4M5 19h14" /></svg>
        </span>
        <span class="min-w-0 flex-1">
            <span class="block text-sm font-semibold text-slate-800">{{ filled($currentProjectIdeaName ?? null) ? 'Replace Project ideas Excel' : 'Upload Project ideas Excel' }}</span>
            <span class="mt-1 block truncate text-xs text-slate-500">
                <span x-show="! uploading">{{ $projectIdea?->getClientOriginalName() ?? 'One Excel file · maximum 10 MB' }}</span>
                <span x-show="uploading" x-cloak class="font-semibold text-emerald-700">Uploading... <span x-text="`${progress}%`">0%</span></span>
            </span>
        </span>
        <div x-show="uploading" x-cloak class="absolute inset-x-0 bottom-0 h-1 bg-emerald-100">
            <div class="h-full bg-emerald-600" x-bind:style="`width: ${progress}%`"></div>
        </div>
    </label>
    @error('projectIdea')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
