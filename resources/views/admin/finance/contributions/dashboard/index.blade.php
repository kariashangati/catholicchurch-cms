@extends('layouts.admin')

@section('title', db_trans('michango_dashboard'))

@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
    @php
        $stats = $stats ?? [];
        $monthly = collect($monthly ?? []);
        $topTypes = collect($topTypes ?? []);
        $recentCash = collect($recentCash ?? []);
        $recentBank = collect($recentBank ?? []);
        $year = $year ?? now()->year;
        $month = $month ?? ($filters['month'] ?? null);

        $cashTotal = (float) ($stats['cash_total'] ?? 0);
        $bankTotal = (float) ($stats['bank_total'] ?? 0);
        $grandTotal = (float) ($stats['grand_total'] ?? 0);
        $cashCount = (int) ($stats['cash_count'] ?? 0);
        $bankCount = (int) ($stats['bank_count'] ?? 0);
        $typeCount = (int) ($stats['type_count'] ?? 0);
        $recordCount = $cashCount + $bankCount;

        $monthlyAverage = (float) ($monthly->count() ? $monthly->avg('total_amount') : 0);
        $bestMonth = $monthly->sortByDesc('total_amount')->first();
        $topType = $topTypes->first();
        $cashShare = $grandTotal > 0 ? ($cashTotal / $grandTotal) * 100 : 0;
        $bankShare = $grandTotal > 0 ? ($bankTotal / $grandTotal) * 100 : 0;

        $monthLabels = $monthly->pluck('label')->values();
        $cashSeries = $monthly->pluck('cash_amount')->map(fn ($value) => (float) $value)->values();
        $bankSeries = $monthly->pluck('bank_amount')->map(fn ($value) => (float) $value)->values();
        $totalSeries = $monthly->pluck('total_amount')->map(fn ($value) => (float) $value)->values();

        $typeLabels = $topTypes->pluck('name')->values();
        $typeSeries = $topTypes->pluck('total_amount')->map(fn ($value) => (float) $value)->values();

        $currency = fn ($amount) => number_format((float) $amount, 2);
        $statusClass = fn ($status) => match((string) $status) {
            \App\Models\CashContribution::STATUS_APPROVED => 'ui-status-approved',
            \App\Models\BankContribution::STATUS_VERIFIED => 'ui-status-approved',
            \App\Models\CashContribution::STATUS_PENDING => 'ui-status-pending',
            \App\Models\BankContribution::STATUS_PENDING => 'ui-status-pending',
            \App\Models\CashContribution::STATUS_REJECTED => 'ui-status-rejected',
            \App\Models\BankContribution::STATUS_REJECTED => 'ui-status-rejected',
            default => 'ui-status-pending',
        };

        $statusLabel = fn ($item) => $item->status_label ?? db_trans($item->status ?? \App\Models\CashContribution::STATUS_PENDING);

        $quickLinks = [
            [
                'label' => db_trans('cash_contributions'),
                'route' => Route::has('finance.contributions.cash.index') ? route('finance.contributions.cash.index') : '#',
                'icon' => 'fas fa-wallet',
            ],
            [
                'label' => db_trans('bank_contributions'),
                'route' => Route::has('finance.contributions.bank.index') ? route('finance.contributions.bank.index') : '#',
                'icon' => 'fas fa-university',
            ],
            [
                'label' => db_trans('contribution_types'),
                'route' => Route::has('finance.contributions.types.index') ? route('finance.contributions.types.index') : '#',
                'icon' => 'fas fa-tags',
            ],
            [
                'label' => db_trans('dashboard'),
                'route' => Route::has('finance.contributions.dashboard') ? route('finance.contributions.dashboard') : '#',
                'icon' => 'fas fa-chart-line',
            ],
        ];
    @endphp

    <div class="admin-ui-v4 contributions-dashboard-v4">
        <div class="ui-page-hero mb-4">
            <div class="ui-hero-pattern"></div>
            <div class="row g-4 align-items-center position-relative">
                <div class="col-xl-8">
                    <span class="ui-page-badge">
                        <i class="fas fa-chart-pie"></i>
                        {{ db_trans('michango_dashboard') }}
                    </span>

                    <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('financial_summary') }}</h1>

                    <div class="ui-meta-wrap mt-3">
                        <span class="ui-meta-pill"><i class="fas fa-calendar-alt"></i>{{ db_trans('year') }}: {{ $year }}</span>
                        @if($month)
                            <span class="ui-meta-pill"><i class="fas fa-calendar-day"></i>{{ db_trans('month') }}: {{ \Carbon\Carbon::create(null, (int) $month, 1)->translatedFormat('F') }}</span>
                        @endif
                        <span class="ui-meta-pill"><i class="fas fa-layer-group"></i>{{ $typeCount }} {{ db_trans('contribution_types') }}</span>
                        <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-receipt"></i>{{ number_format($recordCount) }} {{ db_trans('records') }}</span>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="ui-actions-grid">
                        @foreach($quickLinks as $link)
                            <a href="{{ $link['route'] }}" class="ui-hero-action">
                                <span class="ui-hero-action-icon"><i class="{{ $link['icon'] }}"></i></span>
                                <span class="ui-hero-action-text">{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="card ui-filter-card border-0 mb-4">
            <div class="card-body p-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-xl-3 col-md-4">
                        <label for="contributionDashboardYear" class="form-label">{{ db_trans('year') }}</label>
                        <input id="contributionDashboardYear" type="number" name="year" class="form-control" value="{{ $year }}" min="2020" max="2100">
                    </div>

                    <div class="col-xl-3 col-md-4">
                        <label for="contributionDashboardMonth" class="form-label">{{ db_trans('month') }}</label>
                        <select id="contributionDashboardMonth" name="month" class="form-select">
                            <option value="">{{ db_trans('all_months') }}</option>
                            @foreach(range(1, 12) as $monthNumber)
                                <option value="{{ $monthNumber }}" @selected((int) ($filters['month'] ?? 0) === $monthNumber)>
                                    {{ \Carbon\Carbon::create(null, $monthNumber, 1)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-3 col-md-4">
                        <button class="btn ui-btn-primary w-100" type="submit">
                            <i class="fas fa-filter me-2"></i>{{ db_trans('filter_records') }}
                        </button>
                    </div>

                    <div class="col-xl-3 col-md-4">
                        <a href="{{ route('finance.contributions.dashboard') }}" class="btn ui-btn-light w-100">{{ db_trans('reset') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xxl-2 col-xl-4 col-md-6">
                <div class="ui-stat-card ui-tone-primary p-4 h-100">
                    <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-wallet"></i></span><span class="ui-chip">{{ db_trans('cash') }}</span></div>
                    <div class="ui-stat-label">{{ db_trans('total_cash') }}</div>
                    <div class="ui-stat-value">{{ $currency($cashTotal) }}</div>
                </div>
            </div>

            <div class="col-xxl-2 col-xl-4 col-md-6">
                <div class="ui-stat-card ui-tone-info p-4 h-100">
                    <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-building-columns"></i></span><span class="ui-chip">{{ db_trans('bank') }}</span></div>
                    <div class="ui-stat-label">{{ db_trans('total_bank') }}</div>
                    <div class="ui-stat-value">{{ $currency($bankTotal) }}</div>
                </div>
            </div>

            <div class="col-xxl-2 col-xl-4 col-md-6">
                <div class="ui-stat-card ui-tone-success p-4 h-100">
                    <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-sack-dollar"></i></span><span class="ui-chip">{{ db_trans('total') }}</span></div>
                    <div class="ui-stat-label">{{ db_trans('grand_total') }}</div>
                    <div class="ui-stat-value">{{ $currency($grandTotal) }}</div>
                </div>
            </div>

            <div class="col-xxl-2 col-xl-4 col-md-6">
                <div class="ui-stat-card ui-tone-warning p-4 h-100">
                    <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-tags"></i></span><span class="ui-chip">{{ db_trans('setup') }}</span></div>
                    <div class="ui-stat-label">{{ db_trans('contribution_types') }}</div>
                    <div class="ui-stat-value">{{ number_format($typeCount) }}</div>
                </div>
            </div>

            <div class="col-xxl-2 col-xl-4 col-md-6">
                <div class="ui-stat-card ui-tone-secondary p-4 h-100">
                    <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-chart-column"></i></span><span class="ui-chip">{{ db_trans('average') }}</span></div>
                    <div class="ui-stat-label">{{ db_trans('monthly_average') }}</div>
                    <div class="ui-stat-value">{{ $currency($monthlyAverage) }}</div>
                </div>
            </div>

            <div class="col-xxl-2 col-xl-4 col-md-6">
                <div class="ui-stat-card ui-tone-dark p-4 h-100">
                    <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-trophy"></i></span><span class="ui-chip">{{ db_trans('peak') }}</span></div>
                    <div class="ui-stat-label">{{ db_trans('best_month') }}</div>
                    <div class="ui-stat-value">{{ $bestMonth['label'] ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="ui-mini-card p-4 h-100">
                    <div class="ui-mini-icon ui-mini-tone-primary"><i class="fas fa-percent"></i></div>
                    <div class="ui-stat-label">{{ db_trans('cash_share') }}</div>
                    <div class="ui-stat-value">{{ number_format($cashShare, 1) }}%</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="ui-mini-card p-4 h-100">
                    <div class="ui-mini-icon ui-mini-tone-info"><i class="fas fa-landmark"></i></div>
                    <div class="ui-stat-label">{{ db_trans('bank_share') }}</div>
                    <div class="ui-stat-value">{{ number_format($bankShare, 1) }}%</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="ui-mini-card p-4 h-100">
                    <div class="ui-mini-icon ui-mini-tone-success"><i class="fas fa-award"></i></div>
                    <div class="ui-stat-label">{{ db_trans('top_type') }}</div>
                    <div class="ui-stat-value">{{ \Illuminate\Support\Str::limit(data_get($topType, 'name', '—'), 14) }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="ui-mini-card p-4 h-100">
                    <div class="ui-mini-icon ui-mini-tone-warning"><i class="fas fa-list"></i></div>
                    <div class="ui-stat-label">{{ db_trans('records') }}</div>
                    <div class="ui-stat-value">{{ number_format($recordCount) }}</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="ui-panel p-4 h-100">
                    <div class="ui-panel-head">
                        <div><h5 class="mb-1">{{ db_trans('monthly_contribution_trend') }}</h5></div>
                        <span class="ui-panel-icon"><i class="fas fa-chart-line"></i></span>
                    </div>
                    <div class="ui-chart-shell"><canvas id="contributionTrendChart"></canvas></div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="ui-panel p-4 h-100">
                    <div class="ui-panel-head">
                        <div><h5 class="mb-1">{{ db_trans('top_contribution_types') }}</h5></div>
                        <span class="ui-panel-icon"><i class="fas fa-chart-pie"></i></span>
                    </div>
                    <div class="ui-chart-shell ui-chart-shell-sm"><canvas id="contributionTypeChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="ui-table-card p-4 mb-4">
            <div class="ui-section-heading">
                <div>
                    <h5 class="mb-1">{{ db_trans('recent_cash_contributions') }}</h5>
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="ui-section-badge">{{ $recentCash->count() }} {{ db_trans('records') }}</span>

                    @if(Route::has('pdf.finance.contributions.cash.dashboard.export'))
                        <a href="{{ route('pdf.finance.contributions.cash.dashboard.export', request()->query()) }}" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3">
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if(Route::has('finance.contributions.cash.dashboard.export.excel'))
                        <a href="{{ route('finance.contributions.cash.dashboard.export.excel', request()->query()) }}" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif
                </div>
            </div>

            @if($recentCash->isNotEmpty())
                <div class="table-responsive">
                    <table class="table align-middle" id="recentCashTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ db_trans('member') }}</th>
                                <th>{{ db_trans('contribution_type') }}</th>
                                <th>{{ db_trans('amount') }}</th>
                                <th>{{ db_trans('contribution_date') }}</th>
                                <th>{{ db_trans('status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentCash as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->member->name ?? $item->member->full_name ?? '—' }}</td>
                                    <td>{{ $item->contributionType->name ?? '—' }}</td>
                                    <td class="ui-amount">{{ $currency($item->amount ?? 0) }}</td>
                                    <td>{{ $item->contribution_date ? $item->contribution_date->format('d M Y') : '—' }}</td>
                                    <td><span class="ui-status-pill {{ $statusClass($item->status ?? \App\Models\CashContribution::STATUS_PENDING) }}">{{ $statusLabel($item) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="ui-empty-state">
                    <div class="ui-empty-state-icon"><i class="fas fa-wallet"></i></div>
                    <h6 class="mb-1">{{ db_trans('no_cash_contributions_recorded') }}</h6>
                </div>
            @endif
        </div>

        <div class="ui-table-card p-4">
            <div class="ui-section-heading">
                <div>
                    <h5 class="mb-1">{{ db_trans('recent_bank_contributions') }}</h5>
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="ui-section-badge">{{ $recentBank->count() }} {{ db_trans('records') }}</span>

                    @if(Route::has('pdf.finance.contributions.bank.dashboard.export'))
                        <a href="{{ route('pdf.finance.contributions.bank.dashboard.export', request()->query()) }}" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3">
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if(Route::has('finance.contributions.bank.dashboard.export.excel'))
                        <a href="{{ route('finance.contributions.bank.dashboard.export.excel', request()->query()) }}" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif
                </div>
            </div>

            @if($recentBank->isNotEmpty())
                <div class="table-responsive">
                    <table class="table align-middle" id="recentBankTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ db_trans('member') }}</th>
                                <th>{{ db_trans('contribution_type') }}</th>
                                <th>{{ db_trans('bank_account') }}</th>
                                <th>{{ db_trans('amount') }}</th>
                                <th>{{ db_trans('reference_no') }}</th>
                                <th>{{ db_trans('status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBank as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->member->name ?? $item->member->full_name ?? '—' }}</td>
                                    <td>{{ $item->contributionType->name ?? '—' }}</td>
                                    <td>{{ $item->bankAccount->display_name ?? $item->bankAccount->account_name ?? '—' }}</td>
                                    <td class="ui-amount">{{ $currency($item->amount ?? 0) }}</td>
                                    <td>{{ $item->reference_no ?? '—' }}</td>
                                    <td><span class="ui-status-pill {{ $statusClass($item->status ?? \App\Models\BankContribution::STATUS_PENDING) }}">{{ $statusLabel($item) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="ui-empty-state">
                    <div class="ui-empty-state-icon"><i class="fas fa-building-columns"></i></div>
                    <h6 class="mb-1">{{ db_trans('no_bank_contributions_recorded') }}</h6>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const monthLabels = @json($monthLabels);
            const cashSeries = @json($cashSeries);
            const bankSeries = @json($bankSeries);
            const totalSeries = @json($totalSeries);
            const typeLabels = @json($typeLabels);
            const typeSeries = @json($typeSeries);

            if (typeof Chart !== 'undefined') {
                const trendCanvas = document.getElementById('contributionTrendChart');
                if (trendCanvas) {
                    new Chart(trendCanvas, {
                        type: 'bar',
                        data: {
                            labels: monthLabels,
                            datasets: [
                                { label: @json(db_trans('cash')), data: cashSeries, borderWidth: 2, borderRadius: 8, tension: 0.35, type: 'bar' },
                                { label: @json(db_trans('bank')), data: bankSeries, borderWidth: 2, borderRadius: 8, tension: 0.35, type: 'bar' },
                                { label: @json(db_trans('total')), data: totalSeries, borderWidth: 3, tension: 0.35, type: 'line' }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            scales: { y: { beginAtZero: true } },
                            plugins: { legend: { position: 'bottom' } }
                        }
                    });
                }

                const typeCanvas = document.getElementById('contributionTypeChart');
                if (typeCanvas) {
                    new Chart(typeCanvas, {
                        type: 'doughnut',
                        data: { labels: typeLabels, datasets: [{ data: typeSeries, borderWidth: 2 }] },
                        options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                    });
                }
            }

            if (window.jQuery && $.fn.DataTable) {
                ['#recentCashTable', '#recentBankTable'].forEach(function (selector) {
                    if ($(selector).length) {
                        $(selector).DataTable({
                            paging: true,
                            pageLength: 5,
                            lengthChange: true,
                            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, @json(db_trans('all'))]],
                            responsive: true,
                            order: [],
                            autoWidth: false,
                            language: {
                                search: @json(db_trans('search')) + ':',
                                lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('entries')),
                                info: @json(db_trans('showing')) + ' _START_ ' + @json(db_trans('to')) + ' _END_ ' + @json(db_trans('of')) + ' _TOTAL_ ' + @json(db_trans('entries')),
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