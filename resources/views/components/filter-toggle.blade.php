<button type="button" x-on:click="open = !open" x-bind:aria-expanded="open"
    aria-label="{{ __('Filters') }}" class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-700">
    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M7 12h10m-7 5h4" /></svg>
    <span>{{ __('Filters') }}</span><span aria-hidden="true" x-text="open ? '−' : '+'"></span>
</button>
