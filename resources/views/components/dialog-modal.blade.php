@props(['name', 'maxWidth' => '2xl', 'closeMethod' => null, 'closeButtonRed' => false])

<x-modal :name="$name" :maxWidth="$maxWidth" :close-method="$closeMethod" :scrollable="false" {{ $attributes }}>
    {{-- Encabezado del modal --}}
    <div class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-200 bg-white px-4 py-3 sm:px-6 sm:py-4">
        <div class="min-w-0 text-lg font-medium text-gray-900">
            {{ $title }}
        </div>
        @if ($closeMethod)
            <button type="button" x-on:click="$dispatch('close')"
                @class([
                    'inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg transition focus:outline-none focus:ring-2',
                    '!text-red-600 hover:bg-red-50 hover:!text-red-700 focus:ring-red-500' => $closeButtonRed,
                    'text-slate-500 hover:bg-slate-100 hover:text-slate-800 focus:ring-blue-500' => ! $closeButtonRed,
                ])
                aria-label="Close modal">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" d="m5 5 10 10M15 5 5 15" />
                </svg>
            </button>
        @endif
    </div>

    {{-- Contenido con scroll --}}
    <div class="app-modal-content min-h-0 flex-[0_1_auto] overflow-y-auto bg-gray-50 px-4 py-4 sm:px-6 sm:py-5">
        <div class="text-sm text-gray-600">
            {{ $content }}
        </div>
    </div>

    {{-- Pie del modal --}}
    <div class="app-modal-footer shrink-0 border-t border-gray-200 bg-gray-100 px-4 py-3 sm:px-6 sm:py-4">
        {{ $footer }}
    </div>
</x-modal>
