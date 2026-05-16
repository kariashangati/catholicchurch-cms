<script src="{{ asset('admin/js/admin-kanda.js') }}"></script>
<script src="{{ asset('admin/js/admin-jumuiya.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('adminSidebarBackdrop');
        const toggle = document.getElementById('mobileSidebarToggle');

        if (toggle && sidebar && backdrop) {
            toggle.addEventListener('click', function () {
                sidebar.classList.toggle('show');
                backdrop.classList.toggle('show');
            });

            backdrop.addEventListener('click', function () {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
            });
        }
    });
</script>
"{{ asset('admin/js/communication.js') }}"></script>