@if ($hasProjectBookedSupplierData || $hasProjectExecutedSupplierData)
    <section class="space-y-4">
        <div class="soft-title-surface rounded-xl border px-5 py-4">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">Supplier analysis</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900">Project purchasing concentration</h2>
            <p class="mt-1 text-sm text-slate-500">
                Review which suppliers hold the largest commitments and executed values for this project.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @if ($hasProjectBookedSupplierData)
                @include('livewire.project.partials.booked-by-supplier-chart')
            @endif
            @if ($hasProjectExecutedSupplierData)
                @include('livewire.project.partials.executed-by-supplier-chart')
            @endif
        </div>
    </section>
@endif
