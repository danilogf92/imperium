<div class="flex justify-end px-3 py-1 md:hidden">
    <button type="button" x-on:click="fullTable = !fullTable" x-bind:aria-pressed="fullTable"
        class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-700">
        <span x-show="!fullTable">{{ __('All columns') }}</span>
        <span x-show="fullTable" x-cloak>{{ __('Compact view') }}</span>
    </button>
</div>
