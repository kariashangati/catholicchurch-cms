<div class="modal fade finance-modal" id="createServiceProviderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content ui-modal-card border-0" method="POST" action="{{ route('operations.service-providers.store') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title mb-0">{{ db_trans('add_service_provider') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
            </div>
            <div class="modal-body">
                @php($provider = null)
                @include('admin.operations.service-providers.partials.form')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ db_trans('save') }}</button>
            </div>
        </form>
    </div>
</div>
