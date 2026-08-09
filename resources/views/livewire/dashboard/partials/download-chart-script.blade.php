<script>
    window.downloadDashboardChart = async function(button) {
        const card = button.closest('[data-chart-card]');
        const chartRoot = card?.querySelector('[x-data]');
        const filename = button.dataset.downloadFilename || 'dashboard-chart';

        const chart = chartRoot && window.Alpine
            ? Alpine.$data(chartRoot).chart
            : null;

        if (!chart || typeof chart.dataURI !== 'function') {
            window.alert('La gráfica todavía no está lista. Inténtalo nuevamente.');
            return;
        }

        button.disabled = true;
        button.style.opacity = '0.5';

        try {
            const image = await chart.dataURI({
                scale: 2
            });

            const link = document.createElement('a');

            link.download = filename + '.png';
            link.href = image.imgURI;

            document.body.appendChild(link);
            link.click();
            link.remove();
        } catch (error) {
            console.error('No se pudo descargar la gráfica.', error);
            window.alert('No se pudo descargar la gráfica. Inténtalo nuevamente.');
        } finally {
            button.disabled = false;
            button.style.opacity = '1';
        }
    };
</script>
