(function () {
    'use strict';

    function onReady(callback) {
        if (document.readyState !== 'loading') {
            callback();
            return;
        }
        document.addEventListener('DOMContentLoaded', callback);
    }

    onReady(function () {
        const downloadLink = document.querySelector('[data-receipt-download-link="true"]');
        if (!downloadLink) return;

        downloadLink.addEventListener('click', function () {
            const card = document.querySelector('.receipt-access-card');
            if (card) {
                card.classList.add('is-downloading');
            }
        });
    });
})();
