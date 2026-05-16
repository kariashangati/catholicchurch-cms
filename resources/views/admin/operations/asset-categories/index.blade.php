@extends('layouts.admin')

@section('title', db_trans('asset_categories'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $categories = collect($categories ?? []);
    $activeCount = $categories->where('is_active', true)->count();
    $inactiveCount = $categories->where('is_active', false)->count();
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-boxes-stacked"></i>{{ db_trans('asset_categories') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('asset_categories') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-list"></i>{{ number_format($categories->count()) }} {{ db_trans('records') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-circle-check"></i>{{ number_format($activeCount) }} {{ db_trans('active') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-ban"></i>{{ number_format($inactiveCount) }} {{ db_trans('inactive') }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    @can('operations.asset-categories.create')
                        <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createAssetCategoryModal">
                            <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('add_asset_category') }}</span>
                        </button>
                    @endcan
                    <a href="{{ Route::has('operations.church-assets.index') ? route('operations.church-assets.index') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-church"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('church_assets') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="ui-table-card p-4">
        <div class="ui-section-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">{{ db_trans('asset_categories') }}</h5>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="ui-section-badge">{{ number_format($categories->count()) }} {{ db_trans('records') }}</span>
                <a href="{{ route('pdf.operations.asset-categories.export') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                </a>
                <a href="{{ route('operations.asset-categories.export.excel') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle" id="assetCategoriesTable">
                <thead><tr><th>#</th><th>{{ db_trans('name') }}</th><th>{{ db_trans('description') }}</th><th>{{ db_trans('status') }}</th><th class="text-end">{{ db_trans('actions') }}</th></tr></thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td>{{ $category->description ?: '—' }}</td>
                            <td><span class="ui-status-pill {{ $category->is_active ? 'ui-status-approved' : 'ui-status-pending' }}">{{ $category->is_active ? db_trans('active') : db_trans('inactive') }}</span></td>
                            <td class="text-end text-nowrap">
                                @can('operations.asset-categories.update')<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAssetCategoryModal{{ $category->id }}"><i class="fas fa-pen me-1"></i>{{ db_trans('edit') }}</button>@endcan
                                @can('operations.asset-categories.delete')<form action="{{ route('operations.asset-categories.destroy', $category) }}" method="POST" class="d-inline js-swal-delete">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>{{ db_trans('delete') }}</button></form>@endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @foreach($categories as $category)
        @include('admin.operations.asset-categories.partials.edit-modal', ['category' => $category])
    @endforeach
    @include('admin.operations.asset-categories.partials.create-modal')
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
    if (window.jQuery && $.fn.DataTable && $('#assetCategoriesTable').length) {
        $('#assetCategoriesTable').DataTable({ paging: true, info: true, searching: true, responsive: true, pageLength: 10, lengthMenu: [[5,10,25,50,100,-1],[5,10,25,50,100,@json(db_trans('all'))]], order: [], autoWidth: false, language: { search: @json(db_trans('search')) + ':', lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('entries')), info: @json(db_trans('showing')) + ' _START_ ' + @json(db_trans('to')) + ' _END_ ' + @json(db_trans('of')) + ' _TOTAL_ ' + @json(db_trans('entries')), infoEmpty: @json(db_trans('no_records_found')), zeroRecords: @json(db_trans('no_records_found')), paginate: { previous: '‹', next: '›' } } });
    }
    document.querySelectorAll('.js-swal-delete').forEach(function (form) { form.addEventListener('submit', function (event) { event.preventDefault(); Swal.fire({ icon: 'warning', title: @json(db_trans('are_you_sure')), text: @json(db_trans('this_action_cannot_be_undone')), showCancelButton: true, confirmButtonColor: '#7c3aed', confirmButtonText: @json(db_trans('delete')), cancelButtonText: @json(db_trans('cancel')) }).then(function (result) { if (result.isConfirmed) form.submit(); }); }); });
});
</script>
@endpush
