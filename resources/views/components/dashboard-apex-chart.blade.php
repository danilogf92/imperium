@props(['options', 'chartKey'])

<div wire:key="{{ $chartKey }}"
    wire:ignore
    class="h-full w-full"
    x-data="{ chart: null }"
    x-init="
        $nextTick(() => {
            chart = new ApexCharts($refs.container, {{ Illuminate\Support\Js::from($options) }});
            chart.render();
        });
    ">
    <div x-ref="container" class="h-full w-full"></div>
</div>
