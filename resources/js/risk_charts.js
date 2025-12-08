window.renderCharts = function (panels) {

    console.log("Rendering charts...");
    console.log(panels);

    panels.forEach(panel => {

        if (!panel.chartData || panel.chartData.length === 0) {
            return;
        }

        const canvasId = "chart_" + panel.variable.id;
        const ctx = document.getElementById(canvasId);

        if (!ctx) return;

        const labels = panel.chartData.map(d => d.month);
        const values = panel.chartData.map(d => d.value);

        new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [{
                    label: panel.variable.name,
                    data: values,
                    borderColor: "#0d6efd",
                    tension: 0.3
                }]
            },
        });

    });
};
