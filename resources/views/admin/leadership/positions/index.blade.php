@extends('layouts.admin')

@section('title', db_trans('leadership_positions'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $positionCollection = collect($positions ?? []);
    $activeCount = $positionCollection->where('is_active', true)->count();
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-id-badge"></i>{{ db_trans('leadership_positions') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('leadership_positions') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-list"></i>{{ number_format($positionCollection->count()) }} {{ db_trans('records') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-circle-check"></i>{{ number_format($activeCount) }} {{ db_trans('active') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-user-check"></i>{{ db_trans('leadership') }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    @can('leadership.positions.create')
                        <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createPositionModal">
                            <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('new_position') }}</span>
                        </button>
                    @endcan
                    @can('leadership.assignments.view')
                        <a href="{{ route('leadership.assignments.index') }}" class="ui-hero-action">
                            <span class="ui-hero-action-icon"><i class="fas fa-user-check"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('leadership_assignments') }}</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="ui-table-card p-4">
        <div class="ui-section-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div><h5 class="mb-1">{{ db_trans('leadership_positions') }}</h5></div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="ui-section-badge">{{ number_format($positionCollection->count()) }} {{ db_trans('records') }}</span>
                <a href="{{ route('pdf.leadership.positions.export') }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}</a>
                <a href="{{ route('leadership.positions.export.excel') }}" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle leadership-table" id="leadershipPositionsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ db_trans('name') }}</th>
                        <th>{{ db_trans('scope') }}</th>
                        <th>{{ db_trans('committee_type') }}</th>
                        <th>{{ db_trans('auto_role') }}</th>
                        <th>{{ db_trans('status') }}</th>
                        <th>{{ db_trans('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($positionCollection as $position)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $position->name }}</td>
                            <td>{{ db_trans($position->level_type) }}</td>
                            <td>{{ db_trans($position->committee_type) }}</td>
                            <td>{{ $position->auto_role_name ?: '—' }}</td>
                            <td><span class="badge text-bg-{{ $position->is_active ? 'success' : 'secondary' }}">{{ $position->is_active ? db_trans('active') : db_trans('inactive') }}</span></td>
                            <td class="text-nowrap">
                                @can('leadership.positions.update')
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPositionModal{{ $position->id }}">{{ db_trans('edit') }}</button>
                                @endcan
                                @can('leadership.positions.delete')
                                    <form method="POST" action="{{ route('leadership.positions.destroy', $position) }}" class="d-inline" onsubmit="return confirm('{{ db_trans('are_you_sure') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                        @include('admin.leadership.partials.position-modal', [
                            'modalId' => 'editPositionModal' . $position->id,
                            'title' => db_trans('edit') . ' ' . $position->name,
                            'action' => route('leadership.positions.update', $position),
                            'method' => 'PUT',
                            'position' => $position,
                        ])
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">{{ db_trans('no_records_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.leadership.partials.position-modal', [
    'modalId' => 'createPositionModal',
    'title' => db_trans('new_position'),
    'action' => route('leadership.positions.store'),
    'method' => 'POST',
    'position' => null,
])
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.DataTable && $('#leadershipPositionsTable').length) {
        $('#leadershipPositionsTable').DataTable({
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
