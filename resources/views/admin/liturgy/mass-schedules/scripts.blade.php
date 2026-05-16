<script>
document.addEventListener('DOMContentLoaded', function () {
    const syncAssignableSelect = function (select) {
        const targetSelect = document.getElementById(select.dataset.target);
        const searchInput = document.getElementById(select.dataset.search);

        if (!targetSelect) {
            return;
        }

        const selectedType = select.value;
        const search = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let firstVisible = null;

        Array.from(targetSelect.options).forEach(function (option) {
            const matchesType = option.dataset.type === selectedType;
            const matchesSearch = !search || (option.dataset.search || option.textContent || '').toLowerCase().includes(search);
            option.hidden = !(matchesType && matchesSearch);

            if (!option.hidden && !firstVisible) {
                firstVisible = option;
            }
        });

        const selected = targetSelect.options[targetSelect.selectedIndex];
        if (selected && selected.hidden && firstVisible) {
            targetSelect.value = firstVisible.value;
        }
    };

    document.querySelectorAll('.liturgy-assignable-type').forEach(function (select) {
        select.addEventListener('change', function () {
            const searchInput = document.getElementById(select.dataset.search);
            if (searchInput) {
                searchInput.value = '';
            }
            syncAssignableSelect(select);
        });
        syncAssignableSelect(select);
    });

    document.querySelectorAll('.liturgy-assignable-search').forEach(function (input) {
        input.addEventListener('input', function () {
            const targetSelect = document.getElementById(input.dataset.target);
            if (!targetSelect) {
                return;
            }

            const typeSelect = document.querySelector('.liturgy-assignable-type[data-target="' + input.dataset.target + '"]');
            if (typeSelect) {
                syncAssignableSelect(typeSelect);
            }
        });
    });
});
</script>
