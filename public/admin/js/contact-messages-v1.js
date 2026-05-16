(function () {
    'use strict';

    function initDataTables() {
        if (!window.jQuery || !jQuery.fn.DataTable) return;
        ['#contactMessagesTable', '#contactReasonsTable'].forEach(function (selector) {
            var table = jQuery(selector);
            if (table.length && !jQuery.fn.DataTable.isDataTable(selector)) {
                table.DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [],
                    language: {
                        search: 'Tafuta:',
                        lengthMenu: 'Onyesha _MENU_ rekodi',
                        info: 'Inaonyesha _START_ - _END_ / _TOTAL_',
                        infoEmpty: 'Hakuna rekodi',
                        zeroRecords: 'Hakuna rekodi zilizopatikana',
                        paginate: { previous: 'Iliyotangulia', next: 'Inayofuata' }
                    }
                });
            }
        });
    }

    function initDeleteConfirm() {
        document.querySelectorAll('.js-confirm-delete').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.Swal) return;
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Una uhakika?',
                    text: 'Kitendo hiki hakiwezi kurejeshwa kirahisi.',
                    showCancelButton: true,
                    confirmButtonText: 'Ndiyo, futa',
                    cancelButtonText: 'Ghairi',
                    confirmButtonColor: '#ef4444'
                }).then(function (result) {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    }

    function initCharts() {
        if (!window.Chart || !window.contactMessagesCharts) return;
        var monthly = window.contactMessagesCharts.monthly || { labels: [], values: [] };
        var status = window.contactMessagesCharts.status || { labels: [], values: [] };

        var trendEl = document.getElementById('contactMessagesTrendChart');
        if (trendEl) {
            new Chart(trendEl, {
                type: 'line',
                data: {
                    labels: monthly.labels || [],
                    datasets: [{
                        label: 'Ujumbe',
                        data: monthly.values || [],
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, .12)',
                        tension: .35,
                        fill: true,
                        borderWidth: 3
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        }

        var statusEl = document.getElementById('contactMessagesStatusChart');
        if (statusEl) {
            new Chart(statusEl, {
                type: 'doughnut',
                data: {
                    labels: status.labels || [],
                    datasets: [{
                        data: status.values || [],
                        backgroundColor: ['#7c3aed', '#0ea5e9', '#10b981', '#64748b'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initDataTables();
        initDeleteConfirm();
        initCharts();
    });
})();
