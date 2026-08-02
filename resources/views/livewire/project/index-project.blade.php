<div class="min-h-screen bg-gray-100 dark:bg-gray-900">

    <div class="mx-auto w-full px-4 py-2 sm:px-6 lg:px-8">

        @if ($active)
            {{-- Alertas del módulo --}}
            <livewire:project.alerts />

            {{-- Contenedor principal del módulo --}}
            <div class="mx-auto w-full">

                {{-- Acciones --}}
                <div class="mb-4">
                    <livewire:project.actions />
                </div>

                {{-- Filtros --}}
                <div class="mb-4">
                    <livewire:project.filters />
                </div>

                {{-- Tabla --}}
                <div>
                    <livewire:project.table :active="$active" />
                </div>

            </div>
        @else
            {{-- Usuario o módulo deshabilitado --}}
            <livewire:user.user-disabled />
        @endif

    </div>

</div>
