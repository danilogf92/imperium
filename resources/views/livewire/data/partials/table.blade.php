<div x-data="{ columnsOpen: window.matchMedia('(min-width: 768px)').matches }" x-on:resize.window.debounce="if (window.innerWidth >= 768) columnsOpen = true" class="unified-table-shell min-w-0 max-w-full overflow-hidden">
    @include('livewire.data.partials.columns-toolbar')
    @include('livewire.data.partials.records-table')
</div>
