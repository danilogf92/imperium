<x-modal name="delete-project-sap-data" maxWidth="md" close-method="closeDeleteSapModal" focusable>
    <div class="p-6">
        <div class="flex items-start gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                style="background-color: #fee2e2; color: #dc2626;">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6 7.5h12m-10.5 0 .75 12h7.5l.75-12M9.75 7.5V5.25h4.5V7.5" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ __('sap.delete_records') }}</h2>
                <p class="mt-1 break-words text-sm text-slate-500">{{ $project->pda_code }} · {{ $project->name }}</p>
            </div>
        </div>

        <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ __('sap.delete_records_confirm', ['count' => number_format($sapRecordCount), 'project' => $project->pda_code]) }}
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button wire:click="closeDeleteSapModal" data-no-global-loading type="button"
                wire:loading.attr="disabled" wire:target="deleteSapData"
                class="data-modal-cancel inline-flex h-10 items-center justify-center rounded-lg px-4 text-sm font-semibold disabled:opacity-60">
                {{ __('Cancel') }}
            </button>
            <button wire:click="deleteSapData" data-no-global-loading wire:loading.attr="disabled"
                wire:target="deleteSapData" type="button" @disabled($sapRecordCount === 0)
                class="inline-flex h-10 items-center justify-center rounded-lg px-4 text-sm font-semibold text-white transition hover:brightness-110 disabled:opacity-60"
                style="background-color: #dc2626;">
                <span wire:loading.remove wire:target="deleteSapData">{{ __('sap.delete_records') }}</span>
                <span wire:loading wire:target="deleteSapData" role="status">{{ __('sap.deleting_records') }}</span>
            </button>
        </div>
    </div>
</x-modal>
