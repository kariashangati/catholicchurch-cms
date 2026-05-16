@extends('layouts.admin')

@section('title', db_trans('bank_accounts'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $bankAccounts = collect($bankAccounts ?? []);
    $statuses = $statuses ?? \App\Models\BankAccount::statuses();
    $activeCount = $bankAccounts->where('is_active', true)->count();
    $inactiveCount = $bankAccounts->where('is_active', false)->count();
    $closedCount = $bankAccounts->filter(fn ($account) => \App\Models\BankAccount::normalizeStatus($account->status ?? null) === \App\Models\BankAccount::STATUS_CLOSED)->count();
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge">
                    <i class="fas fa-building-columns"></i>
                    {{ db_trans('bank_accounts') }}
                </span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('bank_accounts') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-list"></i>{{ number_format($bankAccounts->count()) }} {{ db_trans('records') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-circle-check"></i>{{ number_format($activeCount) }} {{ db_trans('active') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-lock"></i>{{ number_format($closedCount) }} {{ db_trans('imefungwa') }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createBankAccountModal">
                        <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('add_bank_account') }}</span>
                    </button>
                    <a href="{{ Route::has('finance.contributions.bank.index') ? route('finance.contributions.bank.index') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-university"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('bank_contributions') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ db_trans('close') }}"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ db_trans('close') }}"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">{{ db_trans('please_fix_the_following_errors') }}</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-tone-primary p-4 h-100">
                <div class="ui-stat-top"><span class="ui-stat-icon"><i class="fas fa-building-columns"></i></span><span class="ui-chip">{{ db_trans('total') }}</span></div>
                <div class="ui-stat-label">{{ db_trans('bank_accounts') }}</div>
                <div class="ui-stat-value">{{ number_format($bankAccounts->count()) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-success"><i class="fas fa-circle-check"></i></div>
                <div class="ui-stat-label">{{ db_trans('active') }}</div>
                <div class="ui-stat-value">{{ number_format($activeCount) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-warning"><i class="fas fa-pause-circle"></i></div>
                <div class="ui-stat-label">{{ db_trans('inactive') }}</div>
                <div class="ui-stat-value">{{ number_format($inactiveCount) }}</div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-danger"><i class="fas fa-lock"></i></div>
                <div class="ui-stat-label">{{ db_trans('imefungwa') }}</div>
                <div class="ui-stat-value">{{ number_format($closedCount) }}</div>
            </div>
        </div>
    </div>

    <div class="ui-table-card p-4">
        <div class="ui-section-heading d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">{{ db_trans('bank_accounts') }}</h5>
                <span class="ui-section-badge">{{ number_format($bankAccounts->count()) }} {{ db_trans('records') }}</span>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('pdf.finance.bank-accounts.export') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                </a>

                <a href="{{ route('finance.bank-accounts.export.excel') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                </a>
            </div>
        </div>

        @if($bankAccounts->isNotEmpty())
            <div class="table-responsive">
                <table class="table align-middle" id="bankAccountsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ db_trans('bank_name') }}</th>
                            <th>{{ db_trans('account_name') }}</th>
                            <th>{{ db_trans('account_number') }}</th>
                            <th>{{ db_trans('branch_name') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('active') }}</th>
                            <th>{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bankAccounts as $bankAccount)
                            @php
                                $normalizedStatus = \App\Models\BankAccount::normalizeStatus($bankAccount->status ?? null);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $bankAccount->bank_name }}</td>
                                <td>{{ $bankAccount->account_name }}</td>
                                <td>{{ $bankAccount->account_number }}</td>
                                <td>{{ $bankAccount->branch_name ?: '—' }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $bankAccount->status_badge_class }}">
                                        {{ db_trans($normalizedStatus) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $bankAccount->is_active ? 'success' : 'secondary' }}">
                                        {{ $bankAccount->is_active ? db_trans('yes') : db_trans('no') }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBankAccountModal{{ $bankAccount->id }}">
                                        <i class="fas fa-pen me-1"></i>{{ db_trans('edit') }}
                                    </button>

                                    <form action="{{ route('finance.bank-accounts.destroy', $bankAccount) }}" method="POST" class="d-inline js-swal-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash me-1"></i>{{ db_trans('delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="ui-empty-state">
                <div class="ui-empty-state-icon"><i class="fas fa-building-columns"></i></div>
                <h6 class="mb-1">{{ db_trans('no_bank_accounts_found') }}</h6>
            </div>
        @endif
    </div>
</div>

@include('admin.finance.bank-accounts.modal', [
    'modalId' => 'createBankAccountModal',
    'action' => route('finance.bank-accounts.store'),
    'method' => 'POST',
    'bankAccount' => null,
    'statuses' => $statuses,
    'title' => db_trans('add_bank_account'),
])

@foreach($bankAccounts as $bankAccount)
    @include('admin.finance.bank-accounts.modal', [
        'modalId' => 'editBankAccountModal' . $bankAccount->id,
        'action' => route('finance.bank-accounts.update', $bankAccount),
        'method' => 'PUT',
        'bankAccount' => $bankAccount,
        'statuses' => $statuses,
        'title' => db_trans('edit_bank_account'),
    ])
@endforeach
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && $.fn.DataTable && $('#bankAccountsTable').length) {
                $('#bankAccountsTable').DataTable({
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

            document.querySelectorAll('.js-swal-delete').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    if (typeof Swal === 'undefined') {
                        if (confirm(@json(db_trans('confirm_delete_bank_account')))) {
                            form.submit();
                        }
                        return;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: @json(db_trans('are_you_sure')),
                        text: @json(db_trans('confirm_delete_bank_account')),
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
