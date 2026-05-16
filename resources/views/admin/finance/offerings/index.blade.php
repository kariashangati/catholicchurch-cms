@extends('layouts.admin')

@section('title', db_trans('offerings'))

@section('disable_default_alerts')@endsection

@section('content')
    @php
        $stats = $stats ?? [];
        $scopeFieldLabel = match($filters['collection_scope'] ?? '') {
            'parish' => db_trans('parish'),
            'kanda' => db_trans('kanda'),
            'jumuiya' => db_trans('jumuiya'),
            default => db_trans('collection_scope'),
        };
        $statusClass = fn ($status) => match($status) {
            'imeidhinishwa', 'approved' => 'ui-status-approved',
            'inasubiri', 'pending' => 'ui-status-pending',
            'imekataliwa', 'rejected' => 'ui-status-rejected',
            default => '',
        };
        $statusLabel = fn ($status) => match($status) {
            'imeidhinishwa', 'approved' => db_trans('approved'),
            'inasubiri', 'pending' => db_trans('pending'),
            'imekataliwa', 'rejected' => db_trans('rejected'),
            default => db_trans($status) ?: ucfirst((string) $status),
        };
        $paymentLabel = fn ($payment) => match($payment) {
            'taslimu', 'cash' => db_trans('cash'),
            'benki', 'bank' => db_trans('bank'),
            'simu', 'mobile_money' => db_trans('mobile_money'),
            'nyingine', 'other' => db_trans('other'),
            default => $payment ? (db_trans($payment) ?: ucfirst(str_replace('_', ' ', $payment))) : '—',
        };
        $reopenContext = old('form_context');
    @endphp

    <div class="admin-ui-v4 offerings-module-v4">
        <div class="ui-page-hero mb-4">
            <div class="ui-hero-pattern"></div>
            <div class="row g-4 align-items-center position-relative">
                <div class="col-xl-8">
                    <span class="ui-page-badge">{{ db_trans('manage_offerings') }}</span>
                    <h1 class="ui-page-title">{{ db_trans('offerings') }}</h1>

                    <div class="ui-meta-wrap mt-3">
                        <span class="ui-meta-pill"><i class="fas fa-calendar-alt"></i>{{ $filters['year'] ?? now()->year }}</span>
                        <span class="ui-meta-pill"><i class="fas fa-coins"></i>{{ number_format($stats['year_total'] ?? 0, 2) }}</span>
                        <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-clock"></i>{{ $stats['pending_count'] ?? 0 }} {{ db_trans('pending_finance_records') }}</span>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="ui-actions-grid">
                        @canany(['finance.create', 'finance.offerings.create'])
                            <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createOfferingModal">
                                <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                                <span class="ui-hero-action-text">{{ db_trans('add_offering') }}</span>
                            </button>
                        @endcanany

                        <form method="GET" action="{{ route('finance.offerings.index') }}" class="ui-hero-action d-block">
                            <div class="row g-2">
                                <div class="col-6">
                                    <select name="year" class="form-select form-select-sm">
                                        @for($yr = now()->year; $yr >= 2020; $yr--)
                                            <option value="{{ $yr }}" @selected(($filters['year'] ?? now()->year) == $yr)>{{ $yr }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-6">
                                    <select name="month" class="form-select form-select-sm">
                                        <option value="">{{ db_trans('all_months') }}</option>
                                        @foreach(range(1, 12) as $monthNumber)
                                            <option value="{{ $monthNumber }}" @selected(($filters['month'] ?? '') == $monthNumber)>
                                                {{ \Carbon\Carbon::create()->month($monthNumber)->translatedFormat('M') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6">
                                    <select name="kanda_id" class="form-select form-select-sm">
                                        <option value="">{{ db_trans('all_kandas') }}</option>
                                        @foreach($kandas as $kanda)
                                            <option value="{{ $kanda->id }}" @selected(($filters['kanda_id'] ?? '') == $kanda->id)>{{ $kanda->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6">
                                    <select name="jumuiya_id" class="form-select form-select-sm">
                                        <option value="">{{ db_trans('all_jumuiyas') }}</option>
                                        @foreach($jumuiyas as $jumuiya)
                                            <option value="{{ $jumuiya->id }}" @selected(($filters['jumuiya_id'] ?? '') == $jumuiya->id)>{{ $jumuiya->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-light btn-sm w-100">
                                        <i class="fas fa-filter me-1"></i>{{ db_trans('filter_records') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="ui-stat-card ui-tone-primary p-4">
                    <div class="ui-stat-top">
                        <span class="ui-stat-icon"><i class="fas fa-sack-dollar"></i></span>
                        <span class="ui-chip">{{ db_trans('overview') }}</span>
                    </div>
                    <div class="ui-stat-label">{{ db_trans('offerings_this_year') }}</div>
                    <div class="ui-stat-value">{{ number_format($stats['year_total'] ?? 0, 2) }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="ui-stat-card ui-tone-success p-4">
                    <div class="ui-stat-top">
                        <span class="ui-stat-icon"><i class="fas fa-circle-check"></i></span>
                        <span class="ui-chip">{{ db_trans('status') }}</span>
                    </div>
                    <div class="ui-stat-label">{{ db_trans('approved_total') }}</div>
                    <div class="ui-stat-value">{{ number_format($stats['approved_total'] ?? 0, 2) }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="ui-stat-card ui-tone-warning p-4">
                    <div class="ui-stat-top">
                        <span class="ui-stat-icon"><i class="fas fa-hourglass-half"></i></span>
                        <span class="ui-chip">{{ db_trans('status') }}</span>
                    </div>
                    <div class="ui-stat-label">{{ db_trans('pending_total') }}</div>
                    <div class="ui-stat-value">{{ number_format($stats['pending_total'] ?? 0, 2) }}</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="ui-stat-card ui-tone-info p-4">
                    <div class="ui-stat-top">
                        <span class="ui-stat-icon"><i class="fas fa-arrows-spin"></i></span>
                        <span class="ui-chip">{{ db_trans('overview') }}</span>
                    </div>
                    <div class="ui-stat-label">{{ db_trans('average_record_value') }}</div>
                    <div class="ui-stat-value">{{ number_format($stats['average_record'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-4">
                <div class="ui-mini-card p-4">
                    <span class="ui-mini-icon ui-mini-tone-primary"><i class="fas fa-church"></i></span>
                    <div class="ui-mini-label">{{ db_trans('parish') }}</div>
                    <div class="ui-mini-value">{{ number_format($scopeChart['amounts'][0] ?? 0, 2) }}</div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-mini-card p-4">
                    <span class="ui-mini-icon ui-mini-tone-success"><i class="fas fa-map-marked-alt"></i></span>
                    <div class="ui-mini-label">{{ db_trans('kanda') }}</div>
                    <div class="ui-mini-value">{{ number_format($scopeChart['amounts'][1] ?? 0, 2) }}</div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-mini-card p-4">
                    <span class="ui-mini-icon ui-mini-tone-info"><i class="fas fa-layer-group"></i></span>
                    <div class="ui-mini-label">{{ db_trans('jumuiya') }}</div>
                    <div class="ui-mini-value">{{ number_format($scopeChart['amounts'][2] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="ui-panel p-4 h-100">
                    <div class="ui-panel-head">
                        <div>
                            <h5 class="ui-panel-title">{{ db_trans('monthly_offering_movement') }}</h5>
                            <p class="ui-panel-subtitle">{{ db_trans('monthly_offering_performance') }}</p>
                        </div>
                        <span class="ui-panel-icon"><i class="fas fa-chart-column"></i></span>
                    </div>
                    <div class="ui-chart-shell ui-chart-shell-lg">
                        <canvas id="offeringMonthlyChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-panel p-4 h-100">
                    <div class="ui-panel-head">
                        <div>
                            <h5 class="ui-panel-title">{{ db_trans('offering_breakdown') }}</h5>
                            <p class="ui-panel-subtitle">{{ db_trans('how_income_streams_compare_this_year') }}</p>
                        </div>
                        <span class="ui-panel-icon"><i class="fas fa-chart-donut"></i></span>
                    </div>
                    <div class="ui-chart-shell">
                        <canvas id="offeringTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <form method="GET" class="ui-filter-card p-4 mb-4" id="offeringsTableCard">
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
                                {{ \Carbon\Carbon::create()->month($monthNumber)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ db_trans('offering_type') }}</label>
                    <select name="offering_type_id" class="form-select">
                        <option value="">{{ db_trans('all_types') }}</option>
                        @foreach($offeringTypes as $type)
                            <option value="{{ $type->id }}" @selected(($filters['offering_type_id'] ?? '') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('mass_type') }}</label>
                    <select name="mass_type_id" class="form-select">
                        <option value="">{{ db_trans('all_mass_types') }}</option>
                        @foreach($massTypes as $massType)
                            <option value="{{ $massType->id }}" @selected(($filters['mass_type_id'] ?? '') == $massType->id)>{{ $massType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ db_trans('collection_scope') }}</label>
                    <select name="collection_scope" class="form-select">
                        <option value="">{{ db_trans('all_scopes') }}</option>
                        @foreach($scopes as $scope)
                            <option value="{{ $scope }}" @selected(($filters['collection_scope'] ?? '') === $scope)>{{ db_trans($scope) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('kanda') }}</label>
                    <select name="kanda_id" class="form-select">
                        <option value="">{{ db_trans('all_kandas') }}</option>
                        @foreach($kandas as $kanda)
                            <option value="{{ $kanda->id }}" @selected(($filters['kanda_id'] ?? '') == $kanda->id)>{{ $kanda->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('jumuiya') }}</label>
                    <select name="jumuiya_id" class="form-select">
                        <option value="">{{ db_trans('all_jumuiyas') }}</option>
                        @foreach($jumuiyas as $jumuiya)
                            <option value="{{ $jumuiya->id }}" @selected(($filters['jumuiya_id'] ?? '') == $jumuiya->id)>{{ $jumuiya->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ db_trans('status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ db_trans('all_statuses') }}</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $statusLabel($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="ui-btn-primary w-100"><i class="fas fa-filter me-2"></i>{{ db_trans('filter_records') }}</button>
                    <a href="{{ route('finance.offerings.index') }}" class="ui-btn-light w-100 text-center">{{ db_trans('reset_filters') }}</a>
                </div>
            </div>
        </form>

        <div class="ui-table-card p-0 overflow-hidden">
            <div class="p-4 border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h5 class="ui-panel-title mb-1">{{ db_trans('manage_offerings') }}</h5>
                        <p class="ui-panel-subtitle mb-0">{{ db_trans('grand_total') }}: {{ number_format($total, 2) }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="ui-inline-badge">{{ db_trans('records') }}: {{ $stats['records_count'] ?? 0 }}</span>
                        <span class="ui-inline-badge">{{ $scopeFieldLabel }}</span>

                        @if(Route::has('pdf.finance.offerings.export'))
                            <a
                                href="{{ route('pdf.finance.offerings.export', request()->query()) }}"
                                target="_blank"
                                class="btn btn-sm btn-danger rounded-pill px-3"
                            >
                                <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                            </a>
                        @endif

                        @if(Route::has('finance.offerings.export.excel'))
                            <a
                                href="{{ route('finance.offerings.export.excel', request()->query()) }}"
                                class="btn btn-sm btn-success rounded-pill px-3"
                            >
                                <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="table-responsive p-3 pt-0">
                <table class="table align-middle offerings-table mb-0" id="offeringsDataTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ db_trans('offering_type') }}</th>
                            <th>{{ db_trans('mass_type') }}</th>
                            <th>{{ db_trans('collection_scope') }}</th>
                            <th>{{ db_trans('location') }}</th>
                            <th>{{ db_trans('amount') }}</th>
                            <th>{{ db_trans('payment_method') }}</th>
                            <th>{{ db_trans('collection_date') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $items->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->offeringType?->name ?? '—' }}</div>
                                    <div class="small text-muted">{{ $item->receipt_no ?: '—' }}</div>
                                </td>
                                <td>{{ $item->massType?->name ?? '—' }}</td>
                                <td><span class="ui-status-pill">{{ $item->scope_label }}</span></td>
                                <td>{{ $item->location_name }}</td>
                                <td class="fw-bold text-nowrap">{{ number_format((float) $item->amount, 2) }}</td>
                                <td>{{ $paymentLabel($item->payment_method) }}</td>
                                <td>{{ optional($item->collection_date)->format('M d, Y') }}</td>
                                <td><span class="ui-status-pill {{ $statusClass($item->status) }}">{{ $statusLabel($item->status) }}</span></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2 justify-content-end offerings-actions">
                                        @canany(['finance.update', 'finance.offerings.update'])
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editOfferingModal{{ $item->id }}">
                                                <i class="fas fa-pen me-1"></i>{{ db_trans('edit') }}
                                            </button>
                                        @endcanany

                                        @canany(['finance.delete', 'finance.offerings.delete'])
                                            <form method="POST" action="{{ route('finance.offerings.destroy', $item) }}" class="js-confirm-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    <i class="fas fa-trash me-1"></i>{{ db_trans('delete') }}
                                                </button>
                                            </form>
                                        @endcanany
                                    </div>
                                  </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-5">
                                    <div class="ui-empty-state">
                                        <div class="ui-empty-icon"><i class="fas fa-inbox"></i></div>
                                        {{ db_trans('no_records_found') }}
                                    </div>
                                  </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top bg-white">
                {{ $items->links() }}
            </div>
        </div>

        @foreach($items as $item)
            @canany(['finance.update', 'finance.offerings.update'])
                @include('admin.finance.offerings.partials.offering-modal', [
                    'modalId' => 'editOfferingModal'.$item->id,
                    'title' => db_trans('edit_offering'),
                    'action' => route('finance.offerings.update', $item),
                    'method' => 'PUT',
                    'item' => $item,
                    'context' => 'edit-'.$item->id,
                ])
            @endcanany
        @endforeach

        @canany(['finance.create', 'finance.offerings.create'])
            @include('admin.finance.offerings.partials.offering-modal', [
                'modalId' => 'createOfferingModal',
                'title' => db_trans('add_offering'),
                'action' => route('finance.offerings.store'),
                'method' => 'POST',
                'item' => null,
                'context' => 'create',
            ])
        @endcanany
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const monthlyCtx = document.getElementById('offeringMonthlyChart');
            if (monthlyCtx) {
                new Chart(monthlyCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($monthlyChart['labels'] ?? []),
                        datasets: [{
                            label: @json(db_trans('offerings')),
                            data: @json($monthlyChart['amounts'] ?? []),
                            backgroundColor: 'rgba(59, 130, 246, 0.75)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 10,
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }

            const typeCtx = document.getElementById('offeringTypeChart');
            if (typeCtx) {
                new Chart(typeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($typeChart['labels'] ?? []),
                        datasets: [{
                            data: @json($typeChart['amounts'] ?? []),
                            backgroundColor: [
                                'rgba(59,130,246,0.9)',
                                'rgba(34,197,94,0.9)',
                                'rgba(124,58,237,0.9)',
                                'rgba(245,158,11,0.9)',
                                'rgba(20,184,166,0.9)',
                                'rgba(239,68,68,0.9)'
                            ]
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }

            if (window.jQuery && document.getElementById('offeringsDataTable')) {
                $('#offeringsDataTable').DataTable({
                    paging: true,
                    searching: true,
                    info: true,
                    ordering: true,
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    language: {
                        search: '',
                        searchPlaceholder: @json(db_trans('member_name_or_code')),
                        lengthMenu: '_MENU_',
                    },
                    dom: '<"offerings-dt-toolbar"lf>rt<"d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"ip>'
                });
            }

            function toggleScopeFields(modal) {
                const select = modal.querySelector('.offering-scope-select');
                if (!select) return;

                modal.querySelectorAll('.scope-block').forEach(el => el.classList.add('d-none'));

                if (select.value === 'kanda') {
                    modal.querySelectorAll('.scope-kanda').forEach(el => el.classList.remove('d-none'));
                }

                if (select.value === 'jumuiya') {
                    modal.querySelectorAll('.scope-jumuiya').forEach(el => el.classList.remove('d-none'));
                }
            }

            document.querySelectorAll('.offering-modal').forEach(function (modalEl) {
                toggleScopeFields(modalEl);

                modalEl.addEventListener('change', function (event) {
                    if (event.target.classList.contains('offering-scope-select')) {
                        toggleScopeFields(modalEl);
                    }
                });

                modalEl.addEventListener('shown.bs.modal', function () {
                    toggleScopeFields(modalEl);
                });
            });

            document.querySelectorAll('.js-confirm-delete').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: @json(db_trans('are_you_sure')),
                        text: @json(db_trans('confirm_delete_record')),
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#7c3aed',
                        confirmButtonText: @json(db_trans('delete')),
                        cancelButtonText: @json(db_trans('cancel')),
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            const reopenContext = @json($reopenContext);
            if (reopenContext) {
                const targetId = reopenContext === 'create'
                    ? 'createOfferingModal'
                    : 'editOfferingModal' + reopenContext.replace('edit-', '');

                const modalNode = document.getElementById(targetId);
                if (modalNode && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(modalNode).show();
                }
            }
        });
    </script>
@endpush