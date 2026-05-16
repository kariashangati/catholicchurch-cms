@extends('layouts.admin')

@section('title', db_trans('project_finance'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/tithes-module-v4.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $filters = $filters ?? [];
    $projects = collect($projects ?? []);
    $categories = collect($categories ?? []);
    $transactions = $transactions ?? null;
    $transactionRows = collect($transactions?->items() ?? []);
    $currency = fn ($value) => number_format((float) $value, 2);
    $projectStatuses = $projectStatuses ?? \App\Models\Project::availableStatuses();
    $transactionStatuses = $transactionStatuses ?? \App\Models\ProjectTransaction::availableStatuses();
    $transactionTypes = $transactionTypes ?? \App\Models\ProjectTransaction::availableTypes();
    $paymentMethods = $paymentMethods ?? \App\Models\ProjectTransaction::availablePaymentMethods();
@endphp

<div class="admin-ui-v4 project-finance-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-briefcase"></i>{{ db_trans('project_finance') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('project_finance') }}</h1>

                <div class="ui-meta-wrap mt-4">
                    <span class="ui-meta-pill"><i class="fas fa-folder-tree"></i>{{ number_format($categories->count()) }} {{ db_trans('project_categories') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-diagram-project"></i>{{ number_format($projects->count()) }} {{ db_trans('projects') }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-receipt"></i>{{ number_format($transactionRows->count()) }} {{ db_trans('project_transactions') }}</span>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <button type="button" class="ui-hero-action border-0" data-bs-toggle="modal" data-bs-target="#categoryCreateModal">
                        <span class="ui-hero-action-icon"><i class="fas fa-tags"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('add_project_category') }}</span>
                    </button>

                    <button type="button" class="ui-hero-action border-0" data-bs-toggle="modal" data-bs-target="#projectCreateModal" id="project-create-modal">
                        <span class="ui-hero-action-icon"><i class="fas fa-folder-plus"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('add_project') }}</span>
                    </button>

                    <button type="button" class="ui-hero-action border-0" data-bs-toggle="modal" data-bs-target="#transactionCreateModal" id="transaction-create-modal">
                        <span class="ui-hero-action-icon"><i class="fas fa-money-bill-transfer"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('add_project_transaction') }}</span>
                    </button>

                    <a href="{{ route('finance.projects.dashboard') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('project_finance_dashboard') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card ui-filter-card border-0 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('finance.projects.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-xl-2 col-md-4">
                        <label class="form-label">{{ db_trans('year') }}</label>
                        <input type="number" min="2000" max="2100" class="form-control" name="year" value="{{ $filters['year'] ?? now()->year }}">
                    </div>

                    <div class="col-xl-2 col-md-4">
                        <label class="form-label">{{ db_trans('month') }}</label>
                        <select class="form-select" name="month">
                            <option value="">{{ db_trans('all_months') }}</option>
                            @foreach(range(1,12) as $month)
                                <option value="{{ $month }}" @selected((int) ($filters['month'] ?? 0) === $month)>{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-3 col-md-4">
                        <label class="form-label">{{ db_trans('project_category') }}</label>
                        <select class="form-select" name="project_category_id">
                            <option value="">{{ db_trans('all') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) ($filters['project_category_id'] ?? 0) === (int) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <label class="form-label">{{ db_trans('project_status') }}</label>
                        <select class="form-select" name="project_status">
                            <option value="">{{ db_trans('all_statuses') }}</option>
                            @foreach($projectStatuses as $status)
                                <option value="{{ $status }}" @selected(($filters['project_status'] ?? '') === $status)>{{ db_trans($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-6 d-flex gap-2">
                        <button type="submit" class="btn ui-btn-primary w-100">{{ db_trans('apply_filters') }}</button>
                        <a href="{{ route('finance.projects.index') }}" class="btn ui-btn-light w-100">{{ db_trans('reset') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @foreach($categories as $category)
            <div class="col-xl-3 col-md-6">
                <div class="card ui-mini-card border-0 h-100">
                    <div class="card-body">
                        <div class="ui-mini-icon ui-mini-tone-primary"><i class="fas fa-layer-group"></i></div>
                        <div class="ui-mini-label">{{ $category->name }}</div>
                        <div class="ui-mini-value">{{ number_format((int) ($category->projects_count ?? 0)) }}</div>
                        <div class="ui-mini-note">{{ db_trans('projects') }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card ui-table-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="ui-section-heading">
                <div>
                    <h5 class="ui-section-title">{{ db_trans('project_summary') }}</h5>
                    <p class="ui-section-subtitle">{{ db_trans('project_finance_overview') }}</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if(Route::has('pdf.finance.projects.export'))
                        <a href="{{ route('pdf.finance.projects.export', request()->query()) }}" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3">
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if(Route::has('finance.projects.export.excel'))
                        <a href="{{ route('finance.projects.export.excel', request()->query()) }}" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="projectsTable">
                    <thead>
                        <tr>
                            <th>{{ db_trans('project') }}</th>
                            <th>{{ db_trans('project_category') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('budget_amount') }}</th>
                            <th>{{ db_trans('target_amount') }}</th>
                            <th>{{ db_trans('income') }}</th>
                            <th>{{ db_trans('expense') }}</th>
                            <th>{{ db_trans('balance') }}</th>
                            <th>{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($projects as $project)
                            @php
                                $income = (float) ($project->total_income ?? 0);
                                $expense = (float) ($project->total_expense ?? 0);
                                $balance = $income - $expense;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $project->name }}</div>
                                    <small class="text-muted">{{ optional($project->start_date)->format('d M Y') ?? '—' }} @if($project->end_date) · {{ optional($project->end_date)->format('d M Y') }} @endif</small>
                                </td>
                                <td>{{ $project->category?->name ?? '—' }}</td>
                                <td><span class="ui-status-pill ui-status-{{ strtolower($project->status) }}">{{ $project->status_label }}</span></td>
                                <td>{{ $currency($project->budget_amount) }}</td>
                                <td>{{ $currency($project->target_amount) }}</td>
                                <td class="ui-text-success fw-semibold">{{ $currency($income) }}</td>
                                <td class="ui-text-danger fw-semibold">{{ $currency($expense) }}</td>
                                <td class="fw-bold {{ $balance >= 0 ? 'ui-text-success' : 'ui-text-danger' }}">{{ $currency($balance) }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-sm ui-btn-light" data-bs-toggle="modal" data-bs-target="#projectEditModal{{ $project->id }}">{{ db_trans('edit') }}</button>
                                        <form method="POST" action="{{ route('finance.projects.destroy', $project) }}" class="project-delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    @if($projects->isNotEmpty())
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">{{ db_trans('grand_total') }}</th>
                                <th class="ui-text-success">{{ $currency($projects->sum('total_income')) }}</th>
                                <th class="ui-text-danger">{{ $currency($projects->sum('total_expense')) }}</th>
                                <th>{{ $currency($projects->sum(fn ($project) => ((float) ($project->total_income ?? 0)) - ((float) ($project->total_expense ?? 0)))) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="card ui-table-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="ui-section-heading">
                <div>
                    <h5 class="ui-section-title">{{ db_trans('project_transactions') }}</h5>
                    <p class="ui-section-subtitle">{{ db_trans('recent_project_transactions') }}</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if(Route::has('pdf.finance.projects.transactions.export'))
                        <a href="{{ route('pdf.finance.projects.transactions.export', request()->query()) }}" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3">
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if(Route::has('finance.projects.transactions.export.excel'))
                        <a href="{{ route('finance.projects.transactions.export.excel', request()->query()) }}" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="transactionsTable">
                    <thead>
                        <tr>
                            <th>{{ db_trans('date') }}</th>
                            <th>{{ db_trans('project') }}</th>
                            <th>{{ db_trans('type') }}</th>
                            <th>{{ db_trans('amount') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('payment_method') }}</th>
                            <th>{{ db_trans('reference_no') }}</th>
                            <th>{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($transactionRows as $transaction)
                            <tr>
                                <td>{{ optional($transaction->transaction_date)->format('d M Y') }}</td>
                                <td>{{ $transaction->project?->name ?? '—' }}</td>
                                <td><span class="ui-inline-badge">{{ $transaction->type_label }}</span></td>
                                <td class="fw-semibold {{ $transaction->transaction_type === \App\Models\ProjectTransaction::TYPE_INCOME ? 'ui-text-success' : 'ui-text-danger' }}">{{ $currency($transaction->amount) }}</td>
                                <td><span class="ui-status-pill ui-status-{{ strtolower($transaction->status) }}">{{ $transaction->status_label }}</span></td>
                                <td>{{ $transaction->payment_method_label }}</td>
                                <td>{{ $transaction->reference_no ?: '—' }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-sm ui-btn-light" data-bs-toggle="modal" data-bs-target="#transactionEditModal{{ $transaction->id }}">{{ db_trans('edit') }}</button>
                                        <form method="POST" action="{{ route('finance.projects.transactions.destroy', $transaction) }}" class="project-delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($transactions, 'links'))
                <div class="pt-4">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>

    @include('admin.finance.projects.partials.modals')
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableLanguage = {
            search: '',
            searchPlaceholder: @json(db_trans('search')) + '...',
            lengthMenu: '_MENU_',
            zeroRecords: @json(db_trans('no_records_found')),
            info: @json(db_trans('showing_records_info')),
            infoEmpty: @json(db_trans('no_records_found')),
            paginate: {
                previous: @json(db_trans('previous')),
                next: @json(db_trans('next'))
            }
        };

        if (window.jQuery && $('#projectsTable').length) {
            $('#projectsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [],
                lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, @json(db_trans('all'))]],
                language: tableLanguage,
            });
        }

        if (window.jQuery && $('#transactionsTable').length) {
            $('#transactionsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [],
                lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, @json(db_trans('all'))]],
                language: tableLanguage,
            });
        }

        document.querySelectorAll('.project-delete-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: @json(db_trans('are_you_sure')),
                    text: @json(db_trans('this_action_cannot_be_undone')),
                    showCancelButton: true,
                    confirmButtonColor: '#7c3aed',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: @json(db_trans('delete')),
                    cancelButtonText: @json(db_trans('cancel')),
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