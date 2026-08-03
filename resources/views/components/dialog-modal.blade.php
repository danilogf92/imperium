@props(['name', 'maxWidth' => '2xl', 'closeMethod' => null])

<x-modal :name="$name" :maxWidth="$maxWidth" :close-method="$closeMethod" {{ $attributes }}>
    {{-- Encabezado del modal --}}
    <div class="shrink-0 border-b border-gray-200 bg-white px-6 py-4">
        <div class="text-lg font-medium text-gray-900">
            {{ $title }}
        </div>
    </div>

    {{-- Contenido con scroll --}}
    <div class="min-h-0 flex-1 overflow-y-auto bg-gray-50 px-6 py-5">
        <div class="text-sm text-gray-600">
            {{ $content }}
        </div>
    </div>

    {{-- Pie del modal --}}
    <div class="shrink-0 border-t border-gray-200 bg-gray-100 px-6 py-4">
        <div class="flex w-full flex-row items-center justify-end gap-3">
            {{ $footer }}
        </div>
    </div>
</x-modal>
