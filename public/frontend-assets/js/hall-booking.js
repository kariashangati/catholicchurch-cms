(function () {
    'use strict';

    function qs(selector, root) {
        return (root || document).querySelector(selector);
    }

    function qsa(selector, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(selector));
    }

    function renderCalendar(root, payload) {
        var grid = qs('[data-calendar-grid]', root);
        var monthLabel = qs('[data-calendar-month]', root);
        if (!grid || !payload) return;

        if (monthLabel) monthLabel.textContent = payload.month_label || '';
        grid.setAttribute('data-year', payload.year);
        grid.setAttribute('data-month', payload.month);
        grid.innerHTML = '';

        (payload.days || []).forEach(function (day) {
            if (!day) {
                var empty = document.createElement('div');
                empty.className = 'hall-day is-empty';
                grid.appendChild(empty);
                return;
            }

            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'hall-day status-' + day.status + (day.available ? ' is-available' : ' is-disabled');
            button.dataset.date = day.date;
            button.dataset.price = day.price;
            button.dataset.priceLabel = day.price_label;
            button.dataset.status = day.status;
            button.dataset.statusLabel = day.status_label;
            if (!day.available) button.disabled = true;
            button.innerHTML = '<span class="day-number">' + day.day + '</span>' +
                '<small>' + day.status_label + '</small>' +
                '<strong>' + day.price_label + '</strong>';
            grid.appendChild(button);
        });
    }

    function selectDay(button, root) {
        qsa('.hall-day.is-selected', root).forEach(function (el) {
            el.classList.remove('is-selected');
        });
        button.classList.add('is-selected');

        var dateInput = qs('[data-booking-date-input]');
        var dateText = qs('[data-selected-date-text]');
        var priceText = qs('[data-selected-price-text]');
        var submit = qs('[data-submit-booking]');

        if (dateInput) dateInput.value = button.dataset.date || '';
        if (dateText) dateText.textContent = button.dataset.date || '';
        if (priceText) priceText.textContent = button.dataset.priceLabel || '';
        if (submit) submit.disabled = false;
    }

    document.addEventListener('click', function (event) {
        var day = event.target.closest('.hall-day.is-available');
        if (day && !day.disabled) {
            var calendarRoot = day.closest('[data-calendar-root]') || document;
            selectDay(day, calendarRoot);
            return;
        }

        var copy = event.target.closest('[data-copy-text]');
        if (copy) {
            var text = copy.getAttribute('data-copy-text');
            if (navigator.clipboard && text) {
                navigator.clipboard.writeText(text).then(function () {
                    var original = copy.innerHTML;
                    copy.innerHTML = '<i class="bi bi-check2"></i>Copied';
                    setTimeout(function () { copy.innerHTML = original; }, 1400);
                });
            }
        }
    });

    qsa('[data-calendar-root]').forEach(function (root) {
        var url = root.getAttribute('data-availability-url');
        var grid = qs('[data-calendar-grid]', root);
        if (!url || !grid) return;

        function loadMonth(yearMonth) {
            var parts = String(yearMonth).split('-');
            if (parts.length !== 2) return;

            fetch(url + '?year=' + encodeURIComponent(parts[0]) + '&month=' + encodeURIComponent(parts[1]), {
                headers: { 'Accept': 'application/json' }
            })
                .then(function (response) { return response.json(); })
                .then(function (payload) { renderCalendar(root, payload); })
                .catch(function () {});
        }

        var prev = qs('[data-calendar-prev]', root);
        var next = qs('[data-calendar-next]', root);

        if (prev) {
            prev.addEventListener('click', function () {
                var year = parseInt(grid.getAttribute('data-year'), 10);
                var month = parseInt(grid.getAttribute('data-month'), 10) - 1;
                if (month < 1) { month = 12; year -= 1; }
                loadMonth(year + '-' + String(month).padStart(2, '0'));
            });
        }

        if (next) {
            next.addEventListener('click', function () {
                var year = parseInt(grid.getAttribute('data-year'), 10);
                var month = parseInt(grid.getAttribute('data-month'), 10) + 1;
                if (month > 12) { month = 1; year += 1; }
                loadMonth(year + '-' + String(month).padStart(2, '0'));
            });
        }
    });
})();
