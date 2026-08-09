<section class="space-y-4">
    <div class="rounded-xl border border-indigo-200 bg-gradient-to-r from-indigo-50 via-white to-white px-5 py-4">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-700">Executive control</p>
        <h2 class="mt-1 text-lg font-bold text-slate-900">Project health and delivery</h2>
        <p class="mt-1 text-sm text-slate-500">Financial deviations, planned progress, milestones, weekly execution and information quality.</p>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">@include('livewire.project.insights.health') @include('livewire.project.insights.financial-variances') @include('livewire.project.insights.alerts-quality')</div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">@include('livewire.project.insights.progress') @include('livewire.project.insights.schedule')</div>
    @include('livewire.project.insights.activities')
</section>
