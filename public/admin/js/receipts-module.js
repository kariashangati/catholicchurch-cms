(function () {
    'use strict';

    const selectors = {
        generateForm: '#receiptGenerateForm',
        previewForm: '#receiptPreviewForm',
        filterForm: '#receiptFilterForm',
        autoSubmit: '[data-auto-submit="true"]',
        resetFilters: '[data-reset-receipt-filters]',
        quickAction: '[data-receipt-action]',
        sendSmsButton: '[data-receipt-send-sms]',
        resendSmsButton: '[data-receipt-resend-sms]',
        reprintButton: '[data-receipt-reprint]'
    };

    function onReady(callback) {
        if (document.readyState !== 'loading') {
            callback();
            return;
        }
        document.addEventListener('DOMContentLoaded', callback);
    }

    function persistFilterState(form) {
        if (!form) return;
        const key = 'receipts.filter.state';
        form.addEventListener('change', function () {
            const payload = {};
            new FormData(form).forEach((value, field) => {
                payload[field] = value;
            });
            localStorage.setItem(key, JSON.stringify(payload));
        });
    }

    function restoreFilterState(form) {
        if (!form) return;
        const key = 'receipts.filter.state';
        const saved = localStorage.getItem(key);
        if (!saved) return;

        try {
            const state = JSON.parse(saved);
            Object.keys(state).forEach((field) => {
                const el = form.querySelector(`[name="${field}"]`);
                if (!el) return;

                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = el.value === state[field] || state[field] === true;
                } else {
                    el.value = state[field];
                }
            });
        } catch (error) {
            console.warn('Unable to restore receipt filter state.', error);
        }
    }

    function bindAutoSubmit(form) {
        if (!form) return;
        form.querySelectorAll(selectors.autoSubmit).forEach((input) => {
            input.addEventListener('change', () => form.submit());
        });
    }

    function bindResetFilters(form) {
        if (!form) return;
        const btn = document.querySelector(selectors.resetFilters);
        if (!btn) return;

        btn.addEventListener('click', function () {
            form.reset();
            localStorage.removeItem('receipts.filter.state');
            form.submit();
        });
    }

    function bindQuickActions() {
        document.querySelectorAll(selectors.quickAction).forEach((button) => {
            button.addEventListener('click', function () {
                const href = this.getAttribute('data-href');
                if (href) {
                    window.location.href = href;
                }
            });
        });
    }

    function bindFormSubmissionState(form) {
        if (!form) return;

        form.addEventListener('submit', function () {
            const submitButtons = form.querySelectorAll('button[type="submit"]');
            submitButtons.forEach((button) => {
                button.dataset.originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML = button.dataset.loadingText || button.innerHTML;
            });
        });
    }

    function bindConfirmationButtons() {
        const buttonSets = [
            { selector: selectors.sendSmsButton, message: 'Send receipt SMS now?' },
            { selector: selectors.resendSmsButton, message: 'Resend receipt SMS now?' },
            { selector: selectors.reprintButton, message: 'Reprint this receipt now?' },
        ];

        buttonSets.forEach(({ selector, message }) => {
            document.querySelectorAll(selector).forEach((button) => {
                button.addEventListener('click', function (event) {
                    const confirmed = window.confirm(this.dataset.confirm || message);
                    if (!confirmed) {
                        event.preventDefault();
                    }
                });
            });
        });
    }

    function highlightActiveFilters(form) {
        if (!form) return;
        const fields = form.querySelectorAll('input, select');
        fields.forEach((field) => {
            const toggleState = () => {
                const wrapper = field.closest('.receipt-field');
                if (!wrapper) return;
                const filled = !!field.value;
                wrapper.classList.toggle('is-filled', filled);
            };
            field.addEventListener('change', toggleState);
            toggleState();
        });
    }

    onReady(function () {
        const generateForm = document.querySelector(selectors.generateForm);
        const previewForm = document.querySelector(selectors.previewForm);
        const filterForm = document.querySelector(selectors.filterForm);

        restoreFilterState(filterForm);
        persistFilterState(filterForm);
        bindAutoSubmit(filterForm);
        bindResetFilters(filterForm);
        bindQuickActions();
        bindConfirmationButtons();
        bindFormSubmissionState(generateForm);
        bindFormSubmissionState(previewForm);
        bindFormSubmissionState(filterForm);
        highlightActiveFilters(filterForm);
    });
})();
