<section class="flex w-full min-w-0 max-w-full flex-col overflow-x-hidden" style="gap: 1.25rem;">
    <x-unified-table-theme />

    @include('livewire.data.partials.styles')
    @include('livewire.data.partials.header')
    @include('livewire.data.partials.filters')
    @include('livewire.data.partials.table')
    @include('livewire.data.partials.edit-modal')
    @include('livewire.data.partials.delete-modal')
</section>
