<section class="flex w-full min-w-0 max-w-full flex-col gap-6 overflow-x-hidden">
    <x-unified-table-theme />

    @include('livewire.data.partials.styles')
    @include('livewire.data.partials.header')
    @include('livewire.data.partials.filters')
    @include('livewire.data.partials.table')
    @include('livewire.data.partials.edit-modal')
    @include('livewire.data.partials.delete-modal')
</section>
