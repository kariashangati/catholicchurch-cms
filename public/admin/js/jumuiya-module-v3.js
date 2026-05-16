window.ChurchCMSJumuiyaModule = (function () {
    function createChart(canvasId, type, data, datasetOverrides = {}) {
        const element = document.getElementById(canvasId);
        if (!element || typeof Chart === 'undefined') return;

        const mergedData = {
            ...data,
            datasets: (data.datasets || []).map((dataset) => ({
                borderRadius: 10,
                maxBarThickness: 32,
                tension: 0.25,
                fill: type === 'line' ? false : dataset.fill,
                ...datasetOverrides,
                ...dataset,
            })),
        };

        new Chart(element.getContext('2d'), {
            type,
            data: mergedData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
                scales: type === 'doughnut' || type === 'pie' ? {} : {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                        },
                    },
                },
            },
        });
    }

    function initTable(tableId) {
        const wrapper = document.querySelector(`[data-table-wrapper="${tableId}"]`);
        if (!wrapper) return;

        const table = wrapper.querySelector('[data-table]');
        const tbody = table ? table.querySelector('tbody') : null;
        if (!table || !tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        if (!rows.length) return;

        const searchInput = document.querySelector(`[data-table-search="${tableId}"]`);
        const pageSizeSelect = document.querySelector(`[data-table-page-size="${tableId}"]`);
        const info = document.querySelector(`[data-table-info="${tableId}"]`);
        const summary = document.querySelector(`[data-table-summary="${tableId}"]`);
        const pagination = document.querySelector(`[data-table-pagination="${tableId}"]`);

        let currentPage = 1;
        let pageSize = parseInt(pageSizeSelect?.value || '10', 10);
        let filteredRows = rows;

        function filterRows() {
            const query = (searchInput?.value || '').trim().toLowerCase();
            filteredRows = rows.filter((row) => row.innerText.toLowerCase().includes(query));
            currentPage = 1;
            render();
        }

        function render() {
            const total = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(total / pageSize));
            currentPage = Math.min(currentPage, totalPages);
            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;

            rows.forEach((row) => {
                row.style.display = 'none';
            });

            filteredRows.slice(start, end).forEach((row) => {
                row.style.display = '';
            });

            const from = total === 0 ? 0 : start + 1;
            const to = Math.min(end, total);

            if (info) info.textContent = `Showing ${from}-${to} of ${total}`;
            if (summary) summary.textContent = `Page ${currentPage} of ${totalPages}`;

            if (pagination) {
                pagination.innerHTML = '';

                const prev = document.createElement('button');
                prev.type = 'button';
                prev.className = 'btn btn-sm btn-outline-secondary';
                prev.textContent = 'Prev';
                prev.disabled = currentPage === 1;
                prev.addEventListener('click', () => {
                    currentPage -= 1;
                    render();
                });
                pagination.appendChild(prev);

                const next = document.createElement('button');
                next.type = 'button';
                next.className = 'btn btn-sm btn-outline-secondary';
                next.textContent = 'Next';
                next.disabled = currentPage >= totalPages;
                next.addEventListener('click', () => {
                    currentPage += 1;
                    render();
                });
                pagination.appendChild(next);
            }
        }

        searchInput?.addEventListener('input', filterRows);
        pageSizeSelect?.addEventListener('change', (event) => {
            pageSize = parseInt(event.target.value || '10', 10);
            currentPage = 1;
            render();
        });

        render();
    }

    function initEditModal({ triggerSelector, modalId, formId }) {
        const modalElement = document.getElementById(modalId);
        const form = document.getElementById(formId);
        if (!modalElement || !form) return;

        const bootstrapModal = typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalElement) : null;

        document.querySelectorAll(triggerSelector).forEach((button) => {
            button.addEventListener('click', async () => {
                const url = button.getAttribute('data-url');
                const updateUrl = button.getAttribute('data-update-url');
                if (!url || !updateUrl) return;

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                    });

                    if (!response.ok) throw new Error('Unable to load record');

                    const payload = await response.json();
                    form.setAttribute('action', updateUrl);
                    form.querySelector('[name="name"]').value = payload.name || '';
                    form.querySelector('[name="kanda_id"]').value = payload.kanda_id || '';
                    form.querySelector('[name="comment"]').value = payload.comment || '';
                    form.querySelector('[name="is_active"]').checked = !!payload.is_active;

                    const previewWrapper = form.querySelector('[data-image-preview-wrapper]');
                    const preview = form.querySelector('[data-image-preview]');
                    if (preview && previewWrapper) {
                        if (payload.image_url) {
                            preview.src = payload.image_url;
                            previewWrapper.classList.remove('d-none');
                        } else {
                            preview.src = '';
                            previewWrapper.classList.add('d-none');
                        }
                    }

                    bootstrapModal?.show();
                } catch (error) {
                    console.error(error);
                    window.alert('Failed to load jumuiya details for editing.');
                }
            });
        });
    }

    function initImageInputs() {
        document.querySelectorAll('input[type="file"][name="image"]').forEach((input) => {
            input.addEventListener('change', (event) => {
                const file = event.target.files?.[0];
                const form = event.target.closest('form');
                const previewWrapper = form?.querySelector('[data-image-preview-wrapper]');
                const preview = form?.querySelector('[data-image-preview]');
                if (!file || !previewWrapper || !preview) return;

                const reader = new FileReader();
                reader.onload = function (readerEvent) {
                    preview.src = readerEvent.target?.result;
                    previewWrapper.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });
        });
    }

    return {
        renderChart: createChart,
        initTable,
        initEditModal,
        initImageInputs,
    };
})();
