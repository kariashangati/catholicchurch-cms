@extends('layouts.admin')

@section('title', db_trans('kandas'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/kanda-v3.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
    <div class="kanda-v3">
        <div class="dashboard-hero mb-4">
            <div class="hero-pattern"></div>
            <div class="row align-items-center g-4 position-relative">
                <div class="col-lg-8">
                    <span class="dashboard-hero-badge">{{ $hero['eyebrow'] }}</span>
                    <h2 class="dashboard-title mb-2">{{ $hero['title'] }}</h2>
                    <p class="dashboard-subtitle mb-3">{{ $hero['today'] }}</p>

                    <div class="hero-meta-wrap">
                
                    </div>
                </div>
<div class="col-lg-4">
    <form method="GET"
          action="{{ route('kandas.index') }}"
          class="hero-filter-card bg-white bg-opacity-10 rounded-4 p-2">
        <div class="row g-2 align-items-end">
            <div class="col-6">
                <label class="form-label small mb-1 text-white">{{ db_trans('from_date') }}</label>
                <input type="date"
                       name="from_date"
                       class="form-control form-control-sm"
                       value="{{ request('from_date') }}">
            </div>

            <div class="col-6">
                <label class="form-label small mb-1 text-white">{{ db_trans('to_date') }}</label>
                <input type="date"
                       name="to_date"
                       class="form-control form-control-sm"
                       value="{{ request('to_date') }}">
            </div>

            <div class="col-8">
                <button type="submit" class="btn btn-light btn-sm w-100">
                    <i class="fas fa-filter me-1"></i>{{ db_trans('apply_filters') }}
                </button>
            </div>

            <div class="col-4">
                <a href="{{ route('kandas.index') }}" class="btn btn-outline-light btn-sm w-100">
                    {{ db_trans('reset') }}
                </a>
            </div>
        </div>
    </form>
</div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="row g-4 mb-4">
            @foreach($summaryCards as $card)
                @if($loop->iteration <= 4)
                    <div class="col-xxl-2 col-xl-4 col-md-6">
                        @if(!empty($card['route']))
                            <a href="{{ $card['route'] }}" class="text-decoration-none text-reset d-block h-100">
                        @endif
                        <div class="card stat-card stat-card-{{ $card['tone'] }} h-100 border-0">
                            <div class="card-body">
                                <div class="stat-top-row">
                                    <div class="stat-icon"><i class="{{ $card['icon'] }}"></i></div>
                                </div>
                                <div class="stat-label">{{ $card['title'] }}</div>
                                <div class="stat-number">{{ $card['value'] }}</div>
                            </div>
                        </div>
                        @if(!empty($card['route']))</a>@endif
                    </div>
                @endif
            @endforeach

            <div class="col-xxl-2 col-xl-4 col-md-6">
                <a href="{{ route('finance.dashboard') }}" class="text-decoration-none text-reset d-block h-100">
                    <div class="card stat-card stat-card-dark h-100 border-0">
                        <div class="card-body">
                            <div class="stat-top-row">
                                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                            </div>
                            <div class="stat-label">{{ db_trans('grand_total') }}</div>
                            <div class="stat-number">{{ number_format((float) ($stats['grand_total'] ?? 0), 2) }}</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <a href="{{ route('finance.dashboard') }}" class="text-decoration-none text-reset d-block h-100">
                    <div class="card analytics-panel border-0 h-100">
                        <div class="card-body p-4">
                            <div class="panel-head">
                                <div><h5 class="panel-title">{{ db_trans('finance_by_kanda') }}</h5></div>
                                <div class="panel-icon"><i class="fas fa-chart-bar"></i></div>
                            </div>
                            <div class="chart-shell"><canvas id="kandaFinanceChart"></canvas></div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-6">
                <div class="card analytics-panel border-0 h-100">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div><h5 class="panel-title">{{ db_trans('membership_and_structure_mix') }}</h5></div>
                            <div class="panel-icon"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="chart-shell"><canvas id="kandaMembersChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

      <div class="card table-panel border-0">
    <div class="card-header table-panel-header border-0 p-4">
        <!-- Updated block: title and form with Excel button -->
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-end gap-3">
            <div>
                <h5 class="panel-title mb-1">{{ db_trans('kanda_and_jumuiya_breakdown') }}</h5>
            </div>

            <form action="{{ route('pdf.kanda-reports.breakdown') }}"
                  method="GET"
                  target="_blank"
                  class="d-flex flex-column flex-md-row align-items-md-end gap-2">
                <div>
                    <label for="breakdown_from_date" class="form-label mb-1 small">
                        {{ db_trans('from_date') }}
                    </label>
                    <input
                        type="date"
                        id="breakdown_from_date"
                        name="from_date"
                        class="form-control form-control-sm"
                        value="{{ request('from_date') }}">
                </div>

                <div>
                    <label for="breakdown_to_date" class="form-label mb-1 small">
                        {{ db_trans('to_date') }}
                    </label>
                    <input
                        type="date"
                        id="breakdown_to_date"
                        name="to_date"
                        class="form-control form-control-sm"
                        value="{{ request('to_date') }}">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                    </button>

                    <a href="{{ route('kandas.export.excel', request()->only(['from_date', 'to_date'])) }}"
                       class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

            <div class="card-body p-0">
                @if($breakdownSummaryRows->isNotEmpty())
                    <div class="table-responsive dashboard-table-wrap border-bottom">
                        <!-- First table: dynamic columns after offerings and tithes -->
                        <table id="kandaBreakdownSummaryTable" class="table align-middle table-hover mb-0 dashboard-table js-breakdown-table">
                            <thead>
                                <tr>
                                    <th>{{ db_trans('kanda') }}</th>
                                    <th>{{ db_trans('jumuiyas') }}</th>
                                    <th>{{ db_trans('familias') }}</th>
                                    <th>{{ db_trans('members') }}</th>
                                    <th>{{ db_trans('offerings') }}</th>
                                    <th>{{ db_trans('tithes') }}</th>

                                    @foreach($breakdownContributionTypes as $type)
                                        <th>{{ $type['name'] }}</th>
                                    @endforeach

                                    <th>{{ db_trans('grand_total') }}</th>
                                    <th>{{ db_trans('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($breakdownSummaryRows as $row)
                                    <tr>
                                        <td class="fw-semibold">{{ $row['name'] }}</td>
                                        <td>{{ number_format($row['jumuiyas_count']) }}</td>
                                        <td>{{ number_format($row['familias_count']) }}</td>
                                        <td>{{ number_format($row['members_count']) }}</td>
                                        <td>{{ number_format($row['offering_total'], 2) }}</td>
                                        <td>{{ number_format($row['tithe_total'], 2) }}</td>

                                        @foreach($breakdownContributionTypes as $type)
                                            <td>
                                                {{ number_format($row['contribution_type_totals'][$type['slug']]['amount'] ?? 0, 2) }}
                                            </td>
                                        @endforeach

                                        <td class="fw-bold text-success">{{ number_format($row['grand_total'], 2) }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ $row['details_url'] }}" class="btn btn-sm btn-outline-primary btn-modern">
                                                    {{ db_trans('open') }}
                                                </a>
                                                <a href="{{ $row['report_url'] }}" class="btn btn-sm btn-outline-secondary btn-modern">
                                                    {{ db_trans('report') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="p-4 border-bottom bg-white">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-6">
                            <input
                                type="text"
                                class="form-control"
                                id="kandaBreakdownSearch"
                                placeholder="{{ db_trans('search_kanda_or_jumuiya') }}">
                        </div>
                    </div>
                </div>

                <div class="accordion accordion-flush dashboard-accordion" id="kandaBreakdownAccordion">
                    @forelse($kandaBreakdown as $index => $kanda)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-{{ $kanda['id'] }}">
                                <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse-{{ $kanda['id'] }}"
                                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                        aria-controls="collapse-{{ $kanda['id'] }}">
                                    <div class="w-100 pe-3">
                                        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                                            <div>
                                                <div class="accordion-kanda-title">{{ $kanda['name'] }}</div>
                                                <div class="accordion-kanda-meta">
                                                    {{ $kanda['jumuiya_count'] }} {{ db_trans('jumuiyas') }} ·
                                                    {{ $kanda['familias_count'] }} {{ db_trans('familias') }} ·
                                                    {{ $kanda['members_count'] }} {{ db_trans('members') }}
                                                </div>
                                            </div>
                                            <!-- Dynamic badges -->
                                            <div class="accordion-badges">
                                                <span class="soft-badge">{{ db_trans('offerings') }}: {{ number_format($kanda['offering_total'], 2) }}</span>
                                                <span class="soft-badge">{{ db_trans('tithes') }}: {{ number_format($kanda['tithe_total'], 2) }}</span>

                                                @foreach($breakdownContributionTypes as $type)
                                                    <span class="soft-badge">
                                                        {{ $type['name'] }}: {{ number_format($kanda['contribution_type_totals'][$type['slug']]['amount'] ?? 0, 2) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </h2>

                            <div id="collapse-{{ $kanda['id'] }}"
                                 class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                 aria-labelledby="heading-{{ $kanda['id'] }}"
                                 data-bs-parent="#kandaBreakdownAccordion">
                                <div class="accordion-body p-0">
                                    <div class="accordion-toolbar">
                                        <a href="{{ $kanda['details_url'] }}" class="btn btn-sm btn-outline-primary btn-modern">
                                            <i class="fas fa-eye me-1"></i>{{ db_trans('view_kanda') }}
                                        </a>
                                        <a href="{{ $kanda['report_url'] }}" class="btn btn-sm btn-outline-secondary btn-modern">
                                            <i class="fas fa-chart-column me-1"></i>{{ db_trans('view_report') }}
                                        </a>
                                    </div>

                                    <div class="table-responsive dashboard-table-wrap">
                                        <!-- Second table: dynamic columns after offerings and tithes -->
                                        <table class="table align-middle table-hover mb-0 dashboard-table js-breakdown-table">
                                            <thead>
                                                <tr>
                                                    <th>{{ db_trans('jumuiya') }}</th>
                                                    <th>{{ db_trans('familias') }}</th>
                                                    <th>{{ db_trans('members') }}</th>
                                                    <th>{{ db_trans('offerings') }}</th>
                                                    <th>{{ db_trans('tithes') }}</th>

                                                    @foreach($breakdownContributionTypes as $type)
                                                        <th>{{ $type['name'] }}</th>
                                                    @endforeach

                                                    <th>{{ db_trans('grand_total') }}</th>
                                                    <th>{{ db_trans('actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($kanda['jumuiyas'] as $jumuiya)
                                                    <tr>
                                                        <td class="fw-semibold">{{ $jumuiya['name'] }}</td>
                                                        <td>{{ number_format($jumuiya['familias_count']) }}</td>
                                                        <td>{{ number_format($jumuiya['members_count']) }}</td>
                                                        <td>{{ number_format($jumuiya['offering_total'], 2) }}</td>
                                                        <td>{{ number_format($jumuiya['tithe_total'], 2) }}</td>

                                                        @foreach($breakdownContributionTypes as $type)
                                                            <td>
                                                                {{ number_format($jumuiya['contribution_type_totals'][$type['slug']]['amount'] ?? 0, 2) }}
                                                            </td>
                                                        @endforeach

                                                        <td class="fw-bold text-success">{{ number_format($jumuiya['grand_total'], 2) }}</td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <a href="{{ $jumuiya['details_url'] }}" class="btn btn-sm btn-outline-primary btn-modern">
                                                                    {{ db_trans('open') }}
                                                                </a>
                                                                <a href="{{ $jumuiya['report_url'] }}" class="btn btn-sm btn-outline-secondary btn-modern">
                                                                    {{ db_trans('report') }}
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="{{ 7 + $breakdownContributionTypes->count() }}" class="text-center py-4 text-muted">
                                                            {{ db_trans('no_jumuiyas_found') }}
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-muted">
                            {{ db_trans('no_kandas_found') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
window.kandaDashboardData = {
    monthlyFinanceChart: @json($monthlyFinanceChart),
    financeByKandaChart: @json($financeByKandaChart),
    chartData: @json($chartData),
    financeMixChart: @json($financeMixChart),
    contributionTypeChart: @json($contributionTypeChart),
    labels: {
        tithes: @json(db_trans('tithes')),
        offerings: @json(db_trans('offerings')),
        cash: @json(db_trans('cash_contributions')),
        bank: @json(db_trans('bank_contributions')),
        members: @json(db_trans('members')),
        familias: @json(db_trans('familias')),
        jumuiyas: @json(db_trans('jumuiyas')),
        grand_total: @json(db_trans('grand_total')),
        finance: @json(db_trans('finance')),
        overview: @json(db_trans('overview')),
        pending_items: @json(db_trans('pending_items')),
        contribution_types: @json(db_trans('contribution_types'))
    }
};
</script>
<script src="{{ asset('admin/js/kanda-v3.js') }}"></script>
@endpush