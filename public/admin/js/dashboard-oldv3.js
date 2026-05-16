document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined' || typeof window.dashboardChartsData === 'undefined') {
        return;
    }

    const data = window.dashboardChartsData || {};
    const chartLinks = data.chartLinks || {};
    const labels = data.labels || {};

    const baseGrid = '#eef2f7';
    const baseTick = '#64748b';

    Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
    Chart.defaults.color = '#475569';

    function createGradient(ctx, colorA, colorB) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, colorA);
        gradient.addColorStop(1, colorB);
        return gradient;
    }

    function openChartLink(linkItem) {
        if (!linkItem || !linkItem.can_open || !linkItem.url) {
            return;
        }

        window.location.href = linkItem.url;
    }

    function getClickableCursorHandler(chartKey) {
        return {
            id: `${chartKey}CursorHandler`,
            afterEvent(chart, args) {
                const event = args.event;
                if (!event) {
                    return;
                }

                const points = chart.getElementsAtEventForMode(
                    event,
                    'nearest',
                    { intersect: true },
                    false
                );

                const hasClickableTarget =
                    points.length > 0 &&
                    Array.isArray(chartLinks[chartKey]) &&
                    !!chartLinks[chartKey][points[0].index] &&
                    !!chartLinks[chartKey][points[0].index].can_open;

                chart.canvas.style.cursor = hasClickableTarget ? 'pointer' : 'default';
            }
        };
    }

    const commonScales = {
        y: {
            beginAtZero: true,
            grid: {
                color: baseGrid,
                drawBorder: false
            },
            ticks: {
                color: baseTick,
                padding: 10
            }
        },
        x: {
            grid: {
                display: false,
                drawBorder: false
            },
            ticks: {
                color: baseTick,
                padding: 8
            }
        }
    };

    const commonLineOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    boxWidth: 10,
                    padding: 18
                }
            }
        },
        scales: commonScales
    };

    const memberChartEl = document.getElementById('memberChart');
    if (memberChartEl && data.memberChart) {
        const ctx = memberChartEl.getContext('2d');
        const memberGradient = createGradient(ctx, 'rgba(124, 58, 237, 0.22)', 'rgba(124, 58, 237, 0.02)');
        const familiaGradient = createGradient(ctx, 'rgba(22, 163, 74, 0.16)', 'rgba(22, 163, 74, 0.02)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.memberChart.labels || [],
                datasets: [
                    {
                        label: labels.members || 'Members',
                        data: data.memberChart.members || [],
                        borderColor: '#7c3aed',
                        backgroundColor: memberGradient,
                        fill: true,
                        tension: 0.38,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#7c3aed'
                    },
                    {
                        label: labels.familias || 'Familias',
                        data: data.memberChart.familias || [],
                        borderColor: '#16a34a',
                        backgroundColor: familiaGradient,
                        fill: true,
                        tension: 0.38,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#16a34a'
                    }
                ]
            },
            options: commonLineOptions
        });
    }

    const financeChartEl = document.getElementById('financeChart');
    if (financeChartEl && data.financeChart) {
        new Chart(financeChartEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.financeChart.labels || [],
                datasets: [
                    {
                        label: labels.offerings || 'Offerings',
                        data: data.financeChart.offerings || [],
                        backgroundColor: 'rgba(124, 58, 237, 0.85)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 20
                    },
                    {
                        label: labels.tithes || 'Tithes',
                        data: data.financeChart.tithes || [],
                        backgroundColor: 'rgba(22, 163, 74, 0.85)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 20
                    },
                    {
                        label: labels.contributions || 'Contributions',
                        data: data.financeChart.contributions || [],
                        backgroundColor: 'rgba(245, 158, 11, 0.85)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 20
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 18
                        }
                    }
                },
                scales: commonScales
            }
        });
    }

    const visitorChartEl = document.getElementById('visitorChart');
    if (visitorChartEl && data.visitorChart) {
        const ctx = visitorChartEl.getContext('2d');
        const visitorGradient = createGradient(ctx, 'rgba(14, 165, 233, 0.22)', 'rgba(14, 165, 233, 0.02)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.visitorChart.labels || [],
                datasets: [
                    {
                        label: labels.visitors || 'Visitors',
                        data: data.visitorChart.values || [],
                        borderColor: '#0284c7',
                        backgroundColor: visitorGradient,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#0284c7'
                    }
                ]
            },
            options: commonLineOptions
        });
    }

    const sacramentChartEl = document.getElementById('sacramentChart');
    if (sacramentChartEl && data.sacramentChart) {
        new Chart(sacramentChartEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.sacramentChart.labels || [],
                datasets: [{
                    data: data.sacramentChart.values || [],
                    backgroundColor: [
                        '#7c3aed',
                        '#16a34a',
                        '#0284c7',
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 18
                        }
                    }
                }
            }
        });
    }

    const mixChartEl = document.getElementById('mixChart');
    if (mixChartEl && data.mixChart) {
        new Chart(mixChartEl.getContext('2d'), {
            type: 'polarArea',
            data: {
                labels: data.mixChart.labels || [],
                datasets: [{
                    data: data.mixChart.values || [],
                    backgroundColor: [
                        'rgba(124, 58, 237, 0.82)',
                        'rgba(22, 163, 74, 0.82)',
                        'rgba(245, 158, 11, 0.82)',
                        'rgba(2, 132, 199, 0.82)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick(event, elements) {
                    if (!elements.length) {
                        return;
                    }

                    const index = elements[0].index;
                    const linkItem = Array.isArray(chartLinks.mixChart) ? chartLinks.mixChart[index] : null;
                    openChartLink(linkItem);
                },
                scales: {
                    r: {
                        grid: {
                            color: '#e5edf6'
                        },
                        ticks: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 18
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                const label = context.label || '';
                                const value = context.raw ?? 0;
                                return `${label}: ${Number(value).toLocaleString()}`;
                            }
                        }
                    }
                }
            },
            plugins: [getClickableCursorHandler('mixChart')]
        });
    }
});