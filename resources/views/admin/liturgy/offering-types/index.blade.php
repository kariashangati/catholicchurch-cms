@extends('layouts.admin')

@section('title', db_trans('offering_types'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $offeringTypes = collect($offeringTypes ?? []);
    $typeCount = $offeringTypes->count();
    $activeCount = $offeringTypes->where('is_active', true)->count();
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-hand-holding-heart"></i>{{ db_trans('offering_types') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('offering_types') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-list"></i>{{ number_format($typeCount) }} {{ db_trans('records') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-circle-check"></i>{{ number_format($activeCount) }} {{ db_trans('active') }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    @can('liturgy.offering-types.create')
                        <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createOfferingTypeModal">
                            <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('new_offering_type') }}</span>
                        </button>
                    @endcan
                    @can('liturgy.mass-schedules.view')
                        <a href="{{ route('liturgy.mass-schedules.index') }}" class="ui-hero-action">
                            <span class="ui-hero-action-icon"><i class="fas fa-calendar-days"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('mass_schedules') }}</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @include('admin.liturgy.partials.alerts')

    <div class="ui-table-card p-4">
        <div class="ui-section-heading">
            <div><h5 class="mb-1">{{ db_trans('offering_types') }}</h5></div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="{{ route('pdf.liturgy.offering-types.export') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                </a>
                <a href="{{ route('liturgy.offering-types.export.excel') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                </a>
                <span class="ui-section-badge">{{ number_format($typeCount) }} {{ db_trans('records') }}</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="offeringTypesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ db_trans('name') }}</th>
                        <th>{{ db_trans('category') }}</th>
                        <th>{{ db_trans('slug') }}</th>
                        <th>{{ db_trans('status') }}</th>
                        <th class="text-end">{{ db_trans('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offeringTypes as $offeringType)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $offeringType->name }}</td>
                            <td><span class="badge text-bg-info">{{ $offeringType->category_label }}</span></td>
                            <td>{{ $offeringType->slug }}</td>
                            <td>
                                <span class="badge text-bg-{{ $offeringType->is_active ? 'success' : 'secondary' }}">
                                    {{ $offeringType->is_active ? db_trans('active') : db_trans('inactive') }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                @can('liturgy.offering-types.update')
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editOfferingTypeModal{{ $offeringType->id }}">{{ db_trans('edit') }}</button>
                                @endcan
                                @can('liturgy.offering-types.delete')
                                    <form action="{{ route('liturgy.offering-types.destroy', $offeringType) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ db_trans('are_you_sure') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">{{ db_trans('no_records_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@include('admin.liturgy.offering-types.modal', [
    'modalId' => 'createOfferingTypeModal',
    'action' => route('liturgy.offering-types.store'),
    'method' => 'POST',
    'offeringType' => null,
    'categories' => $categories,
])

@foreach($offeringTypes as $offeringType)
    @include('admin.liturgy.offering-types.modal', [
        'modalId' => 'editOfferingTypeModal' . $offeringType->id,
        'action' => route('liturgy.offering-types.update', $offeringType),
        'method' => 'PUT',
        'offeringType' => $offeringType,
        'categories' => $categories,
    ])
@endforeach
@endsection


@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.DataTable && $('#offeringTypesTable').length) {
        $('#offeringTypesTable').DataTable({
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
});
</script>
@endpush
