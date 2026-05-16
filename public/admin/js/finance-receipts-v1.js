document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('receiptPreviewModal');
    const hasBootstrapModal = modalEl && window.bootstrap && typeof window.bootstrap.Modal === 'function';
    const previewModal = hasBootstrapModal ? new window.bootstrap.Modal(modalEl) : null;

    const formatField = (key, value) => {
        if (value === null || value === undefined || value === '') return '—';
        if (key === 'amount') {
            const number = Number(value);
            return Number.isNaN(number) ? value : number.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' TZS';
        }
        if (key === 'receipt_type' || key === 'source_type') {
            return String(value).replaceAll('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
        }
        return value;
    };

    document.querySelectorAll('.js-receipt-preview').forEach((button) => {
        button.addEventListener('click', () => {
            let payload = {};
            try {
                payload = JSON.parse(button.dataset.receiptPayload || '{}');
            } catch (error) {
                console.error('Failed to parse receipt payload.', error);
            }

            if (!modalEl) return;

            modalEl.querySelectorAll('[data-preview]').forEach((node) => {
                const key = node.getAttribute('data-preview');
                node.textContent = formatField(key, payload[key]);
            });

            if (previewModal) {
                previewModal.show();
            }
        });
    });

    const filterForm = document.querySelector('.receipt-filters-form');
    if (filterForm) {
        filterForm.querySelectorAll('select, input').forEach((field) => {
            field.addEventListener('change', () => {
                field.classList.add('is-touched');
            });
        });
    }
});
