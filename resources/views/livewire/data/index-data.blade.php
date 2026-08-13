<div class="dashboard-page-shell">

    <div class="dashboard-page-content space-y-6">
        <livewire:data.data-table :project="$project" :key="'project-data-' . $project->id" />
    </div>
</div>
