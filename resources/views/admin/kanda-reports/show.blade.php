@extends('layouts.admin')

@section('title', db_trans('kanda_financial_report'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/kanda-report-v3.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $jumuiyas = collect($jumuiyas ?? []);
    $contributionTypes = collect($contributionTypes ?? []);
    $money = fn ($value) => number_format((float) $value, 2);
    $exportQuery = array_filter([
        'year' => $selectedYear ?? null,
        'month' => $selectedMonth ?? null,
    ], fn ($value) => filled($value));
@endphp

<div class="kanda-report-v3">
    <div class="dashboard-hero mb-4">
        <div class="hero-pattern"></div>
        <div class="row align-items-center g-4 position-relative">
            <div class="col-lg-8">
                <span class="dashboard-hero-badge">{{ $hero['eyebrow'] }}</span>
                <h2 class="dashboard-title mb-2">{{ $hero['title'] }}</h2>
                <div class="hero-meta-wrap">
                    <span class="hero-pill"><i class="fas fa-compass me-2"></i>{{ $hero['scope_label'] }}</span>
                    <span class="hero-pill"><i class="fas fa-calendar-alt me-2"></i>{{ $hero['period'] }}</span>
                    <span class="hero-pill"><i class="fas fa-code me-2"></i>{{ $kanda->code }}</span>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="hero-actions-grid">
                    <a href="{{ route('kanda-reports.index', ['year' => $selectedYear, 'month' => $selectedMonth]) }}" class="hero-action-btn">
                        <span class="hero-action-icon"><i class="fas fa-arrow-left"></i></span>
                        <span class="hero-action-text">{{ db_trans('back') }}</span>
                    </a>
                    <a href="{{ route('kandas.show', $kanda) }}" class="hero-action-btn">
                        <span class="hero-action-icon"><i class="fas fa-eye"></i></span>
                        <span class="hero-action-text">{{ db_trans('view_kanda') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card filter-panel border-0 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('kanda-reports.show', $kanda) }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ db_trans('year') }}</label>
                        <select name="year" class="form-select modern-input">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" @selected((int)$selectedYear === (int)$year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">{{ db_trans('month') }}</label>
                        <select name="month" class="form-select modern-input">
                            <option value="">{{ db_trans('all_months') }}</option>
                            @foreach(range(1, 12) as $month)
                                <option value="{{ $month }}" @selected((int)$selectedMonth === (int)$month)>
                                    {{ \Carbon\Carbon::create(null, $month, 1)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-grid">
                        <button type="submit" class="btn btn-modern-primary">
                            <i class="fas fa-filter me-2"></i>{{ db_trans('apply_filters') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @foreach($summaryCards as $card)
            <div class="col-xl-4 col-md-6">
                <div class="card stat-card stat-card-{{ $card['tone'] }} h-100 border-0">
                    <div class="card-body">
                        <div class="stat-top-row">
                            <div class="stat-icon"><i class="{{ $card['icon'] }}"></i></div>
                            <span class="stat-chip">{{ db_trans('overview') }}</span>
                        </div>
                        <div class="stat-label">{{ $card['title'] }}</div>
                        <div class="stat-number">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        @foreach($financeCards as $card)
            <div class="col-xl-4 col-md-6">
                <div class="card finance-card finance-card-{{ $card['tone'] }} h-100 border-0">
                    <div class="card-body">
                        <div class="finance-card-top">
                            <div class="finance-card-icon"><i class="{{ $card['icon'] }}"></i></div>
                            <span class="finance-chip">{{ db_trans('finance') }}</span>
                        </div>
                        <div class="finance-card-title">{{ $card['title'] }}</div>
                        <div class="finance-card-value">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card analytics-panel border-0">
                <div class="card-body p-4">
                    <div class="panel-head">
                        <div><h5 class="panel-title">{{ db_trans('monthly_finance_trends') }}</h5></div>
                        <div class="panel-icon"><i class="fas fa-chart-line"></i></div>
                    </div>
                    <div class="chart-shell chart-shell-lg">
                        <canvas id="kandaMonthlyFinanceTrend"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card dashboard-side-panel border-0 h-100">
                <div class="card-body p-4">
                    <div class="panel-head mb-4"><div><h5 class="panel-title">{{ db_trans('income_mix') }}</h5></div></div>
                    <div class="chart-shell">
                        <canvas id="kandaShowCompositionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card table-panel border-0">
        <div class="card-header table-panel-header border-0 p-4">
            <div>
                <h5 class="panel-title mb-1">{{ db_trans('jumuiya_breakdown') }}</h5>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('pdf.kanda-reports.show.export', array_merge(['kanda' => $kanda->id], $exportQuery)) }}" class="btn btn-sm btn-outline-danger btn-modern">
                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                </a>

                <a href="{{ route('kanda-reports.show.export.excel', array_merge(['kanda' => $kanda->id], $exportQuery)) }}" class="btn btn-sm btn-outline-success btn-modern">
                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive dashboard-table-wrap">
                <table class="table align-middle table-hover mb-0 dashboard-table" id="kandaShowJumuiyaTable">
                    <thead>
                        <tr>
                            <th>{{ db_trans('jumuiya') }}</th>
                            <th>{{ db_trans('familias') }}</th>
                            <th>{{ db_trans('members') }}</th>
                            <th>{{ db_trans('tithes') }}</th>
                            <th>{{ db_trans('offerings') }}</th>
                            @foreach($contributionTypes as $type)
                                <th>{{ $type->name }}</th>
                            @endforeach
                            <th>{{ db_trans('grand_total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jumuiyas as $jumuiya)
                            <tr>
                                <td>
                                    <div class="name-block">
                                        <div class="name-main">{{ $jumuiya['name'] }}</div>
                                        <div class="name-sub">{{ $jumuiya['code'] }}</div>
                                    </div>
                                </td>
                                <td>{{ number_format($jumuiya['familias_count']) }}</td>
                                <td>{{ number_format($jumuiya['members_count']) }}</td>
                                <td>{{ $money($jumuiya['tithe_total']) }}</td>
                                <td>{{ $money($jumuiya['offering_total']) }}</td>
                                @foreach($contributionTypes as $type)
                                    <td>{{ $money(data_get($jumuiya, 'contribution_type_totals.' . $type->id, 0)) }}</td>
                                @endforeach
                                <td class="fw-bold text-success">{{ $money($jumuiya['grand_total']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 6 + $contributionTypes->count() }}" class="text-center py-5 text-muted">{{ db_trans('no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td>{{ db_trans('grand_total') }}</td>
                            <td>{{ number_format($jumuiyas->sum('familias_count')) }}</td>
                            <td>{{ number_format($jumuiyas->sum('members_count')) }}</td>
                            <td>{{ $money($jumuiyas->sum('tithe_total')) }}</td>
                            <td>{{ $money($jumuiyas->sum('offering_total')) }}</td>
                            @foreach($contributionTypes as $type)
                                <td>{{ $money($jumuiyas->sum(fn ($row) => (float) data_get($row, 'contribution_type_totals.' . $type->id, 0))) }}</td>
                            @endforeach
                            <td class="text-success">{{ $money($jumuiyas->sum('grand_total')) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
window.kandaReportV3Show = {
    monthlyTrends: @json($monthlyTrends),
    compositionChart: @json($compositionChart),
    labels: {
        tithes: @json(db_trans('tithes')),
        offerings: @json(db_trans('offerings')),
    }
};
</script>
<script src="{{ asset('admin/js/kanda-report-v3.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.DataTable && $('#kandaShowJumuiyaTable').length) {
        $('#kandaShowJumuiyaTable').DataTable({
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