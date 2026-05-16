(function () {
    'use strict';

    const allowedThemes = ['classic', 'light', 'dark'];

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function applyTheme(theme) {
        if (!allowedThemes.includes(theme)) {
            theme = 'classic';
        }

        document.body.classList.remove('admin-theme-classic', 'admin-theme-light', 'admin-theme-dark');
        document.body.classList.add('admin-theme-' + theme);
        document.body.setAttribute('data-admin-theme', theme);

        document.querySelectorAll('[data-admin-theme-option]').forEach(function (button) {
            button.classList.toggle('active', button.getAttribute('data-admin-theme-option') === theme);
        });
    }

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-admin-theme-form]');
        if (!form) return;

        const submitter = event.submitter;
        const selectedTheme = submitter?.value;

        if (!allowedThemes.includes(selectedTheme)) return;

        event.preventDefault();
        applyTheme(selectedTheme);

        const formData = new FormData(form);
        formData.set('theme', selectedTheme);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData,
            credentials: 'same-origin'
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Theme save failed');
            }
            return response.json();
        }).then(function (payload) {
            if (payload && payload.theme) {
                applyTheme(payload.theme);
            }
        }).catch(function () {
            form.submit();
        });
    });
})();
