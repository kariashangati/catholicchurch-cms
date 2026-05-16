document.addEventListener('DOMContentLoaded', function () {
    const successMeta = document.querySelector('meta[name="app-success-message"]');

    if (typeof Swal !== 'undefined' && successMeta && successMeta.content.trim() !== '') {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: successMeta.content,
            confirmButtonColor: '#7c3aed',
            timer: 2600,
            timerProgressBar: true
        });
    }

    const hasValidationErrors = document.querySelector('.is-invalid');
    if (typeof Swal !== 'undefined' && hasValidationErrors) {
        Swal.fire({
            icon: 'error',
            title: 'Please fix the form',
            text: 'There are validation errors in the submitted form.',
            confirmButtonColor: '#7c3aed'
        });
    }
});