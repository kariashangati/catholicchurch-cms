@extends('layouts.admin')

@section('title', db_trans('waliotoa_wasiotoa'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $filters = $filters ?? [];
    $rows = collect($rows ?? []);
    $stats = $stats ?? [];
    $currency = fn ($amount) => number_format((float) $amount, 2);
    $exportQuery = array_filter($filters, fn ($value) => filled($value) || $value === 0 || $value === '0');
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>

        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge">
                    <i class="fas fa-user-check"></i>
                    {{ db_trans('waliotoa_wasiotoa') }}
                </span>

                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('waliotoa_wasiotoa') }}</h1>

                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill">
                        <i class="fas fa-users"></i>
                        {{ number_format((int) data_get($stats, 'total_members', 0)) }} {{ db_trans('members') }}
                    </span>

                    <span class="ui-meta-pill">
                        <i class="fas fa-coins"></i>
                        {{ $currency(data_get($stats, 'total_amount', 0)) }}
                    </span>

                    <span class="ui-meta-pill ui-meta-pill-warning">
                        <i class="fas fa-layer-group"></i>
                        {{ $selectedDataLabel ?? db_trans('amount') }}
                    </span>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ Route::has('finance.contributions.dashboard') ? route('finance.contributions.dashboard') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <span class="ui-hero-action-text">{{ db_trans('michango_dashboard') }}</span>
                    </a>

                    <a href="{{ Route::has('finance.tithes.dashboard') ? route('finance.tithes.dashboard') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon">
                            <i class="fas fa-coins"></i>
                        </span>
                        <span class="ui-hero-action-text">{{ db_trans('tithes') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('finance.reports.waliotoa.index') }}" class="card ui-filter-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('kanda') }}</label>
                    <select name="kanda_id" id="waliotoaKanda" class="form-select">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach($kandas as $kanda)
                            <option value="{{ $kanda->id }}" @selected((string) ($filters['kanda_id'] ?? '') === (string) $kanda->id)>
                                {{ $kanda->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('jumuiya') }}</label>
                    <select name="jumuiya_id" id="waliotoaJumuiya" class="form-select">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach($jumuiyas as $jumuiya)
                            <option
                                value="{{ $jumuiya->id }}"
                                data-kanda="{{ $jumuiya->kanda_id }}"
                                @selected((string) ($filters['jumuiya_id'] ?? '') === (string) $jumuiya->id)
                            >
                                {{ $jumuiya->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-3 col-md-4">
                    <label class="form-label">{{ db_trans('report_type') }}</label>
                    <select name="data_type" class="form-select" required>
                        @foreach($dataOptions as $option)
                            <option value="{{ $option['value'] }}" @selected(($filters['data_type'] ?? 'tithe') === $option['value'])>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-1 col-md-3">
                    <label class="form-label">{{ db_trans('year') }}</label>
                    <input
                        type="number"
                        name="year"
                        class="form-control"
                        min="2000"
                        max="2100"
                        value="{{ $filters['year'] ?? now()->year }}"
                        required
                    >
                </div>

                <div class="col-xl-2 col-md-3">
                    <label class="form-label">{{ db_trans('month') }}</label>
                    <select name="month" class="form-select">
                        <option value="">{{ db_trans('all_months') }}</option>
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" @selected((string) ($filters['month'] ?? '') === (string) $month)>
                                {{ \Carbon\Carbon::create(null, $month, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('status') }}</label>
                    <select name="giver_status" class="form-select">
                        <option value="all" @selected(($filters['giver_status'] ?? 'all') === 'all')>
                            {{ db_trans('all') }}
                        </option>
                        <option value="waliotoa" @selected(($filters['giver_status'] ?? '') === 'waliotoa')>
                            {{ db_trans('waliotoa') }}
                        </option>
                        <option value="wasiotoa" @selected(($filters['giver_status'] ?? '') === 'wasiotoa')>
                            {{ db_trans('wasiotoa') }}
                        </option>
                    </select>
                </div>

                <div class="col-xl-2 col-md-4 d-flex gap-2">
                    <button class="btn ui-btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>
                        {{ db_trans('filter_records') }}
                    </button>

                    <a href="{{ route('finance.reports.waliotoa.index') }}" class="btn ui-btn-light w-100">
                        {{ db_trans('reset') }}
                    </a>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-tone-primary p-4 h-100">
                <div class="ui-stat-top">
                    <span class="ui-stat-icon">
                        <i class="fas fa-users"></i>
                    </span>
                    <span class="ui-chip">{{ db_trans('total') }}</span>
                </div>

                <div class="ui-stat-label">{{ db_trans('members') }}</div>
                <div class="ui-stat-value">{{ number_format((int) data_get($stats, 'total_members', 0)) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-success">
                    <i class="fas fa-user-check"></i>
                </div>

                <div class="ui-stat-label">{{ db_trans('waliotoa') }}</div>
                <div class="ui-stat-value">{{ number_format((int) data_get($stats, 'givers_count', 0)) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-warning">
                    <i class="fas fa-user-clock"></i>
                </div>

                <div class="ui-stat-label">{{ db_trans('wasiotoa') }}</div>
                <div class="ui-stat-value">{{ number_format((int) data_get($stats, 'non_givers_count', 0)) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-info">
                    <i class="fas fa-coins"></i>
                </div>

                <div class="ui-stat-label">{{ db_trans('total_amount') }}</div>
                <div class="ui-stat-value">{{ $currency(data_get($stats, 'total_amount', 0)) }}</div>
            </div>
        </div>
    </div>

    <div class="ui-table-card p-4">
        <div class="ui-section-heading">
            <div>
                <h5 class="mb-1">{{ db_trans('waliotoa_wasiotoa') }}</h5>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                @if(Route::has('pdf.finance.reports.waliotoa.export'))
                    <a href="{{ route('pdf.finance.reports.waliotoa.export', $exportQuery) }}" target="_blank" class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                    </a>
                @endif

                @if(Route::has('finance.reports.waliotoa.export.excel'))
                    <a href="{{ route('finance.reports.waliotoa.export.excel', $exportQuery) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                    </a>
                @endif

                <span class="ui-section-badge">
                    {{ number_format($rows->count()) }} {{ db_trans('records') }}
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="waliotoaTable">
                <thead>
                    <tr>
                        <th>{{ db_trans('sn') }}</th>
                        <th>{{ db_trans('member') }}</th>
                        <th>{{ db_trans('phone') }}</th>
                        @if($showJumuiyaColumn)
                            <th>{{ db_trans('jumuiya') }}</th>
                        @endif
                        <th>{{ db_trans('date') }}</th>
                        <th>{{ $selectedDataLabel ?? db_trans('amount') }}</th>
                        <th>{{ db_trans('status') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <div class="fw-semibold">{{ $row->member_name }}</div>
                                <small class="text-muted">{{ $row->member->member_code ?? '—' }}</small>
                            </td>

                            <td>{{ $row->phone ?: '—' }}</td>

                            @if($showJumuiyaColumn)
                                <td>{{ $row->jumuiya_name ?: '—' }}</td>
                            @endif

                            <td data-order="{{ $row->last_date ? \Carbon\Carbon::parse($row->last_date)->format('Y-m-d') : '' }}">
                                {{ $row->last_date ? \Carbon\Carbon::parse($row->last_date)->format('d M Y') : '—' }}
                            </td>

                            <td class="ui-amount">{{ $currency($row->amount) }}</td>

                            <td>
                                <span class="ui-status-pill {{ $row->has_given ? 'ui-status-approved' : 'ui-status-pending' }}">
                                    {{ $row->has_given ? db_trans('ametoa') : db_trans('hajatoa') }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="{{ $showJumuiyaColumn ? 5 : 4 }}" class="text-end">
                            {{ db_trans('total_amount') }}
                        </th>
                        <th>{{ $currency(data_get($stats, 'total_amount', 0)) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
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
    const kandaSelect = document.getElementById('waliotoaKanda');
    const jumuiyaSelect = document.getElementById('waliotoaJumuiya');

    const syncJumuiyas = function () {
        if (!kandaSelect || !jumuiyaSelect) {
            return;
        }

        const kanda = kandaSelect.value;

        Array.from(jumuiyaSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = !!kanda && option.dataset.kanda !== kanda;
        });

        const selected = jumuiyaSelect.selectedOptions[0];

        if (selected && selected.hidden) {
            jumuiyaSelect.value = '';
        }
    };

    syncJumuiyas();

    if (kandaSelect) {
        kandaSelect.addEventListener('change', syncJumuiyas);
    }

    if (window.jQuery && $.fn.DataTable && $('#waliotoaTable').length) {
        $('#waliotoaTable').DataTable({
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
                paginate: {
                    previous: '‹',
                    next: '›'
                }
            }
        });
    }
});
</script>
@endpush