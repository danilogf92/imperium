<div>
    <x-unified-table-theme />
    @php
        $columnKeys = array_keys($columnOptions);
        $columnCount = count($visibleColumns);
        $physicalColumns = [
            'id',
            'name',
            'links',
            'pda_code',
            'upload_pda',
            'project_ideas',
            'handover_certificate',
            'rate',
            'state',
            'investments',
            'classification',
            'justification',
            'forecast_start_year',
            'forecast_start_date',
            'forecast_end_date',
            'order',
            'plant',
            'creator',
            'responsible',
            'data_uploaded',
            'quartile_date',
            'approve_date',
            'close_date',
            'file_name',
            'created_at',
            'updated_at',
            'budgeted_euros',
            'real_euros',
            'executed_euros',
            'booked_euros',
            'budgeted_dollars',
            'real_dollars',
            'executed_dollars',
            'booked',
            'actions',
        ];
    @endphp

    @if ($active)
        <div class="unified-table-shell relative">
            @include('livewire.project.partials.table-styles')
            @include('livewire.project.partials.table-toolbar')

            <div class="unified-table-scroll">
                <table class="project-table unified-data-table">
                    @include('livewire.project.partials.table-head')
                    @include('livewire.project.partials.table-body')
                </table>
            </div>

            @if ($projects->hasPages())
                <div class="unified-table-pagination">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>

        <livewire:project.project-data-import-manager wire:key="project-data-import-manager" />
        <livewire:project.project-document-manager wire:key="project-document-manager" />
        <livewire:project.project-idea-manager wire:key="project-idea-manager" />
        <livewire:project.project-handover-certificate-manager wire:key="project-handover-certificate-manager" />
    @else
        {{-- <livewire:user-disabled /> --}}
    @endif
</div>
