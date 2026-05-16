(function () {
    'use strict';

    const root = document;

    function bindBulkToggle() {
        const master = root.querySelector('[data-bulk-master]');
        if (!master) return;

        master.addEventListener('change', function () {
            root.querySelectorAll('[data-bulk-item]').forEach((checkbox) => {
                checkbox.checked = master.checked;
            });
            refreshBulkCount();
        });

        root.querySelectorAll('[data-bulk-item]').forEach((checkbox) => {
            checkbox.addEventListener('change', refreshBulkCount);
        });
    }

    function refreshBulkCount() {
        const count = root.querySelectorAll('[data-bulk-item]:checked').length;
        root.querySelectorAll('[data-bulk-count]').forEach((node) => {
            node.textContent = count;
        });
    }

    function bindConfirmations() {
        root.querySelectorAll('[data-confirm-message]').forEach((el) => {
            el.addEventListener('click', function (event) {
                const message = el.getAttribute('data-confirm-message') || 'Are you sure?';
                if (!window.confirm(message)) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });
        });
    }

    function bindFilterAutoSubmit() {
        root.querySelectorAll('[data-auto-submit-form]').forEach((form) => {
            form.querySelectorAll('select[data-auto-submit], input[data-auto-submit]').forEach((field) => {
                field.addEventListener('change', () => form.submit());
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindBulkToggle();
        bindConfirmations();
        bindFilterAutoSubmit();
        refreshBulkCount();
    });
})();
