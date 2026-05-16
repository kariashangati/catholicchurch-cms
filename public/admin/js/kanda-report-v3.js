document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
    Chart.defaults.color = '#475569';

    const commonScales = {
        y: { beginAtZero: true, grid: { color: '#eef2f7', drawBorder: false }, ticks: { color: '#64748b', padding: 10 } },
        x: { grid: { display: false, drawBorder: false }, ticks: { color: '#64748b', padding: 8 } }
    };

    const palette = [
        '#16a34a', '#7c3aed', '#f59e0b', '#0284c7', '#dc2626',
        '#0f172a', '#14b8a6', '#a855f7', '#84cc16', '#ea580c', '#64748b'
    ];

    function hexToRgba(hex, alpha) {
        const clean = hex.replace('#', '');
        const bigint = parseInt(clean, 16);
        const r = (bigint >> 16) & 255;
        const g = (bigint >> 8) & 255;
        const b = bigint & 255;
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    function buildContributionBarDatasets(items, startIndex = 2) {
        return (items || []).map(function (item, index) {
            const color = palette[(startIndex + index) % palette.length];
            return {
                label: item.label,
                data: item.data,
                backgroundColor: hexToRgba(color, 0.85),
                borderRadius: 12,
                borderSkipped: false,
                maxBarThickness: 18
            };
        });
    }

    function buildContributionLineDatasets(items, startIndex = 2) {
        return (items || []).map(function (item, index) {
            const color = palette[(startIndex + index) % palette.length];
            return {
                label: item.label,
                data: item.data,
                borderColor: color,
                backgroundColor: hexToRgba(color, 0.08),
                fill: true,
                tension: 0.35,
                borderWidth: 3,
                pointRadius: 0,
                pointHoverRadius: 5
            };
        });
    }

    function buildIndexCharts() {
        if (typeof window.kandaReportV3Index === 'undefined') return;
        const data = window.kandaReportV3Index;

        const financeEl = document.getElementById('kandaReportFinanceChart');
        if (financeEl) {
            new Chart(financeEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.chartData.labels,
                    datasets: [
                        { label: data.labels.tithes, data: data.chartData.tithes, backgroundColor: hexToRgba(palette[0], 0.85), borderRadius: 12, borderSkipped: false, maxBarThickness: 18 },
                        { label: data.labels.offerings, data: data.chartData.offerings, backgroundColor: hexToRgba(palette[1], 0.85), borderRadius: 12, borderSkipped: false, maxBarThickness: 18 },
                        ...buildContributionBarDatasets(data.chartData.contribution_types, 2),
                        { label: data.labels.grandTotal, data: data.chartData.grand_total, backgroundColor: hexToRgba(palette[5], 0.75), borderRadius: 12, borderSkipped: false, maxBarThickness: 18 }
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

        const compositionEl = document.getElementById('kandaReportCompositionChart');
        if (compositionEl) {
            new Chart(compositionEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: data.compositionChart.labels,
                    datasets: [{
                        data: data.compositionChart.values,
                        backgroundColor: data.compositionChart.values.map(function (_, index) { return palette[index % palette.length]; }),
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10, padding: 18 } } }
                }
            });
        }
    }

    function buildShowCharts() {
        if (typeof window.kandaReportV3Show === 'undefined') return;
        const data = window.kandaReportV3Show;

        const monthlyEl = document.getElementById('kandaMonthlyFinanceTrend');
        if (monthlyEl) {
            new Chart(monthlyEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.monthlyTrends.labels,
                    datasets: [
                        { label: data.labels.tithes, data: data.monthlyTrends.tithes, borderColor: palette[0], backgroundColor: hexToRgba(palette[0], 0.08), fill: true, tension: 0.35, borderWidth: 3, pointRadius: 0, pointHoverRadius: 5 },
                        { label: data.labels.offerings, data: data.monthlyTrends.offerings, borderColor: palette[1], backgroundColor: hexToRgba(palette[1], 0.08), fill: true, tension: 0.35, borderWidth: 3, pointRadius: 0, pointHoverRadius: 5 },
                        ...buildContributionLineDatasets(data.monthlyTrends.contribution_types, 2)
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 10, padding: 18 } } },
                    scales: commonScales
                }
            });
        }

        const compositionEl = document.getElementById('kandaShowCompositionChart');
        if (compositionEl) {
            new Chart(compositionEl.getContext('2d'), {
                type: 'polarArea',
                data: {
                    labels: data.compositionChart.labels,
                    datasets: [{
                        data: data.compositionChart.values,
                        backgroundColor: data.compositionChart.values.map(function (_, index) { return hexToRgba(palette[index % palette.length], 0.82); }),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { r: { grid: { color: '#e5edf6' }, ticks: { display: false } } },
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10, padding: 18 } } }
                }
            });
        }
    }

    buildIndexCharts();
    buildShowCharts();
});
