<x-modal name="delete-project-data" maxWidth="md" close-method="closeDeleteModal" focusable>
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                        style="background-color: #fee2e2; color: #dc2626;">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 7.5h12m-10.5 0 .75 12h7.5l.75-12M9.75 7.5V5.25h4.5V7.5" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Delete data record</h2>
                        <p class="mt-1 text-sm text-slate-500">This action cannot be undone.</p>
                    </div>
                </div>

                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ \Illuminate\Support\Str::limit($deletingDataLabel, 180) }}
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button x-on:click="$dispatch('close-modal', 'delete-project-data')" wire:click="closeDeleteModal"
                        data-no-global-loading type="button"
                        class="data-modal-cancel inline-flex h-10 items-center rounded-lg px-4 text-sm font-semibold">
                        Cancel
                    </button>
                    <button wire:click="deleteData" data-global-loading wire:loading.attr="disabled"
                        wire:target="deleteData" type="button"
                        class="inline-flex h-10 items-center rounded-lg px-4 text-sm font-semibold text-white transition hover:brightness-110 disabled:opacity-60"
                        style="background-color: #dc2626;">
                        <span wire:loading.remove wire:target="deleteData">Delete record</span>
                        <span wire:loading wire:target="deleteData">Deleting...</span>
                    </button>
                </div>
            </div>
        </x-modal>
