document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.js-edit-jumuiya');
    const editForm = document.getElementById('editJumuiyaForm');

    editButtons.forEach(button => {
        button.addEventListener('click', async function () {
            const url = this.dataset.url;

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch data');
                }

                const data = await response.json();

                editForm.action = this.dataset.updateUrl;
                editForm.querySelector('[name="name"]').value = data.name ?? '';
                editForm.querySelector('[name="kanda_id"]').value = data.kanda_id ?? '';
                editForm.querySelector('[name="comment"]').value = data.comment ?? '';
                editForm.querySelector('[name="is_active"]').checked = !!data.is_active;

                const previewWrap = document.getElementById('editJumuiyaImagePreviewWrap');
                const previewImage = document.getElementById('editJumuiyaImagePreview');

                if (data.image_url) {
                    previewImage.src = data.image_url;
                    previewWrap.style.display = 'block';
                } else {
                    previewImage.src = '';
                    previewWrap.style.display = 'none';
                }

                const modal = new bootstrap.Modal(document.getElementById('editJumuiyaModal'));
                modal.show();
            } catch (error) {
                alert('Failed to load Jumuiya details.');
            }
        });
    });

    const deleteForms = document.querySelectorAll('.js-confirm-delete');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function (event) {
            const confirmed = confirm('Are you sure you want to delete this Jumuiya?');
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
});