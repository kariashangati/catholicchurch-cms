document.addEventListener('DOMContentLoaded', function () {
    const deleteForms = document.querySelectorAll('.js-confirm-delete');

    deleteForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const confirmed = confirm('Are you sure you want to delete this record?');
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
});