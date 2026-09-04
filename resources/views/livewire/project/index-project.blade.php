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

<script data-navigate-once>
    if (!window.__projectHistoryFilterResetInstalled) {
        window.__projectHistoryFilterResetInstalled = true;
        window.__projectHistoryNavigation = false;

        const projectsPath = @js(parse_url(route('projects'), PHP_URL_PATH));
        const resetProjectFilters = () => {
            if (window.location.pathname !== projectsPath || !window.Livewire) return;

            document.querySelectorAll('[data-dashboard-filter-menu] input[type="checkbox"], [data-dashboard-filter-menu] input[type="radio"]')
                .forEach(input => input.checked = false);

            queueMicrotask(() => window.Livewire.dispatch('project-reset-all'));
        };

        window.addEventListener('popstate', () => {
            window.__projectHistoryNavigation = true;
        }, { capture: true });

        document.addEventListener('livewire:navigated', () => {
            if (!window.__projectHistoryNavigation) return;

            window.__projectHistoryNavigation = false;
            resetProjectFilters();
        });

        window.addEventListener('pageshow', event => {
            if (event.persisted) resetProjectFilters();
        });
    }
</script>
