document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined' || typeof window.kandaDashboardData === 'undefined') {
        return;
    }

    const data = window.kandaDashboardData;
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

    const monthlyEl = document.getElementById('kandaMonthlyFinanceChart');
    if (monthlyEl) {
        const ctx = monthlyEl.getContext('2d');
        const tithesGradient = createGradient(ctx, 'rgba(22, 163, 74, 0.20)', 'rgba(22, 163, 74, 0.02)');
        const offeringsGradient = createGradient(ctx, 'rgba(124, 58, 237, 0.20)', 'rgba(124, 58, 237, 0.02)');
        const cashGradient = createGradient(ctx, 'rgba(245, 158, 11, 0.20)', 'rgba(245, 158, 11, 0.02)');
        const bankGradient = createGradient(ctx, 'rgba(2, 132, 199, 0.20)', 'rgba(2, 132, 199, 0.02)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.monthlyFinanceChart.labels,
                datasets: [
                    {
                        label: data.labels.tithes,
                        data: data.monthlyFinanceChart.tithes,
                        borderColor: '#16a34a',
                        backgroundColor: tithesGradient,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 5
                    },
                    {
                        label: data.labels.offerings,
                        data: data.monthlyFinanceChart.offerings,
                        borderColor: '#7c3aed',
                        backgroundColor: offeringsGradient,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 5
                    },
                    {
                        label: data.labels.cash,
                        data: data.monthlyFinanceChart.cash,
                        borderColor: '#f59e0b',
                        backgroundColor: cashGradient,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 5
                    },
                    {
                        label: data.labels.bank,
                        data: data.monthlyFinanceChart.bank,
                        borderColor: '#0284c7',
                        backgroundColor: bankGradient,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
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

    const financeEl = document.getElementById('kandaFinanceChart');
    if (financeEl) {
        new Chart(financeEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.financeByKandaChart.labels,
                datasets: [
                    {
                        label: data.labels.tithes,
                        data: data.financeByKandaChart.tithes,
                        backgroundColor: 'rgba(22, 163, 74, 0.85)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 18
                    },
                    {
                        label: data.labels.offerings,
                        data: data.financeByKandaChart.offerings,
                        backgroundColor: 'rgba(124, 58, 237, 0.85)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 18
                    },
                    {
                        label: data.labels.cash,
                        data: data.financeByKandaChart.cash,
                        backgroundColor: 'rgba(245, 158, 11, 0.85)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 18
                    },
                    {
                        label: data.labels.bank,
                        data: data.financeByKandaChart.bank,
                        backgroundColor: 'rgba(2, 132, 199, 0.85)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 18
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

    const membersEl = document.getElementById('kandaMembersChart');
    if (membersEl) {
        new Chart(membersEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.chartData.labels,
                datasets: [
                    {
                        label: data.labels.members,
                        data: data.chartData.members,
                        backgroundColor: 'rgba(124, 58, 237, 0.85)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 20
                    },
                    {
                        label: data.labels.familias,
                        data: data.chartData.familias,
                        backgroundColor: 'rgba(22, 163, 74, 0.85)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 20
                    },
                    {
                        label: data.labels.jumuiyas,
                        data: data.chartData.jumuiyas,
                        backgroundColor: 'rgba(2, 132, 199, 0.85)',
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

    const contributionTypeEl = document.getElementById('kandaContributionTypeChart');
    if (contributionTypeEl && data.contributionTypeChart) {
        new Chart(contributionTypeEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.contributionTypeChart.labels,
                datasets: [
                    {
                        label: data.labels.cash,
                        data: data.contributionTypeChart.cash,
                        backgroundColor: 'rgba(245, 158, 11, 0.85)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 26
                    },
                    {
                        label: data.labels.bank,
                        data: data.contributionTypeChart.bank,
                        backgroundColor: 'rgba(2, 132, 199, 0.85)',
                        borderRadius: 12,
                        borderSkipped: false,
                        maxBarThickness: 26
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

    const mixEl = document.getElementById('kandaFinanceMixChart');
    if (mixEl) {
        new Chart(mixEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: data.financeMixChart.labels,
                datasets: [{
                    data: data.financeMixChart.values,
                    backgroundColor: [
                        '#16a34a',
                        '#7c3aed',
                        '#f59e0b',
                        '#0284c7'
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
});