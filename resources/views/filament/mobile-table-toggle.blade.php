<button type="button" class="app-admin-table-toggle" x-data="{ expanded: false }"
    x-on:click="expanded = !expanded; $el.closest('.fi-ta-ctn').classList.toggle('app-table-full', expanded)"
    x-bind:aria-pressed="expanded">
    <span x-show="!expanded">{{ __('All columns') }}</span>
    <span x-show="expanded" x-cloak>{{ __('Compact view') }}</span>
</button>
