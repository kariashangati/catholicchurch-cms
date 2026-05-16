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
                <h1 class="ui-page-title">{{ db_trans('tithe_activity_log') }}</h1>
            </div>

            <div class="col-lg-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('finance.tithes.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-receipt"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('tithes') }}</span>
                    </a>

                    <a href="{{ route('finance.tithes.bulk.entry') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-layer-group"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('bulk_tithe_entry') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" class="card ui-filter-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('year') }}</label>
                    <select name="year" class="form-select">
                        @for($yr = now()->year; $yr >= 2020; $yr--)
                            <option value="{{ $yr }}" @selected(($filters['year'] ?? now()->year) == $yr)>{{ $yr }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('month') }}</label>
                    <select name="month" class="form-select">
                        <option value="">{{ db_trans('all_months') }}</option>
                        @foreach(range(1, 12) as $monthNumber)
                            <option value="{{ $monthNumber }}" @selected(($filters['month'] ?? '') == $monthNumber)>
                                {{ \Carbon\Carbon::create(null, $monthNumber, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('kanda') }}</label>
                    <select name="kanda_id" class="form-select">
                        <option value="">{{ db_trans('all_kandas') }}</option>
                        @foreach($kandas as $kanda)
                            <option value="{{ $kanda->id }}" @selected(($filters['kanda_id'] ?? '') == $kanda->id)>
                                {{ $kanda->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('jumuiya') }}</label>
                    <select name="jumuiya_id" class="form-select">
                        <option value="">{{ db_trans('all_jumuiyas') }}</option>
                        @foreach($jumuiyas as $jumuiya)
                            <option value="{{ $jumuiya->id }}" @selected(($filters['jumuiya_id'] ?? '') == $jumuiya->id)>
                                {{ $jumuiya->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn ui-btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>{{ db_trans('apply_filters') }}
                    </button>
                </div>

                <div class="col-md-2">
                    <a href="{{ route('finance.tithes.activity-log') }}" class="btn ui-btn-light w-100">
                        {{ db_trans('reset_filters') }}
                    </a>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="ui-stat-card ui-tone-primary p-4">
                <div class="ui-stat-label">{{ db_trans('total_tithes_this_month') }}</div>
                <div class="ui-stat-value">{{ number_format($stats['month_total'] ?? 0, 2) }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="ui-stat-card ui-tone-success p-4">
                <div class="ui-stat-label">{{ db_trans('total_tithes_this_year') }}</div>
                <div class="ui-stat-value">{{ number_format($stats['year_total'] ?? 0, 2) }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="ui-stat-card ui-tone-info p-4">
                <div class="ui-stat-label">{{ db_trans('records') }}</div>
                <div class="ui-stat-value">{{ number_format($stats['records_count'] ?? 0) }}</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="ui-stat-card ui-tone-warning p-4">
                <div class="ui-stat-label">{{ db_trans('recorders') }}</div>
                <div class="ui-stat-value">{{ number_format($stats['recorders_count'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    <div class="card ui-table-card border-0 mb-4">
        <div class="card-body p-4">
            <h5 class="ui-section-title mb-3">{{ db_trans('monthly_tithe_movement_by_recorder') }}</h5>
            <div style="height: 360px;">
                <canvas id="titheRecorderChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card ui-table-card border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div class="flex-grow-1">
                    @include('admin.finance.tithes.partials.activity-header', [
                        'parishName' => $parishName,
                        'title' => db_trans('tithe_activity_log'),
                        'subtitle' => ($filters['year'] ?? now()->year),
                    ])
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if(Route::has('pdf.finance.tithes.activity-log.export'))
                        <a
                            href="{{ route('pdf.finance.tithes.activity-log.export', request()->query()) }}"
                            target="_blank"
                            class="btn btn-sm btn-danger rounded-pill px-3"
                        >
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if(Route::has('finance.tithes.activity-log.export.excel'))
                        <a
                            href="{{ route('finance.tithes.activity-log.export.excel', request()->query()) }}"
                            class="btn btn-sm btn-success rounded-pill px-3"
                        >
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle" id="titheActivityLogTable">
                    <thead>
                        <tr>
                            <th>{{ db_trans('id') }}</th>
                            <th>{{ db_trans('activity_date') }}</th>
                            <th>{{ db_trans('number_of_records') }}</th>
                            <th>{{ db_trans('recorders') }}</th>
                            <th>{{ db_trans('bulk_batches') }}</th>
                            <th class="text-end">{{ db_trans('amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activityDates as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td data-order="{{ $row->activity_date }}">
                                    <a href="{{ route('finance.tithes.activity-date', ['date' => $row->activity_date]) }}">
                                        {{ \Carbon\Carbon::parse($row->activity_date)->format('Y-m-d') }}
                                    </a>
                                </td>
                                <td>{{ number_format($row->records_count) }}</td>
                                <td>{{ number_format($row->recorders_count) }}</td>
                                <td>{{ number_format($row->batches_count) }}</td>
                                <td class="text-end fw-bold">{{ number_format((float) $row->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">{{ db_trans('no_records_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartEl = document.getElementById('titheRecorderChart');

    if (chartEl && window.Chart) {
        new Chart(chartEl, {
            type: 'line',
            data: {
                labels: @json($monthlyRecorderChart['labels'] ?? []),
                datasets: @json($monthlyRecorderChart['datasets'] ?? []),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom' },
                },
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });
    }

    if (window.jQuery && document.getElementById('titheActivityLogTable')) {
        $('#titheActivityLogTable').DataTable({
            paging: true,
            info: true,
            searching: true,
            ordering: true,
            responsive: true,
            order: [[1, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, @json(db_trans('all'))]],
            language: {
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
            }
        });
    }
});
</script>
@endpush