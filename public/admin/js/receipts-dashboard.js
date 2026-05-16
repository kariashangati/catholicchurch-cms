(function () {
    'use strict';

    function onReady(callback) {
        if (document.readyState !== 'loading') {
            callback();
            return;
        }
        document.addEventListener('DOMContentLoaded', callback);
    }

    function parseChartData(elementId) {
        const el = document.getElementById(elementId);
        if (!el) return [];

        try {
            return JSON.parse(el.dataset.chart || '[]');
        } catch (error) {
            console.warn('Unable to parse chart data for:', elementId, error);
            return [];
        }
    }

    function buildChart(type, elementId, label, points) {
        const el = document.getElementById(elementId);
        if (!el || typeof Chart === 'undefined') return;

        const labels = points.map(item => item.label);
        const values = points.map(item => item.total);

        new Chart(el, {
            type,
            data: {
                labels,
                datasets: [{
                    label,
                    data: values,
                    borderWidth: 2,
                    tension: 0.35,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: type === 'bar' ? {
                    y: {
                        beginAtZero: true
                    }
                } : {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    onReady(function () {
        buildChart('line', 'receiptsIssuedTrendChart', 'Issued Trend', parseChartData('receiptsIssuedTrendChart'));
        buildChart('bar', 'receiptsStatusBreakdownChart', 'Status Breakdown', parseChartData('receiptsStatusBreakdownChart'));
        buildChart('bar', 'receiptsSourceBreakdownChart', 'Source Breakdown', parseChartData('receiptsSourceBreakdownChart'));
        buildChart('line', 'receiptsPendingTrendChart', 'Pending Trend', parseChartData('receiptsPendingTrendChart'));
    });
})();
