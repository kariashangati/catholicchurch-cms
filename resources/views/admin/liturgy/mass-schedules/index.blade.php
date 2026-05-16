@extends('layouts.admin')

@section('title', db_trans('mass_schedules'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $massSchedules = collect($massSchedules ?? []);
    $scheduleCount = $massSchedules->count();
    $publishedCount = $massSchedules->where('status', \App\Models\MassSchedule::STATUS_PUBLISHED)->count();
    $assignmentCount = $massSchedules->sum(fn ($schedule) => $schedule->assignments?->count() ?? 0);
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-calendar-days"></i>{{ db_trans('mass_schedules') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('mass_schedules') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-list"></i>{{ number_format($scheduleCount) }} {{ db_trans('records') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-circle-check"></i>{{ number_format($publishedCount) }} {{ db_trans('imechapishwa') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-user-check"></i>{{ number_format($assignmentCount) }} {{ db_trans('assignments') }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    @can('liturgy.mass-schedules.create')
                        <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createMassScheduleModal">
                            <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('new_mass_schedule') }}</span>
                        </button>
                    @endcan
                    @can('liturgy.mass-types.view')
                        <a href="{{ route('liturgy.mass-types.index') }}" class="ui-hero-action">
                            <span class="ui-hero-action-icon"><i class="fas fa-church"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('mass_types') }}</span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @include('admin.liturgy.partials.alerts')

    <div class="ui-table-card p-4">
        <div class="ui-section-heading">
            <div><h5 class="mb-1">{{ db_trans('mass_schedules') }}</h5></div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a href="{{ route('pdf.liturgy.mass-schedules.export') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                </a>
                <a href="{{ route('liturgy.mass-schedules.export.excel') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                </a>
                <span class="ui-section-badge">{{ number_format($scheduleCount) }} {{ db_trans('records') }}</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="massSchedulesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ db_trans('title') }}</th>
                        <th>{{ db_trans('mass_type') }}</th>
                        <th>{{ db_trans('date') }}</th>
                        <th>{{ db_trans('location') }}</th>
                        <th>{{ db_trans('status') }}</th>
                        <th>{{ db_trans('assignments') }}</th>
                        <th class="text-end">{{ db_trans('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($massSchedules as $schedule)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">{{ $schedule->title }}</div>
                                @if($schedule->special_occasion)
                                    <span class="ui-status-pill ui-status-pending">{{ db_trans('special') }}</span>
                                @endif
                            </td>
                            <td>{{ $schedule->massType?->name ?: '—' }}</td>
                            <td>{{ optional($schedule->scheduled_at)->format('d M Y H:i') }}</td>
                            <td>{{ $schedule->location }}</td>
                            <td><span class="badge text-bg-{{ $schedule->status_class }}">{{ $schedule->status_label }}</span></td>
                            <td>
                                <ul class="mb-0 ps-3 small">
                                    @forelse($schedule->assignments as $assignment)
                                        <li>
                                            <strong>{{ $assignment->role_name }}</strong>: {{ $assignment->assignableLabel() }}
                                            @can('liturgy.mass-schedules.update')
                                                <button type="button" class="btn btn-link btn-sm p-0 ms-1" data-bs-toggle="modal" data-bs-target="#editAssignmentModal{{ $assignment->id }}">{{ db_trans('edit') }}</button>
                                            @endcan
                                            @can('liturgy.mass-schedules.delete')
                                                <form action="{{ route('liturgy.mass-schedule-assignments.destroy', $assignment) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ db_trans('are_you_sure') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0 ms-1">{{ db_trans('delete') }}</button>
                                                </form>
                                            @endcan
                                        </li>
                                    @empty
                                        <li class="text-muted">{{ db_trans('no_records_found') }}</li>
                                    @endforelse
                                </ul>
                                @can('liturgy.mass-schedules.update')
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#createAssignmentModal{{ $schedule->id }}">{{ db_trans('add_assignment') }}</button>
                                @endcan
                            </td>
                            <td class="text-end text-nowrap">
                                @can('liturgy.mass-schedules.update')
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMassScheduleModal{{ $schedule->id }}">{{ db_trans('edit') }}</button>
                                @endcan
                                @can('liturgy.mass-schedules.delete')
                                    <form action="{{ route('liturgy.mass-schedules.destroy', $schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ db_trans('are_you_sure') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">{{ db_trans('no_records_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@include('admin.liturgy.mass-schedules.modal', [
    'modalId' => 'createMassScheduleModal',
    'action' => route('liturgy.mass-schedules.store'),
    'method' => 'POST',
    'schedule' => null,
    'massTypes' => $massTypes,
    'statusOptions' => $statusOptions,
])

@foreach($massSchedules as $schedule)
    @include('admin.liturgy.mass-schedules.modal', [
        'modalId' => 'editMassScheduleModal' . $schedule->id,
        'action' => route('liturgy.mass-schedules.update', $schedule),
        'method' => 'PUT',
        'schedule' => $schedule,
        'massTypes' => $massTypes,
        'statusOptions' => $statusOptions,
    ])

    @include('admin.liturgy.mass-schedules.assignment-modal', [
        'modalId' => 'createAssignmentModal' . $schedule->id,
        'action' => route('liturgy.mass-schedule-assignments.store'),
        'method' => 'POST',
        'schedule' => $schedule,
        'assignment' => null,
        'assignmentTargets' => $assignmentTargets,
    ])

    @foreach($schedule->assignments as $assignment)
        @include('admin.liturgy.mass-schedules.assignment-modal', [
            'modalId' => 'editAssignmentModal' . $assignment->id,
            'action' => route('liturgy.mass-schedule-assignments.update', $assignment),
            'method' => 'PUT',
            'schedule' => $schedule,
            'assignment' => $assignment,
            'assignmentTargets' => $assignmentTargets,
        ])
    @endforeach
@endforeach
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.DataTable && $('#massSchedulesTable').length) {
        $('#massSchedulesTable').DataTable({
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
@include('admin.liturgy.mass-schedules.scripts')
@endpush
