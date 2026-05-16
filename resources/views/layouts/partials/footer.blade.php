<footer class="admin-footer">
    <div class="container-fluid">
        <div class="admin-footer-inner">
            <div>
                © {{ date('Y') }} {{ config('app.name', 'ChurchMS') }} · {{ db_trans('all_rights_reserved') }}
            </div>
            <div class="text-muted small">
                {{ db_trans('built_for_modern_church_management') }}
            </div>
        </div>
    </div>
</footer>