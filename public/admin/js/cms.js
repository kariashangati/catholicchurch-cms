(function () {
    'use strict';

    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    function initAutoSlug() {
        const titleInput = document.querySelector('input[name="title"]');
        const slugInput = document.querySelector('input[name="slug"]');

        if (!titleInput || !slugInput) {
            return;
        }

        let slugTouched = slugInput.value.trim() !== '';

        slugInput.addEventListener('input', function () {
            slugTouched = this.value.trim() !== '';
        });

        titleInput.addEventListener('input', function () {
            if (slugTouched) {
                return;
            }

            slugInput.value = slugify(this.value);
        });
    }

    function slugify(text) {
        return String(text || '')
            .toLowerCase()
            .trim()
            .replace(/['"]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function initConfirmDangerousActions() {
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                const message = form.getAttribute('data-confirm') || 'Are you sure?';

                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            });
        });
    }

    function initGalleryImagePreview() {
        document.querySelectorAll('[data-preview-target]').forEach(function (input) {
            input.addEventListener('change', function () {
                const targetSelector = this.getAttribute('data-preview-target');
                const target = document.querySelector(targetSelector);

                if (!target || !this.files || !this.files[0]) {
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (e) {
                    if (target.tagName.toLowerCase() === 'img') {
                        target.src = e.target.result;
                        target.classList.remove('d-none');
                    }
                };

                reader.readAsDataURL(this.files[0]);
            });
        });
    }

    function initSelectLabelMirrors() {
        document.querySelectorAll('select[data-text-target]').forEach(function (select) {
            const target = document.querySelector(select.getAttribute('data-text-target'));

            if (!target) {
                return;
            }

            const update = function () {
                const selected = select.options[select.selectedIndex];
                target.textContent = selected ? selected.text : '';
            };

            select.addEventListener('change', update);
            update();
        });
    }

    ready(function () {
        initAutoSlug();
        initConfirmDangerousActions();
        initGalleryImagePreview();
        initSelectLabelMirrors();
    });
})();