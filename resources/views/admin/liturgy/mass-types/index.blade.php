@extends('layouts.admin')

@section('title', db_trans('mass_types'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $massTypes = collect($massTypes ?? []);
    $typeCount = $massTypes->count();
    $activeCount = $massTypes->where('is_active', true)->count();
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-church"></i>{{ db_trans('mass_types') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('mass_types') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-list"></i>{{ number_format($typeCount) }} {{ db_trans('records') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-circle-check"></i>{{ number_format($activeCount) }} {{ db_trans('active') }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    @can('liturgy.mass-types.create')
                        <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createMassTypeModal">
                            <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('new_mass_type') }}</span>
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
            <div><h5 class="mb-1">{{ db_trans('mass_types') }}</h5></div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="{{ route('pdf.liturgy.mass-types.export') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                </a>
                <a href="{{ route('liturgy.mass-types.export.excel') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                </a>
                <span class="ui-section-badge">{{ number_format($typeCount) }} {{ db_trans('records') }}</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="massTypesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ db_trans('name') }}</th>
                        <th>{{ db_trans('slug') }}</th>
                        <th>{{ db_trans('description') }}</th>
                        <th>{{ db_trans('status') }}</th>
                        <th class="text-end">{{ db_trans('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($massTypes as $massType)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $massType->name }}</td>
                            <td>{{ $massType->slug }}</td>
                            <td>{{ $massType->description ?: '—' }}</td>
                            <td>
                                <span class="badge text-bg-{{ $massType->is_active ? 'success' : 'secondary' }}">
                                    {{ $massType->is_active ? db_trans('active') : db_trans('inactive') }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                @can('liturgy.mass-types.update')
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMassTypeModal{{ $massType->id }}">{{ db_trans('edit') }}</button>
                                @endcan
                                @can('liturgy.mass-types.delete')
                                    <form action="{{ route('liturgy.mass-types.destroy', $massType) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ db_trans('are_you_sure') }}')">
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

@include('admin.liturgy.mass-types.modal', [
    'modalId' => 'createMassTypeModal',
    'action' => route('liturgy.mass-types.store'),
    'method' => 'POST',
    'massType' => null,
])

@foreach($massTypes as $massType)
    @include('admin.liturgy.mass-types.modal', [
        'modalId' => 'editMassTypeModal' . $massType->id,
        'action' => route('liturgy.mass-types.update', $massType),
        'method' => 'PUT',
        'massType' => $massType,
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
    if (window.jQuery && $.fn.DataTable && $('#massTypesTable').length) {
        $('#massTypesTable').DataTable({
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
