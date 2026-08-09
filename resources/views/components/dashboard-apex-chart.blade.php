@props(['options', 'chartKey'])

<div wire:key="{{ $chartKey }}"
    wire:ignore
    class="h-full w-full"
    x-data="{ chart: null }"
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
            chart = new ApexCharts($refs.container, reviveFormatters({{ Illuminate\Support\Js::from($options) }}));
            chart.render();
        });
    ">
    <div x-ref="container" class="h-full w-full"></div>
</div>
