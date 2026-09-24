<label class="inline-flex shrink-0 items-center gap-1 text-xs text-slate-600">
    <span class="sr-only">{{ __('Appearance') }}</span>
    <select aria-label="{{ __('Appearance') }}" class="max-w-28 rounded-lg border-slate-200 bg-white py-2 pl-2 pr-7 text-sm"
        data-theme-switcher onchange="window.dispatchEvent(new CustomEvent('theme-changed', { detail: this.value }))">
        <option value="system">{{ __('System') }}</option>
        <option value="light">{{ __('Light') }}</option>
        <option value="dark">{{ __('Dark') }}</option>
    </select>
</label>
