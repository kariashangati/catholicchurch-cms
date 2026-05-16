@extends('layouts.admin')

@section('title', $pageTitle)

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/tithes-module-v4.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
<div class="admin-ui-v4 tithe-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-lg-8">
                <span class="ui-page-badge">{{ db_trans('zaka') }}</span>
                <h1 class="ui-page-title">{{ db_trans('tithe_activity_date') }}</h1>
            </div>

            <div class="col-lg-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('finance.tithes.activity-log') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-arrow-left"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('back') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card ui-table-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div class="flex-grow-1">
                    @include('admin.finance.tithes.partials.activity-header', [
                        'parishName' => $parishName,
                        'title' => db_trans('tithes_collected_on') . ' ' . $activityDate->format('Y-m-d'),
                        'subtitle' => db_trans('records') . ': ' . number_format($recordsCount),
                    ])
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if(Route::has('pdf.finance.tithes.activity-date.export'))
                        <a
                            href="{{ route('pdf.finance.tithes.activity-date.export', array_merge(['date' => $activityDate->format('Y-m-d')], request()->query())) }}"
                            target="_blank"
                            class="btn btn-sm btn-danger rounded-pill px-3"
                        >
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if(Route::has('finance.tithes.activity-date.export.excel'))
                        <a
                            href="{{ route('finance.tithes.activity-date.export.excel', array_merge(['date' => $activityDate->format('Y-m-d')], request()->query())) }}"
                            class="btn btn-sm btn-success rounded-pill px-3"
                        >
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="ui-stat-card ui-tone-primary p-4">
                        <div class="ui-stat-label">{{ db_trans('records') }}</div>
                        <div class="ui-stat-value">{{ number_format($recordsCount) }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="ui-stat-card ui-tone-success p-4">
                        <div class="ui-stat-label">{{ db_trans('grand_total') }}</div>
                        <div class="ui-stat-value">{{ number_format($total, 2) }}</div>
                    </div>
                </div>
            </div>

            <h5 class="ui-section-title mb-3">{{ db_trans('recorders') }}</h5>

            <div class="table-responsive mb-4">
                <table class="table align-middle" id="titheActivityRecorderTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ db_trans('recorded_by') }}</th>
                            <th>{{ db_trans('number_of_records') }}</th>
                            <th class="text-end">{{ db_trans('amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recorderRows as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if($row->recorded_by)
                                        <a href="{{ route('finance.tithes.activity-recorder', ['date' => $activityDate->format('Y-m-d'), 'recorder' => $row->recorded_by]) }}">
                                            {{ $row->recorder_name }}
                                        </a>
                                    @else
                                        {{ $row->recorder_name }}
                                    @endif
                                </td>
                                <td>{{ number_format($row->records_count) }}</td>
                                <td class="text-end fw-bold">{{ number_format((float) $row->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($batches->isNotEmpty())
                <h5 class="ui-section-title mb-3">{{ db_trans('denomination_summary') }}</h5>

                @foreach($batches as $batch)
                    <div class="border rounded-4 p-3 mb-3">
                        <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                <div class="fw-bold">{{ db_trans('bulk_batch') }} #{{ $batch->id }}</div>
                                <div class="text-muted small">
                                    {{ db_trans('recorded_by') }}:
                                    {{ $batch->recorder?->name ?? '—' }}
                                </div>
                            </div>

                            <div class="text-end">
                                <div class="fw-bold">{{ number_format((float) $batch->total_amount, 2) }}</div>
                                <div class="text-muted small">{{ number_format($batch->rows_count) }} {{ db_trans('records') }}</div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('denomination') }}</th>
                                        <th>{{ db_trans('quantity') }}</th>
                                        <th class="text-end">{{ db_trans('amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batch->denominations as $denomination)
                                        <tr>
                                            <td>{{ number_format((float) $denomination->denomination_value, 0) }}</td>
                                            <td>{{ number_format($denomination->quantity) }}</td>
                                            <td class="text-end">{{ number_format((float) $denomination->total_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif

            <h5 class="ui-section-title mb-3">{{ db_trans('all_records') }}</h5>

            <div class="table-responsive">
                <table class="table align-middle" id="titheActivityDateTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ db_trans('member') }}</th>
                            <th>{{ db_trans('phone') }}</th>
                            <th>{{ db_trans('kanda') }}</th>
                            <th>{{ db_trans('jumuiya') }}</th>
                            <th>{{ db_trans('recorded_by') }}</th>
                            <th class="text-end">{{ db_trans('amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->member?->full_name ?? '—' }}</td>
                                <td>{{ $item->member?->phone ?: '—' }}</td>
                                <td>{{ $item->member?->familia?->jumuiya?->kanda?->name ?? $item->jumuiya?->kanda?->name ?? '—' }}</td>
                                <td>{{ $item->jumuiya?->name ?? $item->member?->familia?->jumuiya?->name ?? '—' }}</td>
                                <td>{{ $item->recorder?->name ?? '—' }}</td>
                                <td class="text-end fw-bold">{{ number_format((float) $item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">{{ db_trans('grand_total') }}</th>
                            <th class="text-end">{{ number_format($total, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dataTableLanguage = {
        search: '',
        searchPlaceholder: @json(db_trans('search')) + '...',
        lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('rows')),
        info: @json(db_trans('showing')) + ' _START_ - _END_ / _TOTAL_',
        infoEmpty: @json(db_trans('no_records_available')),
        zeroRecords: @json(db_trans('no_matching_records')),
        paginate: {
            previous: @json(db_trans('previous')),
            next: @json(db_trans('next'))
        }
    };

    if (window.jQuery && document.getElementById('titheActivityRecorderTable')) {
        $('#titheActivityRecorderTable').DataTable({
            paging: true,
            info: true,
            searching: true,
            ordering: true,
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, @json(db_trans('all'))]],
            language: dataTableLanguage
        });
    }

    if (window.jQuery && document.getElementById('titheActivityDateTable')) {
        $('#titheActivityDateTable').DataTable({
            paging: true,
            info: true,
            searching: true,
            ordering: true,
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, @json(db_trans('all'))]],
            language: dataTableLanguage
        });
    }
});
</script>
@endpush