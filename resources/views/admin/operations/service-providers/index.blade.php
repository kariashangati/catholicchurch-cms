@extends('layouts.admin')

@section('title', db_trans('service_providers'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $providers = collect($providers ?? []);
    $categories = collect($categories ?? []);
    $activeCount = $providers->where('is_active', true)->count();
    $internalCount = $providers->where('is_internal', true)->count();
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-handshake"></i>{{ db_trans('service_providers') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('service_providers') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-list"></i>{{ number_format($providers->count()) }} {{ db_trans('records') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-circle-check"></i>{{ number_format($activeCount) }} {{ db_trans('active') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-user-shield"></i>{{ number_format($internalCount) }} {{ db_trans('internal') }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    @can('operations.service-providers.create')
                        <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createServiceProviderModal">
                            <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('add_service_provider') }}</span>
                        </button>
                    @endcan
                    <a href="{{ Route::has('operations.service-categories.index') ? route('operations.service-categories.index') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-screwdriver-wrench"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('service_categories') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="ui-table-card p-4">
        <div class="ui-section-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">{{ db_trans('service_providers') }}</h5>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="ui-section-badge">{{ number_format($providers->count()) }} {{ db_trans('records') }}</span>
                <a href="{{ route('pdf.operations.service-providers.export') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                </a>
                <a href="{{ route('operations.service-providers.export.excel') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle" id="serviceProvidersTable">
                <thead><tr><th>#</th><th>{{ db_trans('name') }}</th><th>{{ db_trans('category') }}</th><th>{{ db_trans('phone') }}</th><th>{{ db_trans('status') }}</th><th>{{ db_trans('internal') }}</th><th>{{ db_trans('active') }}</th><th class="text-end">{{ db_trans('actions') }}</th></tr></thead>
                <tbody>
                    @foreach($providers as $provider)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $provider->name }}</td>
                            <td>{{ $provider->category?->name ?? '—' }}</td>
                            <td>{{ $provider->phone }}</td>
                            <td><span class="ui-status-pill {{ $provider->status === \App\Models\ServiceProvider::STATUS_BLACKLISTED ? 'ui-status-rejected' : ($provider->status === \App\Models\ServiceProvider::STATUS_ACTIVE ? 'ui-status-approved' : 'ui-status-pending') }}">{{ $provider->status_label }}</span></td>
                            <td>{{ $provider->is_internal ? db_trans('yes') : db_trans('no') }}</td>
                            <td><span class="ui-status-pill {{ $provider->is_active ? 'ui-status-approved' : 'ui-status-pending' }}">{{ $provider->is_active ? db_trans('active') : db_trans('inactive') }}</span></td>
                            <td class="text-end text-nowrap">
                                @can('operations.service-providers.update')<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editServiceProviderModal{{ $provider->id }}"><i class="fas fa-pen me-1"></i>{{ db_trans('edit') }}</button>@endcan
                                @can('operations.service-providers.delete')<form action="{{ route('operations.service-providers.destroy', $provider) }}" method="POST" class="d-inline js-swal-delete">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>{{ db_trans('delete') }}</button></form>@endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @foreach($providers as $provider)
        @include('admin.operations.service-providers.partials.edit-modal', ['provider' => $provider])
    @endforeach
    @include('admin.operations.service-providers.partials.create-modal')
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.DataTable && $('#serviceProvidersTable').length) {
        $('#serviceProvidersTable').DataTable({ paging: true, info: true, searching: true, responsive: true, pageLength: 10, lengthMenu: [[5,10,25,50,100,-1],[5,10,25,50,100,@json(db_trans('all'))]], order: [], autoWidth: false, language: { search: @json(db_trans('search')) + ':', lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('entries')), info: @json(db_trans('showing')) + ' _START_ ' + @json(db_trans('to')) + ' _END_ ' + @json(db_trans('of')) + ' _TOTAL_ ' + @json(db_trans('entries')), infoEmpty: @json(db_trans('no_records_found')), zeroRecords: @json(db_trans('no_records_found')), paginate: { previous: '‹', next: '›' } } });
    }
    document.querySelectorAll('.js-swal-delete').forEach(function (form) { form.addEventListener('submit', function (event) { event.preventDefault(); Swal.fire({ icon: 'warning', title: @json(db_trans('are_you_sure')), text: @json(db_trans('this_action_cannot_be_undone')), showCancelButton: true, confirmButtonColor: '#7c3aed', confirmButtonText: @json(db_trans('delete')), cancelButtonText: @json(db_trans('cancel')) }).then(function (result) { if (result.isConfirmed) form.submit(); }); }); });
});
</script>
@endpush
