<div class="dashboard-page-shell">

    <div class="dashboard-page-content">

        @if ($active)
            {{-- Alertas del módulo --}}
            <livewire:project.alerts />

            {{-- Contenedor principal del módulo --}}
            <div class="mx-auto min-w-0 max-w-full space-y-6">

                {{-- Acciones --}}
                <div>
                    <livewire:project.actions />
                </div>

                {{-- Filtros --}}
                <div>
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
