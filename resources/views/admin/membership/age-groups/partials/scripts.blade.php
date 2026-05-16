<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.DataTable && $('#ageGroupsTable').length) {
        $('#ageGroupsTable').DataTable({
            paging: true,
            info: true,
            searching: true,
            responsive: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, @json(db_trans('all'))]],
            order: [],
            autoWidth: false,
            language: {
                search: @json(db_trans('search')) + ':',
                lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('entries')),
                info: @json(db_trans('showing')) + ' _START_ ' + @json(db_trans('to')) + ' _END_ ' + @json(db_trans('of')) + ' _TOTAL_ ' + @json(db_trans('entries')),
                infoEmpty: @json(db_trans('no_records_found')),
                zeroRecords: @json(db_trans('no_records_found')),
                paginate: { previous: '‹', next: '›' }
            }
        });
    }

    const createForm = document.getElementById('createAgeGroupForm');
    const editForm = document.getElementById('editAgeGroupForm');

    function setErrors(form, message) {
        const box = form.querySelector('.js-form-errors');
        if (!box) return;
        box.textContent = message;
        box.classList.remove('d-none');
    }

    function clearErrors(form) {
        const box = form.querySelector('.js-form-errors');
        if (!box) return;
        box.textContent = '';
        box.classList.add('d-none');
    }

    function firstErrorMessage(data) {
        if (data && data.errors) {
            const firstKey = Object.keys(data.errors)[0];
            if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
                return data.errors[firstKey][0];
            }
        }

        return (data && data.message) ? data.message : @json(db_trans('validation_failed'));
    }

    async function submitAjaxForm(form) {
        clearErrors(form);
        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json().catch(function () { return {}; });

        if (!response.ok) {
            setErrors(form, firstErrorMessage(data));
            return;
        }

        window.location.reload();
    }

    if (createForm) {
        createForm.addEventListener('submit', function (event) {
            event.preventDefault();
            submitAjaxForm(createForm);
        });
    }

    if (editForm) {
        document.querySelectorAll('.js-edit-age-group').forEach(function (button) {
            button.addEventListener('click', function () {
                clearErrors(editForm);
                editForm.action = @json(url('membership/age-groups')) + '/' + this.dataset.id;
                editForm.querySelector('[name="name"]').value = this.dataset.name || '';
                editForm.querySelector('[name="min_age"]').value = this.dataset.minAge || '';
                editForm.querySelector('[name="max_age"]').value = this.dataset.maxAge || '';
                editForm.querySelector('[name="gender_scope"]').value = this.dataset.genderScope || @json(\App\Models\AgeGroup::GENDER_SCOPE_ALL);
                editForm.querySelector('[name="description"]').value = this.dataset.description || '';
                editForm.querySelector('[name="is_active"][value="1"]').checked = this.dataset.isActive === '1';
            });
        });

        editForm.addEventListener('submit', function (event) {
            event.preventDefault();
            submitAjaxForm(editForm);
        });
    }

    document.querySelectorAll('.js-delete-age-group').forEach(function (button) {
        button.addEventListener('click', async function () {
            const confirmed = await Swal.fire({
                icon: 'warning',
                title: @json(db_trans('are_you_sure')),
                text: @json(db_trans('delete_age_group_confirmation')),
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                confirmButtonText: @json(db_trans('delete')),
                cancelButtonText: @json(db_trans('cancel'))
            });

            if (!confirmed.isConfirmed) {
                return;
            }

            const response = await fetch(this.dataset.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new URLSearchParams({ _method: 'DELETE' }),
            });

            const data = await response.json().catch(function () { return {}; });

            if (!response.ok) {
                Swal.fire({ icon: 'error', title: @json(db_trans('error')), text: firstErrorMessage(data) });
                return;
            }

            window.location.reload();
        });
    });
});
</script>
