<div class="dashboard-page-shell">

    {{-- ================================================================
        ENCABEZADO
        ================================================================ --}}
    <div class="dashboard-page-content space-y-6">

        <div class="module-accent-line rounded-xl border border-slate-200 bg-white px-5 py-5 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-900">
                {{ __('Project Planification') }}
            </h1>

            <p class="mt-1 text-sm text-gray-600">
                {{ __("Build each project's timeline with the milestones configured in Admin.") }}
            </p>
        </div>

        {{-- ============================================================
            MENSAJE DE ÉXITO
            ============================================================ --}}
        @if (session('planification-status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('planification-status') }}
            </div>
        @endif

        {{-- ============================================================
            CONTENEDOR PRINCIPAL
            ============================================================ --}}
        <div class="dashboard-panel overflow-hidden">

            {{-- ========================================================
                BARRA SUPERIOR:
                - Buscador
                - Exportación
                - Registros por página
                - Crear milestone
                ======================================================== --}}
            @include('livewire.planification.partials.bar')


            {{-- ========================================================
                FILTROS
                ======================================================== --}}
            @include('livewire.planification.partials.filters')

            {{-- ========================================================
                TABLA DE PLANIFICACIÓN
                ======================================================== --}}
            @include('livewire.planification.partials.table')

        </div>
    </div>

    {{-- ================================================================
        MODAL CREAR / EDITAR MILESTONE
        ================================================================ --}}
    @include('livewire.planification.partials.milestone-form-modal')
    @include('livewire.planification.partials.weekly-activity-modal')

    {{-- ================================================================
        MODAL DE CONFIRMACIÓN DE ELIMINACIÓN
        ================================================================ --}}
    @include('livewire.planification.partials.delete-milestone-modal')
    @include('livewire.planification.partials.delete-weekly-activity-modal')
</div>
