@props(['filename', 'title'])

<button type="button"
    data-excel-filename="{{ $filename }}"
    data-chart-title="{{ __($title) }}"
    data-export-url="{{ route('charts.export-excel') }}"
    onclick="event.preventDefault(); event.stopPropagation(); window.downloadDashboardChartExcel(this)"
    class="inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-emerald-600 bg-emerald-600 text-white shadow-md transition duration-150 hover:-translate-y-px hover:border-emerald-700 hover:bg-emerald-700 hover:shadow-lg active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-50"
    title="Export chart data to Excel"
    aria-label="Export chart data to Excel">
    <svg class="pointer-events-none h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M7 3.75h7.5L19.5 8.7v11.55H7V3.75Zm7.5 0V9h5M9.5 12l4 5m0-5-4 5" />
    </svg>
</button>

@once
    <script>
        window.downloadDashboardChartExcel = async function (button) {
            const card = button.closest('[data-chart-card]');
            const chartRoot = card?.querySelector('[x-data]');
            const chart = chartRoot && window.Alpine ? Alpine.$data(chartRoot)?.chart : null;

            if (!chart?.w?.config) {
                window.alert('The chart is not ready yet. Please try again.');
                return;
            }

            const config = chart.w.config;
            const labels = config.xaxis?.categories?.length
                ? config.xaxis.categories
                : (config.labels || []);
            const rawSeries = Array.isArray(config.series) ? config.series : [];
            const series = rawSeries.map((item, index) => {
                if (typeof item === 'number') {
                    return { name: config.labels?.[index] || `Series ${index + 1}`, data: [item] };
                }
                return {
                    name: item.name || `Series ${index + 1}`,
                    data: Array.isArray(item.data) ? item.data : [],
                };
            });

            let rows;
            if (rawSeries.length && typeof rawSeries[0] === 'number') {
                rows = [['Category', 'Value'], ...rawSeries.map((value, index) => [labels[index] || `Item ${index + 1}`, value])];
            } else {
                const length = Math.max(labels.length, ...series.map(item => item.data.length), 0);
                rows = [
                    ['Category', ...series.map(item => item.name)],
                    ...Array.from({ length }, (_, index) => [
                        labels[index] || `Item ${index + 1}`,
                        ...series.map(item => {
                            const point = item.data[index];
                            return typeof point === 'object' && point !== null ? (point.y ?? point.x ?? '') : (point ?? '');
                        }),
                    ]),
                ];
            }

            if (rows.length < 2) {
                window.alert('There is no chart data to export.');
                return;
            }

            button.disabled = true;

            try {
                const image = await chart.dataURI({ scale: 2 });
                const title = button.dataset.chartTitle || 'Chart data';
                const response = await fetch(button.dataset.exportUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        title,
                        filename: button.dataset.excelFilename || 'dashboard-chart',
                        image: image.imgURI,
                        rows,
                    }),
                });
                if (!response.ok) {
                    throw new Error(`Export failed with status ${response.status}`);
                }
                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `${button.dataset.excelFilename || 'dashboard-chart'}-chart-and-data.xlsx`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
            } catch (error) {
                console.error('The chart and its data could not be exported.', error);
                window.alert('The chart and its data could not be exported. Please try again.');
            } finally {
                button.disabled = false;
            }
        };
    </script>
@endonce
