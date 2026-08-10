@if ($chartData['hasData'])
    <x-dashboard-chart-card :title="$chartTitle"
        :subtitle="$chartSubtitle" :filename="$chartFilename"
        height="30rem">
        <div wire:key="cumulative-projects-budget-{{ $chartKey }}-{{ md5(json_encode($chartData)) }}"
            x-data="{
                chart: null,
            
                init() {
                    this.destroy();

                    const categories =
                        @js($chartData['categories']);
            
                    const projects =
                        @js($chartData['projectPercentages']);
            
                    const budget =
                        @js($chartData['budget']);
            
                    const symbol =
                        @js($chartData['currencySymbol']);

                    const valueSeriesLabel =
                        @js($valueSeriesLabel);
                    const projectSeriesLabel =
                        @js($projectSeriesLabel);
            
                    const money = value => {
                        const number = Number(value);
            
                        if (Math.abs(number) >= 1000000) {
                            return symbol + ' ' +
                                (number / 1000000).toFixed(2) +
                                ' M';
                        }
            
                        if (Math.abs(number) >= 1000) {
                            return symbol + ' ' +
                                (number / 1000).toFixed(2) +
                                ' K';
                        }
            
                        return symbol + ' ' +
                            number.toFixed(2);
                    };
            
                    this.chart = new ApexCharts(
                        this.$refs.chart, {
                            chart: {
                                height: '100%',
                                type: 'line',
                                stacked: false,
                                fontFamily: 'inherit',
                                parentHeightOffset: 0,

                                zoom: {
                                    enabled: false
                                },

                                selection: {
                                    enabled: false
                                },

                                pan: {
                                    enabled: false
                                },
            
                                toolbar: {
                                    show: false
                                }
                            },
            
                            series: [{
                                    name: projectSeriesLabel,
                                    type: 'line',
                                    data: projects
                                },
                                {
                                    name: valueSeriesLabel,
                                    type: 'column',
                                    data: budget
                                }
                            ],
            
                            colors: [
                                '#2563eb',
                                '#eb9d17'
                            ],
            
                            stroke: {
                                width: [3, 0],
                                curve: 'smooth'
                            },
            
                            markers: {
                                size: [4, 0],
                                strokeWidth: 2,
                                hover: {
                                    sizeOffset: 2
                                }
                            },
            
                            plotOptions: {
                                bar: {
                                    columnWidth: '52%',
                                    borderRadius: 3
                                }
                            },
            
                            dataLabels: {
                                enabled: false
                            },
            
                            xaxis: {
                                categories: categories,
                                tickPlacement: 'on',
                                axisBorder: {
                                    color: '#cbd5e1'
                                },
                                axisTicks: {
                                    color: '#cbd5e1'
                                },
                                labels: {
                                    rotate: -35,
                                    rotateAlways: categories.length > 7,
                                    hideOverlappingLabels: true,
                                    trim: true,
                                    maxHeight: 52,
                                    style: {
                                        colors: '#64748b',
                                        fontSize: '11px'
                                    }
                                }
                            },
            
                            yaxis: [{
                                    seriesName: projectSeriesLabel,
            
                                    min: 0,
                                    max: 100,
                                    tickAmount: 5,
            
                                    labels: {
                                        minWidth: 34,
                                        maxWidth: 42,
                                        style: {
                                            colors: '#2563eb',
                                            fontSize: '11px'
                                        },
                                        formatter: value => Math.round(value) + '%'
                                    }
                                },
            
                                {
                                    seriesName: valueSeriesLabel,
            
                                    opposite: true,
                                    min: 0,
                                    forceNiceScale: true,
                                    labels: {
                                        minWidth: 48,
                                        maxWidth: 62,
                                        style: {
                                            colors: '#d97706',
                                            fontSize: '11px'
                                        },
                                        formatter: money
                                    }
                                }
                            ],
            
                            tooltip: {
                                shared: true,
                                intersect: false,
            
                                y: [{
                                        formatter: value =>
                                            Number(value).toFixed(1) + '%'
                                    },
                                    {
                                        formatter: money
                                    }
                                ]
                            },
            
                            legend: {
                                onItemClick: {
                                    toggleDataSeries: true
                                },
                                onItemHover: {
                                    highlightDataSeries: true
                                },
                                position: 'bottom',
                                horizontalAlign: 'center',
                                fontSize: '12px',
                                markers: {
                                    size: 5
                                },
                                itemMargin: {
                                    horizontal: 10,
                                    vertical: 2
                                }
                            },
            
                            grid: {
                                borderColor: '#e2e8f0',
                                strokeDashArray: 3,
                                padding: {
                                    left: 14,
                                    right: 14
                                }
                            },

                            responsive: [{
                                breakpoint: 640,
                                options: {
                                    xaxis: {
                                        labels: {
                                            rotate: -45,
                                            maxHeight: 60
                                        }
                                    },
                                    yaxis: [{
                                        labels: {
                                            show: true,
                                            minWidth: 30,
                                            maxWidth: 36
                                        }
                                    }, {
                                        labels: {
                                            show: false
                                        }
                                    }]
                                }
                            }]
                        }
                    );

                    this.chart.render();
                },

                destroy() {
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }
                }
            }" x-init="init()" x-on:livewire:navigating.window="destroy()" class="h-full w-full">
            <div x-ref="chart" class="h-full w-full"></div>
        </div>

        <x-slot:footer>
            Line: cumulative projects as percentage of total projects.
            Bars: {{ strtolower($valueSeriesLabel) }} by {{ $chartDateLabel }}.
        </x-slot:footer>
    </x-dashboard-chart-card>
@endif
