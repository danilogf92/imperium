<div id="global-loading-overlay" aria-live="polite" aria-busy="true"
    style="display: none; position: fixed; inset: 0; z-index: 9999;"
    class="items-center justify-center bg-white/70 backdrop-blur-[2px]">
    <div class="flex flex-col items-center gap-3">
        <div class="rounded-full bg-blue-100 p-5 shadow-xl">
            <svg class="h-16 w-16 animate-spin text-blue-500" viewBox="0 0 24 24" fill="none"
                role="status" aria-label="Loading">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"></path>
            </svg>
        </div>
        <p class="text-base font-semibold tracking-wide text-blue-600">Loading...</p>
    </div>
</div>
<noscript>
    <style>
        #global-loading-overlay {
            display: none !important;
        }
    </style>
</noscript>

@once
    <script>
        (() => {
            let pendingRequests = 0;
            let globalLoadingIntentUntil = 0;
            let loadingWatchdog;

            const overlay = () => document.getElementById('global-loading-overlay');
            const show = () => {
                const element = overlay();
                if (element) {
                    element.style.display = 'flex';
                }

                clearTimeout(loadingWatchdog);
                loadingWatchdog = setTimeout(() => {
                    pendingRequests = 0;
                    hide();
                }, 15000);
            };
            const hide = () => {
                clearTimeout(loadingWatchdog);
                const element = overlay();
                if (element) {
                    element.style.display = 'none';
                }
            };

            const trackLoadingIntent = event => {
                const target = event.target instanceof Element ? event.target : null;

                if (!target) {
                    return;
                }

                // Focusing or typing in a text field must never cover that field
                // with the global overlay. Debounced searches remain interactive.
                if (event.type === 'click' && target.closest('input, textarea, select')) {
                    return;
                }

                if (target.closest('[data-no-global-loading]')) {
                    globalLoadingIntentUntil = 0;
                    hide();
                    return;
                }

                if (target.closest('[data-global-loading]')) {
                    // Wait until Livewire actually starts a request. A visual click
                    // must never leave the overlay open by itself.
                    globalLoadingIntentUntil = Date.now() + 3000;
                }
            };

            document.addEventListener('click', trackLoadingIntent, true);
            document.addEventListener('change', trackLoadingIntent, true);
            document.addEventListener('input', trackLoadingIntent, true);
            document.addEventListener('submit', () => {
                // Loading is opt-in through data-global-loading, including form submissions.
            }, true);

            document.addEventListener('livewire:init', () => {
                Livewire.hook('request', ({ payload, succeed, fail }) => {
                    if (Date.now() > globalLoadingIntentUntil) {
                        return;
                    }

                    globalLoadingIntentUntil = 0;
                    pendingRequests++;
                    show();

                    const finish = () => {
                        pendingRequests = Math.max(0, pendingRequests - 1);
                        if (pendingRequests === 0) {
                            hide();
                        }
                    };

                    succeed(finish);
                    fail(finish);
                });
            });

            document.addEventListener('livewire:navigating', show);
            document.addEventListener('livewire:navigated', () => {
                pendingRequests = 0;
                hide();
            });

            if (document.readyState === 'complete') {
                hide();
            } else {
                window.addEventListener('load', hide, { once: true });
            }

            window.addEventListener('pageshow', event => {
                if (event.persisted) {
                    hide();
                }
            });
        })();
    </script>
@endonce
