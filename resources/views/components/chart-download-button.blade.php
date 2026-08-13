@props(['filename'])

<button type="button"
    data-download-filename="{{ $filename }}"
    onclick="event.preventDefault(); event.stopPropagation(); window.downloadDashboardChart(this)"
    class="chart-image-export inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg border text-white shadow-md transition duration-150 hover:-translate-y-px hover:brightness-95 hover:text-white hover:shadow-lg active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-50"
    title="Download chart as PNG"
    aria-label="Download chart as PNG">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
        class="pointer-events-none h-[18px] w-[18px]"
        viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 10.5 12 15m0 0 4.5-4.5M12 15V3" />
    </svg>
</button>

@once
    <script>
        window.downloadDashboardChart = async function (button) {
            const card = button.closest('[data-chart-card]');
            const chartRoot = card?.querySelector('[x-data]');
            const filename = button.dataset.downloadFilename || 'dashboard-chart';
            const chart = chartRoot && window.Alpine
                ? Alpine.$data(chartRoot)?.chart
                : null;

            if (!chart || typeof chart.dataURI !== 'function') {
                window.alert('The chart is not ready yet. Please try again.');
                return;
            }

            button.disabled = true;

            try {
                const image = await chart.dataURI({ scale: 2 });
                const link = document.createElement('a');
                link.download = filename + '.png';
                link.href = image.imgURI;
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (error) {
                console.error('The chart could not be downloaded.', error);
                window.alert('The chart could not be downloaded. Please try again.');
            } finally {
                button.disabled = false;
            }
        };
    </script>
@endonce
