@extends('layouts.admin')

@section('title', db_trans('teaching_enrollments'))

@section('content')
@php
    $selectedYear = request('year', now()->year);
    $selectedMonth = request('month');

    $yearOptions = collect($chart['labels'] ?? [])
        ->map(fn ($year) => (int) $year)
        ->push((int) now()->year)
        ->filter()
        ->unique()
        ->sortDesc()
        ->values();

    $statusCountKey = function ($value) {
        return match ($value) {
            'continuing', 'active' => 'active_students',
            'completed' => 'completed_students',
            'failed' => 'failed_students',
            'repeated' => 'repeated_students',
            'withdrawn' => 'withdrawn_students',
            default => null,
        };
    };

    $statusDotClass = function ($value) {
        return match ($value) {
            'completed' => 'dot-green',
            'failed' => 'dot-red',
            'repeated' => 'dot-amber',
            'withdrawn' => 'dot-amber',
            default => 'dot-blue',
        };
    };
@endphp

<div class="teaching-page">
    <div class="dashboard-hero teaching-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-xl-7">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="dashboard-hero-badge">{{ db_trans('formation_overview') }}</span>
                    <span class="teaching-chip">
                        <i class="fas fa-graduation-cap me-2"></i>{{ db_trans('teaching_dashboard_summary') }}
                    </span>
                    <span class="teaching-chip soft">{{ $selectedYear }}</span>

                    @if($selectedMonth)
                        <span class="teaching-chip soft">
                            {{ \Carbon\Carbon::create(null, (int) $selectedMonth, 1)->translatedFormat('F') }}
                        </span>
                    @endif
                </div>

                <h1 class="dashboard-title mb-0">{{ db_trans('teaching_enrollments') }}</h1>
            </div>

            <div class="col-xl-5">
                <div class="teaching-quick-grid">
                    <form method="GET" action="{{ url()->current() }}" class="teaching-action-card">
                        <div class="w-100">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label small mb-1">{{ db_trans('year') }}</label>
                                    <select name="year" class="form-select form-select-sm">
                                        @foreach($yearOptions as $yearOption)
                                            <option value="{{ $yearOption }}" @selected((int) $selectedYear === (int) $yearOption)>
                                                {{ $yearOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label small mb-1">{{ db_trans('month') }}</label>
                                    <select name="month" class="form-select form-select-sm">
                                        <option value="">{{ db_trans('all_months') }}</option>
                                        @foreach(range(1, 12) as $monthNumber)
                                            <option value="{{ $monthNumber }}" @selected((string) $selectedMonth === (string) $monthNumber)>
                                                {{ \Carbon\Carbon::create(null, $monthNumber, 1)->translatedFormat('M') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-light btn-sm w-100">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    @foreach($summaryByType as $row)
                        <a href="{{ route('mafundisho.index', ['type' => $row['type'], 'year' => $selectedYear]) }}{{ $selectedMonth ? '?month=' . $selectedMonth : '' }}" class="teaching-action-card">
                            <span class="teaching-action-icon"><i class="fas fa-arrow-trend-up"></i></span>
                            <span>
                                <strong>{{ $row['label'] }}</strong>
                                <span class="d-block">{{ number_format($row['total'] ?? 0) }} {{ db_trans('students') }}</span>
                            </span>
                        </a>
                    @endforeach

                    @can('mafundisho-types.view')
                        <a href="{{ route('mafundisho.types.index') }}" class="teaching-action-card">
                            <span class="teaching-action-icon"><i class="fas fa-list-check"></i></span>
                            <span>
                                <strong>{{ db_trans('teaching_types') }}</strong>
                            </span>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card teaching-kpi-card h-100">
                <div class="card-body">
                    <span class="teaching-kpi-label">{{ db_trans('total_students') }}</span>
                    <div class="teaching-kpi-value">{{ number_format($stats['total_students'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card teaching-kpi-card h-100">
                <div class="card-body">
                    <span class="teaching-kpi-label">{{ $statusLabels['continuing'] ?? db_trans('continuing_students') }}</span>
                    <div class="teaching-kpi-value">{{ number_format($stats['active_students'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card teaching-kpi-card h-100">
                <div class="card-body">
                    <span class="teaching-kpi-label">{{ $statusLabels['completed'] ?? db_trans('graduated_students') }}</span>
                    <div class="teaching-kpi-value">{{ number_format($stats['completed_students'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card teaching-kpi-card h-100">
                <div class="card-body">
                    <span class="teaching-kpi-label">{{ db_trans('eligible_members') }}</span>
                    <div class="teaching-kpi-value">{{ number_format($stats['eligible_members'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card dashboard-panel border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">{{ db_trans('enrollment_trend') }}</h5>
                    </div>

                    <div class="chart-container teaching-chart-shell">
                        <canvas id="mafundishoSummaryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card dashboard-panel border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">{{ db_trans('enrollment_status_breakdown') }}</h5>

                    <div class="chart-container teaching-donut-shell mb-4">
                        <canvas id="mafundishoStatusChart"></canvas>
                    </div>

                    <div class="teaching-status-stack">
                        @foreach($statusLabels as $value => $label)
                            @continue($value === 'active')

                            @php
                                $countKey = $statusCountKey($value);
                                $dotClass = $statusDotClass($value);
                            @endphp

                            <div class="teaching-status-item">
                                <span class="dot {{ $dotClass }}"></span>
                                {{ $label }}
                                <strong>{{ number_format($countKey ? ($stats[$countKey] ?? 0) : 0) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @foreach($summaryByType as $row)
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-panel border-0 shadow-sm h-100 teaching-type-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="teaching-type-title">{{ $row['label'] }}</div>
                                <div class="text-muted small">
                                    {{ db_trans('completion_rate') }}: {{ $row['completion_rate'] ?? 0 }}%
                                </div>
                            </div>

                            <span class="badge admin-badge-primary rounded-pill">
                                {{ number_format($row['eligible'] ?? 0) }} {{ db_trans('eligible') }}
                            </span>
                        </div>

                        <div class="teaching-type-grid mb-3">
                            <div>
                                <span>{{ db_trans('total') }}</span>
                                <strong>{{ number_format($row['total'] ?? 0) }}</strong>
                            </div>

                            <div>
                                <span>{{ $statusLabels['continuing'] ?? db_trans('continuing_students') }}</span>
                                <strong>{{ number_format($row['active'] ?? 0) }}</strong>
                            </div>

                            <div>
                                <span>{{ $statusLabels['completed'] ?? db_trans('graduated_students') }}</span>
                                <strong>{{ number_format($row['completed'] ?? 0) }}</strong>
                            </div>

                            <div>
                                <span>{{ $statusLabels['failed'] ?? db_trans('failed_students') }}</span>
                                <strong>{{ number_format($row['failed'] ?? 0) }}</strong>
                            </div>

                            <div>
                                <span>{{ $statusLabels['repeated'] ?? db_trans('repeating_students') }}</span>
                                <strong>{{ number_format($row['repeated'] ?? 0) }}</strong>
                            </div>

                            <div>
                                <span>{{ $statusLabels['withdrawn'] ?? db_trans('withdrawn_students') }}</span>
                                <strong>{{ number_format($row['withdrawn'] ?? 0) }}</strong>
                            </div>
                        </div>

                        <a href="{{ route('mafundisho.index', ['type' => $row['type'], 'year' => $selectedYear]) }}{{ $selectedMonth ? '?month=' . $selectedMonth : '' }}" class="btn btn-outline-primary rounded-pill px-3">
                            {{ db_trans('open_year_overview') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card dashboard-panel border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center">
                <h5 class="fw-bold text-dark mb-0">{{ db_trans('recent_enrollments') }}</h5>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="mafundishoRecentTable">
                    <thead>
                        <tr>
                            <th>{{ db_trans('member') }}</th>
                            <th>{{ db_trans('teaching_type') }}</th>
                            <th>{{ db_trans('year') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('started_on') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentEnrollments as $enrollment)
                            @php
                                $normalizedStatus = $enrollment->status === 'active' ? 'continuing' : $enrollment->status;

                                $badgeClass = match ($normalizedStatus) {
                                    'completed' => 'admin-badge-success',
                                    'failed' => 'admin-badge-danger',
                                    'repeated' => 'admin-badge-warning',
                                    'withdrawn' => 'admin-badge-warning',
                                    default => 'admin-badge-primary',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $enrollment->member?->full_name ?? '—' }}</div>
                                    <div class="small text-muted">
                                        {{ $enrollment->member?->familia?->name ?? '—' }} ·
                                        {{ $enrollment->member?->familia?->jumuiya?->name ?? '—' }}
                                    </div>
                                </td>

                                <td>{{ $enrollment->teachingType?->name ?? $enrollment->teaching_type_label }}</td>
                                <td>{{ $enrollment->year }}</td>

                                <td>
                                    <span class="badge {{ $badgeClass }} rounded-pill">
                                        {{ $statusLabels[$normalizedStatus] ?? db_trans($normalizedStatus) }}
                                    </span>
                                </td>

                                <td>{{ optional($enrollment->started_at)->format('M d, Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $('#mafundishoRecentTable').length) {
        $('#mafundishoRecentTable').DataTable({
            pageLength: 8,
            order: [[4, 'desc']],
            language: {
                search: '',
                searchPlaceholder: @json(db_trans('search')) + '...'
            }
        });
    }

    const chartEl = document.getElementById('mafundishoSummaryChart');
    if (chartEl && window.Chart) {
        new Chart(chartEl, {
            type: 'bar',
            data: {
                labels: @json($chart['labels'] ?? []),
                datasets: [{
                    label: @json(db_trans('students')),
                    data: @json($chart['data'] ?? []),
                    borderRadius: 14
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    const statusEl = document.getElementById('mafundishoStatusChart');
    if (statusEl && window.Chart) {
        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: @json($statusChart['labels'] ?? []),
                datasets: [{
                    data: @json($statusChart['data'] ?? []),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '68%'
            }
        });
    }
});
</script>
@endpush