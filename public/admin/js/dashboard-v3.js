document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined' || typeof window.dashboardChartsData === 'undefined') {
        return;
    }

    const data = window.dashboardChartsData || {};
    const labels = data.labels || {};
    const chartLinks = data.chartLinks || {};
    const grid = '#eef2f7';
    const tick = '#64748b';

    Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
    Chart.defaults.color = '#475569';

    const commonScales = {
        y: { beginAtZero: true, grid: { color: grid, drawBorder: false }, ticks: { color: tick, padding: 10 } },
        x: { grid: { display: false, drawBorder: false }, ticks: { color: tick, padding: 8 } }
    };

    const financeChartEl = document.getElementById('financeChart');
    if (financeChartEl && data.financeChart) {
        new Chart(financeChartEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.financeChart.labels || [],
                datasets: [
                    { label: labels.offerings || 'Offerings', data: data.financeChart.offerings || [], backgroundColor: 'rgba(124, 58, 237, 0.85)', borderRadius: 12, borderSkipped: false, maxBarThickness: 22 },
                    { label: labels.tithes || 'Tithes', data: data.financeChart.tithes || [], backgroundColor: 'rgba(22, 163, 74, 0.85)', borderRadius: 12, borderSkipped: false, maxBarThickness: 22 },
                    { label: labels.contributions || 'Contributions', data: data.financeChart.contributions || [], backgroundColor: 'rgba(245, 158, 11, 0.85)', borderRadius: 12, borderSkipped: false, maxBarThickness: 22 },
                    { label: labels.projects || 'Projects', data: data.financeChart.projects || [], backgroundColor: 'rgba(2, 132, 199, 0.85)', borderRadius: 12, borderSkipped: false, maxBarThickness: 22 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 10, padding: 18 } } },
                scales: commonScales
            }
        });
    }

    const kandaChartEl = document.getElementById('kandaDistributionChart');
    if (kandaChartEl && data.kandaDistributionChart) {
        const link = chartLinks.kandaDistributionChart || {};
        const chart = new Chart(kandaChartEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.kandaDistributionChart.labels || [],
                datasets: [{
                    label: labels.members || 'Members',
                    data: data.kandaDistributionChart.values || [],
                    backgroundColor: [
                        'rgba(124, 58, 237, 0.85)',
                        'rgba(22, 163, 74, 0.85)',
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(2, 132, 199, 0.85)',
                        'rgba(239, 68, 68, 0.85)',
                        'rgba(71, 85, 105, 0.85)'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '64%',
                onClick() {
                    if (link.can_open && link.url) {
                        window.location.href = link.url;
                    }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10, padding: 18 } },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                const row = (data.kandaDistributionChart.rows || [])[context.dataIndex] || {};
                                return `${context.label}: ${Number(context.raw || 0).toLocaleString()} ${labels.members || 'Members'}, ${Number(row.familias || 0).toLocaleString()} ${labels.families || 'Families'}, ${Number(row.jumuiyas || 0).toLocaleString()} ${labels.jumuiyas || 'Jumuiyas'}`;
                            }
                        }
                    }
                }
            }
        });

        if (link.can_open) {
            chart.canvas.style.cursor = 'pointer';
        }
    }
});
