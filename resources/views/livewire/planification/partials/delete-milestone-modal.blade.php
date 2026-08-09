@if ($pendingDeleteId)
    <div wire:key="planification-delete-modal" class="fixed inset-0 z-[60] flex items-center justify-center p-2 sm:p-4" x-data
        x-on:keydown.escape.window="$wire.cancelDelete()">

        {{-- ============================================================
            FONDO DEL MODAL

            Cualquier click fuera del contenido cancela la eliminación.
            ============================================================ --}}
        <div class="absolute inset-0 cursor-pointer bg-slate-900/60 backdrop-blur-sm" wire:click="cancelDelete"
            data-no-global-loading></div>


        {{-- ============================================================
            CONTENIDO DEL MODAL

            relative + z-10 mantiene el contenido encima del fondo.
            wire:click.stop evita que un click dentro cierre el modal.
            ============================================================ --}}
        <div wire:click.stop
            class="relative z-10 max-h-[calc(100dvh-1rem)] w-full max-w-md overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-2xl sm:rounded-2xl">

            {{-- ========================================================
                CONTENIDO
                ======================================================== --}}
            <div class="px-6 pb-5 pt-6 text-center">

                {{-- Icono de advertencia --}}
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">

                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v4m0 4h.01M10.3 3.7 2.8 17a2 2 0 0 0 1.74 3h14.92A2 2 0 0 0 21.2 17L13.7 3.7a2 2 0 0 0-3.4 0Z" />
                    </svg>
                </span>


                {{-- Título --}}
                <h2 class="mt-4 text-lg font-bold text-slate-900">
                    Remove milestone?
                </h2>


                {{-- Descripción --}}
                <p class="mt-2 text-sm text-slate-600">
                    This milestone will be removed from the project timeline.
                </p>


                {{-- Milestone seleccionado --}}
                <p class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-800">
                    {{ $pendingDeleteLabel }}
                </p>
            </div>


            {{-- ========================================================
                ACCIONES
                ======================================================== --}}
            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:justify-end sm:px-6">

                {{-- Cancelar --}}
                <button type="button" wire:click="cancelDelete" data-no-global-loading
                    class="inline-flex h-10 cursor-pointer items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                    Cancel
                </button>


                {{-- Confirmar eliminación --}}
                <button type="button" wire:click="confirmDeleteMilestone" data-global-loading
                    wire:loading.attr="disabled" wire:target="confirmDeleteMilestone"
                    class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">

                    {{-- Icono eliminar --}}
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 7h12m-10 0 1 12h6l1-12M9 7V4h6v3" />
                    </svg>


                    {{-- Texto normal --}}
                    <span wire:loading.remove wire:target="confirmDeleteMilestone">
                        Remove milestone
                    </span>


                    {{-- Texto durante eliminación --}}
                    <span wire:loading wire:target="confirmDeleteMilestone">
                        Removing...
                    </span>
                </button>
            </div>
        </div>
    </div>
@endif
