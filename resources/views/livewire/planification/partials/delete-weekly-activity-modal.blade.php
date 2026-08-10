@if ($pendingActivityDeleteId)
    <div wire:key="weekly-activity-delete-modal"
        class="fixed inset-0 z-[80] flex items-center justify-center p-2 sm:p-4"
        x-data x-on:keydown.escape.window="$wire.cancelDeleteWeeklyActivity()">
        <div class="absolute inset-0 cursor-pointer bg-slate-900/60 backdrop-blur-sm"
            wire:click="cancelDeleteWeeklyActivity" data-no-global-loading></div>

        <div wire:click.stop
            class="relative z-10 w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="px-6 pb-5 pt-6 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v4m0 4h.01M10.3 3.7 2.8 17a2 2 0 0 0 1.74 3h14.92A2 2 0 0 0 21.2 17L13.7 3.7a2 2 0 0 0-3.4 0Z" />
                    </svg>
                </span>
                <h2 class="mt-4 text-lg font-bold text-slate-900">Delete weekly activity?</h2>
                <p class="mt-2 text-sm text-slate-600">This activity will be permanently removed.</p>
                <p class="mt-3 max-h-28 overflow-y-auto whitespace-pre-line rounded-lg bg-slate-50 px-3 py-2 text-left text-sm font-semibold text-slate-800">
                    {{ $pendingActivityDeleteLabel }}
                </p>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:justify-end sm:px-6">
                <button type="button" wire:click="cancelDeleteWeeklyActivity" data-no-global-loading
                    class="inline-flex h-10 cursor-pointer items-center justify-center rounded-lg border border-red-700 bg-red-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                    Cancel
                </button>
                <button type="button" wire:click="confirmDeleteWeeklyActivity" data-global-loading
                    wire:loading.attr="disabled" wire:target="confirmDeleteWeeklyActivity"
                    class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-red-700 disabled:cursor-wait disabled:opacity-60">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 7h12m-10 0 1 12h6l1-12M9 7V4h6v3" />
                    </svg>
                    <span wire:loading.remove wire:target="confirmDeleteWeeklyActivity">Delete activity</span>
                    <span wire:loading wire:target="confirmDeleteWeeklyActivity">Deleting...</span>
                </button>
            </div>
        </div>
    </div>
@endif
