@extends('layouts.admin')

@section('title', db_trans('leadership_dashboard'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $summary = $summary ?? [];
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-user-tie"></i>{{ db_trans('leadership_dashboard') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('leadership_dashboard') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-users"></i>{{ number_format((int) ($summary['active_leaders'] ?? 0)) }} {{ db_trans('active_leaders') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-id-badge"></i>{{ number_format((int) ($summary['positions'] ?? 0)) }} {{ db_trans('leadership_positions') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-layer-group"></i>{{ db_trans('leadership') }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    @can('leadership.assignments.view')
                        <a href="{{ route('leadership.assignments.index') }}" class="ui-hero-action">
                            <span class="ui-hero-action-icon"><i class="fas fa-user-check"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('leadership_assignments') }}</span>
                        </a>
                    @endcan
                    @can('leadership.positions.view')
                        <a href="{{ route('leadership.positions.index') }}" class="ui-hero-action">
                            <span class="ui-hero-action-icon"><i class="fas fa-id-badge"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('leadership_positions') }}</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-tone-primary p-4 h-100">
                <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-users"></i></span><span class="ui-chip">{{ db_trans('total') }}</span></div>
                <div class="ui-stat-label">{{ db_trans('active_leaders') }}</div>
                <div class="ui-stat-value">{{ number_format((int) ($summary['active_leaders'] ?? 0)) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-success"><i class="fas fa-church"></i></div>
                <div class="ui-stat-label">{{ db_trans('parish_leaders') }}</div>
                <div class="ui-stat-value">{{ number_format((int) ($summary['parish_leaders'] ?? 0)) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-warning"><i class="fas fa-map"></i></div>
                <div class="ui-stat-label">{{ db_trans('kanda_leaders') }}</div>
                <div class="ui-stat-value">{{ number_format((int) ($summary['kanda_leaders'] ?? 0)) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-info"><i class="fas fa-layer-group"></i></div>
                <div class="ui-stat-label">{{ db_trans('jumuiya_leaders') }}</div>
                <div class="ui-stat-value">{{ number_format((int) ($summary['jumuiya_leaders'] ?? 0)) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="ui-table-card p-4 h-100">
                <div class="ui-section-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div><h5 class="mb-1">{{ db_trans('leadership_positions') }}</h5></div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="ui-section-badge">{{ number_format($leadersByPosition->count()) }} {{ db_trans('records') }}</span>
                        <a href="{{ route('pdf.leadership.dashboard.export') }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}</a>
                        <a href="{{ route('leadership.dashboard.export.excel') }}" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle leadership-table" id="leadershipPositionSummaryTable">
                        <thead>
                            <tr>
                                <th>{{ db_trans('position') }}</th>
                                <th>{{ db_trans('active_leaders') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leadersByPosition as $row)
                                <tr>
                                    <td>{{ $row->position?->name ?? '—' }}</td>
                                    <td>{{ number_format((int) $row->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">{{ db_trans('no_records_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="ui-table-card p-4 h-100">
                <div class="ui-section-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div><h5 class="mb-1">{{ db_trans('recent_leadership_activity') }}</h5></div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="ui-section-badge">{{ number_format($recentAssignments->count()) }} {{ db_trans('records') }}</span>
                        <a href="{{ route('pdf.leadership.dashboard.export') }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}</a>
                        <a href="{{ route('leadership.dashboard.export.excel') }}" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle leadership-table" id="recentLeadershipActivityTable">
                        <thead>
                            <tr>
                                <th>{{ db_trans('member') }}</th>
                                <th>{{ db_trans('position') }}</th>
                                <th>{{ db_trans('scope') }}</th>
                                <th>{{ db_trans('status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAssignments as $assignment)
                                <tr>
                                    <td>{{ $assignment->member?->full_name ?? '—' }}</td>
                                    <td>{{ $assignment->position?->name ?? '—' }}</td>
                                    <td>{{ $assignment->jumuiya?->name ?? $assignment->kanda?->name ?? db_trans($assignment->scope_type) }}</td>
                                    <td><span class="badge text-bg-{{ $assignment->status_badge_class }}">{{ $assignment->status_label }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">{{ db_trans('no_records_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.DataTable) {
        ['#leadershipPositionSummaryTable', '#recentLeadershipActivityTable'].forEach(function (selector) {
            if ($(selector).length) {
                $(selector).DataTable({
                    paging: true,
                    info: true,
                    searching: true,
                    responsive: true,
                    pageLength: 5,
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
    }
});
</script>
@endpush
