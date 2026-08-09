@if ($areaCumulativeChart['hasData'])
    <x-dashboard-chart-card
        title="Area budget vs cumulative budget"
        subtitle="Monthly area investment and cumulative portfolio budget"
        filename="area-budget-vs-cumulative-budget"
        height="40rem"
    >
        <div
            wire:key="area-cumulative-chart-{{ md5(json_encode($areaCumulativeChart)) }}"
            x-data="{
                chart: null,

                init() {
                    const categories = @js($areaCumulativeChart['categories']);
                    const areaSeries = @js($areaCumulativeChart['areaSeries']);
                    const cumulativeSeries = @js($areaCumulativeChart['cumulativeSeries']);
                    const areaSeriesNames = @js($areaCumulativeChart['areaSeriesNames']);
                    const currencySymbol = @js($areaCumulativeChart['currencySymbol']);

                    const money = (value) => {
                        return currencySymbol + ' ' +
                            Number(value).toLocaleString(
                                undefined,
                                {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }
                            );
                    };

                    const options = {
                        chart: {
                            type: 'line',
                            height: '100%',
                            stacked: false,
                            toolbar: {
                                show: false
                            },
                            animations: {
                                enabled: true
                            }
                        },

                        series: [
                            ...areaSeries,
                            cumulativeSeries
                        ],

                        stroke: {
                            width: [
                                ...areaSeries.map(() => 0),
                                3
                            ],
                            curve: 'straight'
                        },

                        plotOptions: {
                            bar: {
                                columnWidth: '55%'
                            }
                        },

                        dataLabels: {
                            enabled: false
                        },

                        xaxis: {
                            categories: categories,
                            labels: {
                                rotate: -45,
                                hideOverlappingLabels: true
                            }
                        },

                        yaxis: [
                            {
                                seriesName: areaSeriesNames,
                                title: {
                                    text: 'Budget by area'
                                },
                                labels: {
                                    formatter: money
                                },
                                min: 0,
                                forceNiceScale: true
                            },
                            {
                                seriesName: 'Cumulative budget',
                                opposite: true,
                                title: {
                                    text: 'Cumulative budget'
                                },
                                labels: {
                                    formatter: money
                                },
                                min: 0,
                                forceNiceScale: true
                            }
                        ],

                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: money
                            }
                        },

                        legend: {
                            position: 'bottom'
                        },

                        grid: {
                            borderColor: '#e2e8f0'
                        },

                        markers: {
                            size: [
                                ...areaSeries.map(() => 0),
                                4
                            ]
                        }
                    };

                    this.chart = new ApexCharts(
                        this.$refs.chart,
                        options
                    );

                    this.chart.render();
                },

                destroy() {
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }
                }
            }"
            x-init="init()"
            x-on:livewire:navigating.window="destroy()"
            class="h-full w-full"
        >
            <div
                x-ref="chart"
                class="h-full w-full"
            ></div>
        </div>

        <x-slot:footer>
            Left axis: monthly budget grouped by project area.
            Right axis: cumulative total budget by project creation month.
        </x-slot:footer>
    </x-dashboard-chart-card>
@else
    <section
        class="flex min-h-64 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center"
    >
        <div>
            <h2 class="font-semibold text-slate-800">
                {{ __('No area budget data available') }}
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                {{ __('The combined area and cumulative budget chart will appear when area data is available.') }}
            </p>
        </div>
    </section>
@endif
