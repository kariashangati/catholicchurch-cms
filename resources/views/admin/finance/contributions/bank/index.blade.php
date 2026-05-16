@extends('layouts.admin')

@section('title', db_trans('bank_contributions'))

@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
    @php
        $items = collect($items ?? []);
        $members = collect($members ?? []);
        $types = collect($types ?? []);
        $bankAccounts = collect($bankAccounts ?? []);
        $statuses = $statuses ?? \App\Models\BankContribution::availableStatuses();
        $filters = $filters ?? [];

        $currency = fn ($amount) => number_format((float) $amount, 2);

        $totalAmount = (float) $items->sum('amount');
        $verifiedCount = $items->where('status', \App\Models\BankContribution::STATUS_VERIFIED)->count();
        $pendingCount = $items->where('status', \App\Models\BankContribution::STATUS_PENDING)->count();
        $rejectedCount = $items->where('status', \App\Models\BankContribution::STATUS_REJECTED)->count();

        $statusClass = fn ($status) => match((string) $status) {
            \App\Models\BankContribution::STATUS_VERIFIED => 'ui-status-approved',
            \App\Models\BankContribution::STATUS_PENDING => 'ui-status-pending',
            \App\Models\BankContribution::STATUS_REJECTED => 'ui-status-rejected',
            default => 'ui-status-pending',
        };
    @endphp

    <div class="admin-ui-v4 contributions-page-v4">
        <div class="ui-page-hero mb-4">
            <div class="ui-hero-pattern"></div>

            <div class="row g-4 align-items-center position-relative">
                <div class="col-xl-8">
                    <span class="ui-page-badge">
                        <i class="fas fa-university"></i>
                        {{ db_trans('bank_contributions') }}
                    </span>

                    <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('bank_contributions') }}</h1>

                    <div class="ui-meta-wrap mt-3">
                        <span class="ui-meta-pill">
                            <i class="fas fa-list"></i>
                            {{ number_format($items->count()) }} {{ db_trans('records') }}
                        </span>

                        <span class="ui-meta-pill">
                            <i class="fas fa-building-columns"></i>
                            {{ number_format($bankAccounts->count()) }} {{ db_trans('bank_accounts') }}
                        </span>

                        <span class="ui-meta-pill ui-meta-pill-warning">
                            <i class="fas fa-tag"></i>
                            {{ number_format($types->count()) }} {{ db_trans('contribution_types') }}
                        </span>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="ui-actions-grid">
                        @can('finance.contributions.bank.create')
                            <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createBankModal">
                                <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                                <span class="ui-hero-action-text">{{ db_trans('new_bank_contribution') }}</span>
                            </button>
                        @endcan

                        <a href="{{ Route::has('finance.contributions.dashboard') ? route('finance.contributions.dashboard') : '#' }}" class="ui-hero-action">
                            <span class="ui-hero-action-icon"><i class="fas fa-chart-line"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('dashboard') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card ui-filter-card border-0 mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('finance.contributions.bank.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-xl-2 col-md-4">
                            <label class="form-label">{{ db_trans('year') }}</label>
                            <input type="number" name="year" min="2020" max="2100" class="form-control" value="{{ $filters['year'] ?? now()->year }}">
                        </div>

                        <div class="col-xl-2 col-md-4">
                            <label class="form-label">{{ db_trans('month') }}</label>
                            <select name="month" class="form-select">
                                <option value="">{{ db_trans('all_months') }}</option>
                                @foreach(range(1, 12) as $monthNumber)
                                    <option value="{{ $monthNumber }}" @selected((string) ($filters['month'] ?? '') === (string) $monthNumber)>
                                        {{ \Carbon\Carbon::create(null, $monthNumber, 1)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-4">
                            <label class="form-label">{{ db_trans('contribution_type') }}</label>
                            <select name="contribution_type_id" class="form-select">
                                <option value="">{{ db_trans('all') }}</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" @selected((string) ($filters['contribution_type_id'] ?? '') === (string) $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="status" class="form-select">
                                <option value="">{{ db_trans('all_statuses') }}</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected((string) ($filters['status'] ?? '') === (string) $status)>
                                        {{ db_trans($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-md-6 d-flex gap-2">
                            <button type="submit" class="btn ui-btn-primary w-100">
                                <i class="fas fa-filter me-1"></i>{{ db_trans('filter_records') }}
                            </button>

                            <a href="{{ route('finance.contributions.bank.index') }}" class="btn ui-btn-light w-100">
                                {{ db_trans('reset') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="ui-stat-card ui-tone-info p-4 h-100">
                    <div class="ui-stat-top">
                        <span class="ui-stat-icon"><i class="fas fa-money-check-dollar"></i></span>
                        <span class="ui-chip">{{ db_trans('total') }}</span>
                    </div>
                    <div class="ui-stat-label">{{ db_trans('total_bank') }}</div>
                    <div class="ui-stat-value">{{ $currency($totalAmount) }}</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="ui-mini-card p-4 h-100">
                    <div class="ui-mini-icon ui-mini-tone-success"><i class="fas fa-badge-check"></i></div>
                    <div class="ui-stat-label">{{ db_trans('imethibitishwa') }}</div>
                    <div class="ui-stat-value">{{ number_format($verifiedCount) }}</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="ui-mini-card p-4 h-100">
                    <div class="ui-mini-icon ui-mini-tone-warning"><i class="fas fa-clock"></i></div>
                    <div class="ui-stat-label">{{ db_trans('inasubiri') }}</div>
                    <div class="ui-stat-value">{{ number_format($pendingCount) }}</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="ui-mini-card p-4 h-100">
                    <div class="ui-mini-icon ui-mini-tone-danger"><i class="fas fa-ban"></i></div>
                    <div class="ui-stat-label">{{ db_trans('imekataliwa') }}</div>
                    <div class="ui-stat-value">{{ number_format($rejectedCount) }}</div>
                </div>
            </div>
        </div>

        <div class="ui-table-card p-4">
     <div class="ui-section-heading">
    <div>
        <h5 class="mb-1">{{ db_trans('bank_contributions') }}</h5>
    </div>

    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="ui-section-badge">{{ number_format($items->count()) }} {{ db_trans('records') }}</span>

        @if(Route::has('pdf.finance.contributions.bank.export'))
            <a href="{{ route('pdf.finance.contributions.bank.export', request()->query()) }}" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3">
                <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
            </a>
        @endif

        @if(Route::has('finance.contributions.bank.export.excel'))
            <a href="{{ route('finance.contributions.bank.export.excel', request()->query()) }}" class="btn btn-sm btn-success rounded-pill px-3">
                <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
            </a>
        @endif
    </div>
</div>

            @if($items->isNotEmpty())
                <div class="table-responsive">
                    <table class="table align-middle" id="bankContributionTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ db_trans('member') }}</th>
                                <th>{{ db_trans('contribution_type') }}</th>
                                <th>{{ db_trans('bank_account') }}</th>
                                <th>{{ db_trans('amount') }}</th>
                                <th>{{ db_trans('contribution_date') }}</th>
                                <th>{{ db_trans('reference_no') }}</th>
                                <th>{{ db_trans('status') }}</th>
                                <th>{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <div class="ui-member-block">
                                            <span class="fw-semibold">{{ $item->member->name ?? $item->member->full_name ?? '—' }}</span>
                                        </div>
                                    </td>

                                    <td>{{ $item->contributionType->name ?? '—' }}</td>
                                    <td>{{ $item->bankAccount->display_name ?? $item->bankAccount->account_name ?? '—' }}</td>
                                    <td class="ui-amount">{{ $currency($item->amount ?? 0) }}</td>
                                    <td data-order="{{ optional($item->contribution_date)->format('Y-m-d') }}">
                                        {{ $item->contribution_date ? $item->contribution_date->format('d M Y') : '—' }}
                                    </td>
                                    <td>{{ $item->reference_no ?? '—' }}</td>

                                    <td>
                                        <span class="ui-status-pill {{ $statusClass($item->status ?? \App\Models\BankContribution::STATUS_PENDING) }}">
                                            {{ db_trans($item->status ?? \App\Models\BankContribution::STATUS_PENDING) }}
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        @can('finance.contributions.bank.update')
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBankModal{{ $item->id }}">
                                                <i class="fas fa-pen me-1"></i>{{ db_trans('edit') }}
                                            </button>
                                        @endcan

                                        @can('finance.contributions.bank.delete')
                                            <form method="POST" action="{{ route('finance.contributions.bank.destroy', $item) }}" class="d-inline js-swal-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash me-1"></i>{{ db_trans('delete') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
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

        @foreach($items as $item)
            @include('admin.finance.contributions.partials.bank-modal', [
                'modalId' => 'editBankModal' . $item->id,
                'title' => db_trans('edit') . ' ' . db_trans('bank_contributions'),
                'action' => route('finance.contributions.bank.update', $item),
                'method' => 'PUT',
                'item' => $item,
                'members' => $members,
                'types' => $types,
                'bankAccounts' => $bankAccounts,
            ])
        @endforeach
    </div>

    @include('admin.finance.contributions.partials.bank-modal', [
        'modalId' => 'createBankModal',
        'title' => db_trans('new_bank_contribution'),
        'action' => route('finance.contributions.bank.store'),
        'method' => 'POST',
        'item' => null,
        'members' => $members,
        'types' => $types,
        'bankAccounts' => $bankAccounts,
    ])
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && $.fn.DataTable && $('#bankContributionTable').length) {
                $('#bankContributionTable').DataTable({
                    paging: true,
                    info: true,
                    searching: true,
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, @json(db_trans('all'))]],
                    order: [[5, 'desc']],
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

            document.querySelectorAll('.js-swal-delete').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: @json(db_trans('are_you_sure')),
                        text: @json(db_trans('this_action_cannot_be_undone')),
                        showCancelButton: true,
                        confirmButtonColor: '#7c3aed',
                        confirmButtonText: @json(db_trans('delete')),
                        cancelButtonText: @json(db_trans('cancel'))
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush