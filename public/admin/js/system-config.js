(function () {
    'use strict';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    function initImagePreviewInputs() {
        document.querySelectorAll('[data-image-preview-input]').forEach(function (input) {
            input.addEventListener('change', function () {
                const previewTarget = this.getAttribute('data-image-preview-input');
                const preview = document.querySelector(previewTarget);

                if (!preview || !this.files || !this.files[0]) {
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                };

                reader.readAsDataURL(this.files[0]);
            });
        });
    }

    function initMaintenanceToggleHints() {
        const globalSelect = document.querySelector('select[name="global_enabled"]');
        const downSelect = document.querySelector('select[name="use_laravel_down"]');

        if (!globalSelect || !downSelect) {
            return;
        }

        const applyState = function () {
            if (globalSelect.value === '0') {
                downSelect.value = '0';
            }
        };

        globalSelect.addEventListener('change', applyState);
        applyState();
    }

    function initSecretVisibilityToggles() {
        document.querySelectorAll('[data-toggle-secret]').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetSelector = this.getAttribute('data-toggle-secret');
                const input = document.querySelector(targetSelector);

                if (!input) {
                    return;
                }

                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });
    }

    function initCopyButtons() {
        document.querySelectorAll('[data-copy-value]').forEach(function (button) {
            button.addEventListener('click', async function () {
                const value = this.getAttribute('data-copy-value');

                if (!value || !navigator.clipboard) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(value);
                    this.classList.add('btn-success');

                    setTimeout(() => {
                        this.classList.remove('btn-success');
                    }, 900);
                } catch (error) {
                    console.error(error);
                }
            });
        });
    }

    ready(function () {
        initImagePreviewInputs();
        initMaintenanceToggleHints();
        initSecretVisibilityToggles();
        initCopyButtons();
    });
})();