<div class="dashboard-page-shell">

    <div class="dashboard-page-content">

        @if ($active)
            {{-- Alertas del módulo --}}
            <livewire:project.alerts />

            {{-- Contenedor principal del módulo --}}
            <div class="mx-auto min-w-0 max-w-full">

                {{-- Acciones --}}
                <div class="mb-4">
                    <livewire:project.actions />
                </div>

                {{-- Filtros --}}
                <div class="mb-4">
                    <livewire:project.filters />
                </div>

                {{-- Tabla --}}
                <div class="min-w-0 max-w-full">
                    <livewire:project.table :active="$active" />
                </div>

            </div>
        @else
            {{-- Usuario o módulo deshabilitado --}}
            <livewire:user.user-disabled />
        @endif

    </div>

</div>
