@extends('layouts.admin')

@section('title', db_trans('leadership_assignments'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $assignmentCollection = collect($assignments ?? []);
    $activeCount = $assignmentCollection->where('status', \App\Models\LeadershipAssignment::STATUS_ACTIVE)->count();
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-user-check"></i>{{ db_trans('leadership_assignments') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('leadership_assignments') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-list"></i>{{ number_format($assignmentCollection->count()) }} {{ db_trans('records') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-user-check"></i>{{ number_format($activeCount) }} {{ db_trans('hai') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-id-badge"></i>{{ number_format($positions->count()) }} {{ db_trans('leadership_positions') }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    @can('leadership.assignments.create')
                        <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                            <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('new_assignment') }}</span>
                        </button>
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

    <div class="ui-table-card p-4">
        <div class="ui-section-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div><h5 class="mb-1">{{ db_trans('leadership_assignments') }}</h5></div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="ui-section-badge">{{ number_format($assignmentCollection->count()) }} {{ db_trans('records') }}</span>
                <a href="{{ route('pdf.leadership.assignments.export') }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}</a>
                <a href="{{ route('leadership.assignments.export.excel') }}" class="btn btn-sm btn-outline-success"><i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle leadership-table" id="leadershipAssignmentsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ db_trans('member') }}</th>
                        <th>{{ db_trans('position') }}</th>
                        <th>{{ db_trans('scope') }}</th>
                        <th>{{ db_trans('started_at') }}</th>
                        <th>{{ db_trans('ended_at') }}</th>
                        <th>{{ db_trans('user') }}</th>
                        <th>{{ db_trans('status') }}</th>
                        <th>{{ db_trans('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignmentCollection as $assignment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $assignment->member?->full_name ?? '—' }}</td>
                            <td>{{ $assignment->position?->name ?? '—' }}</td>
                            <td>{{ $assignment->jumuiya?->name ?? $assignment->kanda?->name ?? $assignment->apostolicGroup?->name ?? db_trans($assignment->scope_type) }}</td>
                            <td data-order="{{ optional($assignment->started_at)->format('Y-m-d') }}">{{ optional($assignment->started_at)->format('d M Y') }}</td>
                            <td data-order="{{ optional($assignment->ended_at)->format('Y-m-d') }}">{{ optional($assignment->ended_at)->format('d M Y') ?: '—' }}</td>
                            <td>{{ $assignment->user?->name ?? '—' }}</td>
                            <td><span class="badge text-bg-{{ $assignment->status_badge_class }}">{{ $assignment->status_label }}</span></td>
                            <td class="text-nowrap">
                                @can('leadership.assignments.update')
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAssignmentModal{{ $assignment->id }}">{{ db_trans('edit') }}</button>
                                @endcan
                                @can('leadership.assignments.delete')
                                    <form method="POST" action="{{ route('leadership.assignments.destroy', $assignment) }}" class="d-inline" onsubmit="return confirm('{{ db_trans('are_you_sure') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                        @include('admin.leadership.partials.assignment-modal', [
                            'modalId' => 'editAssignmentModal' . $assignment->id,
                            'title' => db_trans('edit') . ' ' . ($assignment->position?->name ?? db_trans('leadership_assignment')),
                            'action' => route('leadership.assignments.update', $assignment),
                            'method' => 'PUT',
                            'assignment' => $assignment,
                            'positions' => $positions,
                            'members' => $members,
                            'users' => $users,
                            'kandas' => $kandas,
                            'jumuiyas' => $jumuiyas,
                            'apostolicGroups' => $apostolicGroups,
                        ])
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">{{ db_trans('no_records_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.leadership.partials.assignment-modal', [
    'modalId' => 'createAssignmentModal',
    'title' => db_trans('new_assignment'),
    'action' => route('leadership.assignments.store'),
    'method' => 'POST',
    'assignment' => null,
    'positions' => $positions,
    'members' => $members,
    'users' => $users,
    'kandas' => $kandas,
    'jumuiyas' => $jumuiyas,
    'apostolicGroups' => $apostolicGroups,
])
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.DataTable && $('#leadershipAssignmentsTable').length) {
        $('#leadershipAssignmentsTable').DataTable({
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
