<section aria-labelledby="dashboard-insights-title" class="space-y-4">
    <div class="soft-title-surface rounded-xl border px-5 py-4">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Decision support</p>
        <h2 id="dashboard-insights-title" class="mt-1 text-lg font-bold text-slate-900">Portfolio insights</h2>
        <p class="mt-1 text-sm text-slate-500">
            Financial control, information quality and delivery-stage indicators. All charts react to the dashboard filters.
        </p>
    </div>

    @if ($hasInsightFinancialData)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @include('livewire.dashboard.insights.financial-flow-chart')
            @include('livewire.dashboard.insights.budget-availability-chart')
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @include('livewire.dashboard.insights.data-coverage-chart')
        @include('livewire.dashboard.insights.portfolio-stage-chart')
    </div>
</section>
