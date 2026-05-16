@extends('layouts.admin')

@section('title', $pageTitle)

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/tithes-module-v4.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
@php
    $selectedKanda = (string) ($filters['kanda_id'] ?? '');
    $selectedJumuiya = (string) ($filters['jumuiya_id'] ?? '');
@endphp

<div class="admin-ui-v4 tithe-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-lg-8">
                <span class="ui-page-badge">{{ db_trans('finance') }}</span>
                <h1 class="ui-page-title">{{ $pageTitle }}</h1>
            </div>

            <div class="col-lg-4">
                <div class="ui-actions-grid">
                    <button type="button" class="ui-hero-action border-0" data-bs-toggle="modal" data-bs-target="#createTitheModal">
                        <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('record_tithe') }}</span>
                    </button>

                    <a href="{{ route('finance.tithes.bulk.entry') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-layer-group"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('bulk_tithe_entry') }}</span>
                    </a>

                    @if(\Illuminate\Support\Facades\Route::has('finance.tithes.activity-log'))
                        <a href="{{ route('finance.tithes.activity-log') }}" class="ui-hero-action">
                            <span class="ui-hero-action-icon"><i class="fas fa-chart-line"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('tithe_activity_log') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card ui-filter-card border-0 mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('year') }}</label>
                    <select name="year" class="form-select">
                        @for($yr = now()->year; $yr >= 2020; $yr--)
                            <option value="{{ $yr }}" @selected(($filters['year'] ?? now()->year) == $yr)>
                                {{ $yr }}
                            </option>
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
                    <select name="kanda_id" id="titheFilterKanda" class="form-select">
                        <option value="">{{ db_trans('all_kandas') }}</option>
                        @foreach($kandas as $kanda)
                            <option value="{{ $kanda->id }}" @selected($selectedKanda === (string) $kanda->id)>
                                {{ $kanda->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('jumuiya') }}</label>
                    <select name="jumuiya_id" id="titheFilterJumuiya" class="form-select" @disabled(empty($selectedKanda))>
                        <option value="">{{ empty($selectedKanda) ? db_trans('select_kanda') : db_trans('all_jumuiyas') }}</option>
                        @foreach($jumuiyas as $jumuiya)
                            <option
                                value="{{ $jumuiya->id }}"
                                data-kanda-id="{{ $jumuiya->kanda_id }}"
                                @selected($selectedJumuiya === (string) $jumuiya->id)>
                                {{ $jumuiya->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ db_trans('all_statuses') }}</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') == $status)>
                                {{ db_trans($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn ui-btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>{{ db_trans('apply_filters') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card ui-table-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="ui-section-heading">
                <div>
                    <h5 class="ui-section-title">{{ db_trans('monthly_tithe_movement') }}</h5>
                </div>
            </div>

            <div style="height: 320px;">
                <canvas id="titheMonthlyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card ui-table-card border-0">
        <div class="card-body p-4">
            <div class="ui-section-heading">
                <div>
                    <h5 class="ui-section-title">{{ db_trans('tithes') }}</h5>
                    <p class="ui-section-subtitle">{{ db_trans('grand_total') }}: {{ number_format($total, 2) }}</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if(Route::has('pdf.finance.tithes.export'))
                        <a
                            href="{{ route('pdf.finance.tithes.export', request()->query()) }}"
                            target="_blank"
                            class="btn btn-sm btn-danger rounded-pill px-3"
                        >
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if(Route::has('finance.tithes.export.excel'))
                        <a
                            href="{{ route('finance.tithes.export.excel', request()->query()) }}"
                            class="btn btn-sm btn-success rounded-pill px-3"
                        >
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="tithesTable">
                    <thead>
                        <tr>
                            <th>{{ db_trans('date') }}</th>
                            <th>{{ db_trans('member') }}</th>
                            <th>{{ db_trans('phone') }}</th>
                            <th>{{ db_trans('jumuiya') }}</th>
                            <th>{{ db_trans('amount') }}</th>
                            <th>{{ db_trans('payment_method') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td data-order="{{ optional($item->contribution_date)->format('Y-m-d') }}">
                                    {{ optional($item->contribution_date)->format('d/m/Y') }}
                                </td>
                                <td>{{ $item->member?->full_name ?? '—' }}</td>
                                <td>{{ $item->member?->phone ?: '—' }}</td>
                                <td>{{ $item->jumuiya?->name ?: '—' }}</td>
                                <td>{{ number_format((float) $item->amount, 2) }}</td>
                                <td>{{ $item->payment_method_label }}</td>
                                <td>
                                    <span class="ui-status-pill ui-status-{{ $item->status }}">
                                        {{ $item->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm ui-btn-light px-3" data-bs-toggle="modal" data-bs-target="#editTitheModal-{{ $item->id }}">
                                            {{ db_trans('edit') }}
                                        </button>

                                        <form method="POST" action="{{ route('finance.tithes.destroy', $item) }}" class="delete-tithe-form d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm ui-btn-danger px-3">
                                                {{ db_trans('delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-none">{{ $items->links() }}</div>
        </div>
    </div>
</div>

<div class="modal fade" id="createTitheModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content ui-modal-card admin-ui-v4">
            <form method="POST" action="{{ route('finance.tithes.store') }}">
                @csrf

                <div class="modal-header ui-modal-header">
                    <h5 class="ui-modal-title">{{ db_trans('record_tithe') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @include('admin.finance.tithes.partials.single-form', [
                        'tithe' => null,
                        'members' => $members,
                        'jumuiyas' => $jumuiyas,
                        'kandas' => $kandas,
                        'statuses' => $statuses,
                        'paymentMethods' => $paymentMethods,
                    ])
                </div>

                <div class="modal-footer ui-modal-footer">
                    <button type="button" class="btn ui-btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn ui-btn-primary">{{ db_trans('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($items as $item)
    <div class="modal fade" id="editTitheModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content ui-modal-card admin-ui-v4">
                <form method="POST" action="{{ route('finance.tithes.update', $item) }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-header ui-modal-header">
                        <h5 class="ui-modal-title">{{ db_trans('edit_tithe') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        @include('admin.finance.tithes.partials.single-form', [
                            'tithe' => $item,
                            'members' => $members,
                            'jumuiyas' => $jumuiyas,
                            'kandas' => $kandas,
                            'statuses' => $statuses,
                            'paymentMethods' => $paymentMethods,
                        ])
                    </div>

                    <div class="modal-footer ui-modal-footer">
                        <button type="button" class="btn ui-btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                        <button type="submit" class="btn ui-btn-primary">{{ db_trans('save_changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const kandaSelect = document.getElementById('titheFilterKanda');
    const jumuiyaSelect = document.getElementById('titheFilterJumuiya');

    function refreshFilterJumuiyas() {
        if (!kandaSelect || !jumuiyaSelect) {
            return;
        }

        const kandaId = kandaSelect.value;

        jumuiyaSelect.disabled = !kandaId;

        Array.from(jumuiyaSelect.options).forEach(function (option) {
            if (!option.value) {
                option.textContent = kandaId
                    ? @json(db_trans('all_jumuiyas'))
                    : @json(db_trans('select_kanda'));
                option.hidden = false;
                return;
            }

            option.hidden = !kandaId || option.dataset.kandaId !== kandaId;
        });

        const selectedOption = jumuiyaSelect.options[jumuiyaSelect.selectedIndex];

        if (selectedOption && selectedOption.hidden) {
            jumuiyaSelect.value = '';
        }
    }

    refreshFilterJumuiyas();

    kandaSelect?.addEventListener('change', function () {
        jumuiyaSelect.value = '';
        refreshFilterJumuiyas();
    });

    const chartEl = document.getElementById('titheMonthlyChart');

    if (chartEl && window.Chart) {
        new Chart(chartEl, {
            type: 'line',
            data: {
                labels: @json($monthlyChart['labels'] ?? []),
                datasets: [{
                    label: @json(db_trans('amount')),
                    data: @json($monthlyChart['amounts'] ?? []),
                    borderWidth: 3,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    if (window.jQuery && document.getElementById('tithesTable')) {
        $('#tithesTable').DataTable({
            paging: true,
            info: true,
            searching: true,
            ordering: true,
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, @json(db_trans('all'))]],
            language: {
                search: '',
                searchPlaceholder: @json(db_trans('search')) + '...',
                lengthMenu: '_MENU_',
                info: @json(db_trans('showing')) + ' _START_ - _END_ / _TOTAL_',
                paginate: {
                    previous: @json(db_trans('previous')),
                    next: @json(db_trans('next'))
                }
            }
        });
    }

    document.querySelectorAll('.delete-tithe-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: @json(db_trans('are_you_sure')),
                text: @json(db_trans('tithe_delete_confirmation')),
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: @json(db_trans('delete')),
                cancelButtonText: @json(db_trans('cancel')),
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    @if(request('open_create'))
        const modalEl = document.getElementById('createTitheModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    @endif
});
</script>
@endpush