<form wire:submit="analyze" class="space-y-4" data-no-global-loading
    @submit.capture="if (!ready || uploading || error) { $event.preventDefault(); $event.stopImmediatePropagation(); }"
    x-data="{
        uploading: false, ready: false, progress: 0, name: '', size: '', error: '',
        selectFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.ready = false;
            this.error = '';
            this.progress = 0;
            this.name = file.name;
            this.size = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            event.target.value = '';
            if (!/\.(xlsx|xls)$/i.test(file.name)) {
                this.error = @js(__('tools.error_upload_extension'));
                return;
            }
            if (file.size > 12 * 1024 * 1024) {
                this.error = @js(__('tools.error_upload_size'));
                return;
            }
            if (!file.size) {
                this.error = @js(__('tools.error_upload_invalid'));
                return;
            }
            this.uploading = true;
            $wire.upload('upload', file,
                () => { this.uploading = false; this.ready = true; this.progress = 100; },
                () => { this.uploading = false; this.ready = false; this.error = @js(__('tools.error_upload_failed')); },
                (event) => { this.progress = event.detail.progress; },
                () => { this.uploading = false; this.ready = false; }
            );
        }
    }">
    <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-5">
        <input id="tools-excel-upload" x-ref="file" type="file" accept=".xlsx,.xls" class="hidden" @change="selectFile($event)" aria-label="{{ __('tools.choose_file') }}">
        <div class="flex flex-wrap items-center gap-3">
            <x-tools-button variant="primary" @click="$refs.file.click()" x-bind:disabled="uploading" wire:target="analyze" aria-describedby="tools-upload-hint">
                {{ __('tools.choose_file') }}
            </x-tools-button>
            <p class="min-w-0 break-all text-sm font-medium text-slate-700" x-text="name || @js(__('tools.no_file'))"></p>
            <span class="text-xs text-slate-500" x-show="name" x-text="size"></span>
        </div>
        <p id="tools-upload-hint" class="mt-3 text-xs text-slate-500">{{ __('sap.upload_hint') }}</p>
    </div>
    <div x-cloak x-show="uploading || ready" class="space-y-2">
        <div class="flex justify-between gap-3 text-sm text-blue-700" role="status" aria-live="polite">
            <span x-text="uploading ? @js(__('tools.uploading')) : @js(__('tools.upload_complete'))"></span>
            <span x-text="progress + '%'"></span>
        </div>
        <progress class="h-3 w-full accent-blue-600" max="100" x-bind:value="progress" aria-label="{{ __('tools.uploading') }}"></progress>
    </div>
    <p x-cloak x-show="error" x-text="error" role="alert" class="rounded-lg bg-red-50 p-3 text-sm text-red-700"></p>
    @error('upload') <p role="alert" class="text-sm text-red-700">{{ $message }}</p> @enderror
    <div class="flex justify-end border-t border-slate-100 pt-4">
        <x-tools-button type="submit" variant="primary" wire:target="analyze" x-bind:disabled="!ready || uploading || !!error">
            <span wire:loading.remove wire:target="analyze">{{ __('sap.continue') }}</span>
            <span wire:loading wire:target="analyze" role="status">{{ __('tools.processing') }}</span>
        </x-tools-button>
    </div>
</form>
