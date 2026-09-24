<script>
    // Share Filament's preference and event contract, including system mode.
    if (!window.appTheme) {
        window.appTheme = {
            media: window.matchMedia('(prefers-color-scheme: dark)'),
            preference: 'system',
            apply(value) {
                this.preference = ['light', 'dark', 'system'].includes(value) ? value : 'system';
                const dark = this.preference === 'dark' || (this.preference === 'system' && this.media.matches);
                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
                document.querySelectorAll('[data-theme-switcher]').forEach(select => { select.value = this.preference; });
                window.dispatchEvent(new CustomEvent('app-theme-applied', { detail: dark }));
            },
            read() { try { return localStorage.getItem('theme') || 'system'; } catch { return this.preference; } }
        };
        window.addEventListener('theme-changed', event => {
            try { localStorage.setItem('theme', event.detail); } catch {}
            window.appTheme.apply(event.detail);
        });
        window.addEventListener('storage', event => { if (event.key === 'theme') window.appTheme.apply(window.appTheme.read()); });
        window.appTheme.media.addEventListener('change', () => window.appTheme.apply(window.appTheme.read()));
        document.addEventListener('livewire:navigated', () => window.appTheme.apply(window.appTheme.read()));
        document.addEventListener('DOMContentLoaded', () => window.appTheme.apply(window.appTheme.read()));
    }
    window.appTheme.apply(window.appTheme.read());
</script>
