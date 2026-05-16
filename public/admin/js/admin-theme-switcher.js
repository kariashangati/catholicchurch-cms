(function () {
    'use strict';

    var allowedThemes = ['classic', 'light', 'dark'];
    var bodyClasses = ['admin-theme-classic', 'admin-theme-light', 'admin-theme-dark'];
    var defaultThemeUrl = '/admin/theme';

    function normalizeTheme(theme) {
        return allowedThemes.indexOf(theme) !== -1 ? theme : 'classic';
    }

    function removeOldInjectedOverrides() {
        ['admin-theme-final-overrides', 'admin-theme-runtime-overrides', 'admin-theme-dynamic-overrides'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && el.parentNode) {
                el.parentNode.removeChild(el);
            }
        });
    }

    function applyTheme(theme) {
        theme = normalizeTheme(theme);

        removeOldInjectedOverrides();

        if (document.body) {
            document.body.classList.remove.apply(document.body.classList, bodyClasses);
            document.body.classList.add('admin-theme-' + theme);
            document.body.setAttribute('data-admin-theme', theme);
        }

        document.documentElement.setAttribute('data-admin-theme', theme);

        document.querySelectorAll('[data-admin-theme-option]').forEach(function (option) {
            var active = option.getAttribute('data-admin-theme-option') === theme;
            option.classList.toggle('active', active);
            option.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function getThemeUrl(option) {
        var form = option.closest('[data-admin-theme-form]');
        return option.getAttribute('data-admin-theme-url') ||
            (form ? form.getAttribute('action') : '') ||
            defaultThemeUrl;
    }

    function getCsrfToken() {
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        return tokenMeta ? tokenMeta.getAttribute('content') : '';
    }

    function saveTheme(theme, url) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ theme: theme })
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var startTheme = document.body.getAttribute('data-admin-theme') ||
            document.documentElement.getAttribute('data-admin-theme') ||
            'classic';

        applyTheme(startTheme);

        document.querySelectorAll('[data-admin-theme-option]').forEach(function (option) {
            option.addEventListener('click', function (event) {
                event.preventDefault();

                var theme = normalizeTheme(option.getAttribute('data-admin-theme-option'));
                var url = getThemeUrl(option);
                var previousTheme = normalizeTheme(
                    document.body.getAttribute('data-admin-theme') ||
                    document.documentElement.getAttribute('data-admin-theme') ||
                    'classic'
                );

                applyTheme(theme);

                saveTheme(theme, url)
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Theme save failed');
                        }
                        return response.json().catch(function () {
                            return {};
                        });
                    })
                    .then(function (data) {
                        if (data && data.theme) {
                            applyTheme(data.theme);
                        }
                    })
                    .catch(function () {
                        applyTheme(previousTheme);

                        var form = option.closest('[data-admin-theme-form]');
                        if (form) {
                            var hiddenInput = form.querySelector('input[name="theme"][type="hidden"]');

                            if (!hiddenInput) {
                                hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'theme';
                                form.appendChild(hiddenInput);
                            }

                            hiddenInput.value = theme;
                            form.submit();
                        }
                    });
            });
        });
    });
})();