<section aria-labelledby="dashboard-operations-title" class="space-y-4">
    <div class="rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 via-white to-white px-5 py-4">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">Operational control</p>
        <h2 id="dashboard-operations-title" class="mt-1 text-lg font-bold text-slate-900">Plants and suppliers</h2>
        <p class="mt-1 text-sm text-slate-500">
            Compare workload and capital by plant, then review purchasing concentration by supplier.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @include('livewire.dashboard.operations.projects-by-plant-chart')
        @include('livewire.dashboard.operations.budget-by-plant-chart')
    </div>

    @if ($hasSupplierBookedData || $hasSupplierExecutedData)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @if ($hasSupplierBookedData)
                @include('livewire.dashboard.operations.booked-by-supplier-chart')
            @endif
            @if ($hasSupplierExecutedData)
                @include('livewire.dashboard.operations.executed-by-supplier-chart')
            @endif
        </div>
    @endif
</section>
