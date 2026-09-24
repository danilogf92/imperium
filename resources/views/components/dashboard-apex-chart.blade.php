@props(['options', 'chartKey'])

<div wire:key="{{ $chartKey }}"
    wire:ignore
    class="h-full min-w-0 w-full"
    x-data="{ chart: null, themeObserver: null, destroy() { this.themeObserver?.disconnect(); this.chart?.destroy(); } }"
    x-init="
        $nextTick(() => {
            const reviveFormatters = (value) => {
                if (Array.isArray(value)) return value.map(reviveFormatters);
                if (value && typeof value === 'object') {
                    return Object.fromEntries(Object.entries(value).map(([key, item]) => [key, reviveFormatters(item)]));
                }
                if (typeof value === 'string' && (value.trim().startsWith('function') || value.includes('=>'))) {
                    try { return Function('return (' + value + ')')(); } catch (error) { return value; }
                }
                return value;
            };
            const options = reviveFormatters({{ Illuminate\Support\Js::from($options) }});
            options.chart = { ...options.chart, width: '100%', redrawOnParentResize: true, redrawOnWindowResize: true };
            options.responsive = [...(options.responsive ?? []), {
                breakpoint: 640,
                options: {
                    chart: { ...options.chart, height: 320 },
                    legend: { ...options.legend, position: 'bottom', fontSize: '11px' },
                    xaxis: { ...options.xaxis, labels: { ...options.xaxis?.labels, style: { ...options.xaxis?.labels?.style, fontSize: '10px' }, hideOverlappingLabels: true } },
                    ...(!Array.isArray(options.yaxis) ? { yaxis: { ...options.yaxis, labels: { ...options.yaxis?.labels, minWidth: 40, maxWidth: 70, style: { ...options.yaxis?.labels?.style, fontSize: '10px' } } } } : {})
                }
            }];
            chart = new ApexCharts($refs.container, options);
            chart.render().then(() => {
                const applyTheme = () => {
                    const dark = document.documentElement.classList.contains('dark');
                    chart.updateOptions({ chart: { background: 'transparent', foreColor: dark ? '#cbd5e1' : '#475569' }, tooltip: { theme: dark ? 'dark' : 'light' } }, false, false);
                };
                applyTheme();
                themeObserver = new MutationObserver(applyTheme);
                themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            });
        });
    ">
    <div x-ref="container" class="h-full w-full"></div>
</div>
