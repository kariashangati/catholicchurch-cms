@extends('layouts.admin')

@section('title', db_trans('budget_reports'))
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/reports-v4-polish.css') }}">
@endpush

@section('content')
@php
    $incomeRows = collect($incomeRows ?? []);
    $expenseRows = collect($expenseRows ?? []);
    $incomeSourceOptions = $incomeSourceOptions ?? [];
    $expenseSourceOptions = $expenseSourceOptions ?? [];
    $groupOptions = $groupOptions ?? [];
    $currency = fn ($amount) => number_format((float) $amount, 2);
@endphp

<div class="admin-ui-v4 reports-v4-page">
    <div class="ui-page-hero">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge">
                    <i class="fas fa-scale-balanced"></i>{{ db_trans('budget_reports') }}
                </span>
                <h1 class="ui-page-title mt-3">{{ db_trans('budget_vs_actual') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-calendar"></i>{{ db_trans('year') }}: {{ $year }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-arrow-up"></i>{{ db_trans('income') }}: {{ $currency($incomeActualTotal) }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-arrow-down"></i>{{ db_trans('expense') }}: {{ $currency($expenseActualTotal) }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <form method="GET" class="d-flex gap-2 justify-content-xl-end">
                    <input type="number" class="form-control" name="year" min="2020" max="2100" value="{{ $year }}" aria-label="{{ db_trans('year') }}">
                    <button class="btn ui-btn-primary">{{ db_trans('load') }}</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4 ui-kpi-grid">
        <div class="col-lg-3 col-md-6">
            <div class="card ui-stat-card ui-tone-primary border-0 h-100">
                <div class="card-body">
                    <div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-arrow-up"></i></div><span class="ui-chip">{{ db_trans('budget') }}</span></div>
                    <div class="ui-stat-label">{{ db_trans('income_budget_total') }}</div>
                    <div class="ui-stat-value">{{ $currency($incomeBudgetTotal) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card ui-stat-card ui-tone-success border-0 h-100">
                <div class="card-body">
                    <div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-sack-dollar"></i></div><span class="ui-chip">{{ db_trans('actual') }}</span></div>
                    <div class="ui-stat-label">{{ db_trans('income_actual_total') }}</div>
                    <div class="ui-stat-value">{{ $currency($incomeActualTotal) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card ui-stat-card ui-tone-warning border-0 h-100">
                <div class="card-body">
                    <div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-arrow-down"></i></div><span class="ui-chip">{{ db_trans('budget') }}</span></div>
                    <div class="ui-stat-label">{{ db_trans('expense_budget_total') }}</div>
                    <div class="ui-stat-value">{{ $currency($expenseBudgetTotal) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card ui-stat-card ui-tone-danger border-0 h-100">
                <div class="card-body">
                    <div class="ui-stat-top"><div class="ui-stat-icon"><i class="fas fa-receipt"></i></div><span class="ui-chip">{{ db_trans('actual') }}</span></div>
                    <div class="ui-stat-label">{{ db_trans('expense_actual_total') }}</div>
                    <div class="ui-stat-value">{{ $currency($expenseActualTotal) }}</div>
                </div>
            </div>
        </div>
    </div>

    @can('reports.budgets.create')
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card ui-panel border-0 h-100">
                <div class="card-header fw-semibold">{{ db_trans('add_income_budget_line') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('reports.budgets.income.store') }}" class="row g-3 js-budget-form">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('source_type') }}</label>
                            <select class="form-select js-budget-source" name="category_code" required>
                                <option value="">{{ db_trans('select_option') }}</option>
                                @foreach($incomeSourceOptions as $code => $label)
                                    <option value="{{ $code }}" data-label="{{ $label }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('budget_item_name') }}</label>
                            <input class="form-control js-budget-name" name="category_name" placeholder="{{ db_trans('budget_item_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('budget_group') }}</label>
                            <select class="form-select" name="group_name" required>
                                @foreach($groupOptions as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ db_trans('amount') }}</label>
                            <input class="form-control" type="number" step="0.01" min="0" name="amount" placeholder="{{ db_trans('amount') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ db_trans('year') }}</label>
                            <input class="form-control" type="number" name="budget_year" min="2020" max="2100" value="{{ $year }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ db_trans('notes') }}</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn ui-btn-primary w-100">{{ db_trans('save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card ui-panel border-0 h-100">
                <div class="card-header fw-semibold">{{ db_trans('add_expense_budget_line') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('reports.budgets.expense.store') }}" class="row g-3 js-budget-form">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('expense_type') }}</label>
                            <select class="form-select js-budget-source" name="category_code" required>
                                <option value="">{{ db_trans('select_option') }}</option>
                                @foreach($expenseSourceOptions as $code => $label)
                                    <option value="{{ $code }}" data-label="{{ $label }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('budget_item_name') }}</label>
                            <input class="form-control js-budget-name" name="category_name" placeholder="{{ db_trans('budget_item_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('budget_group') }}</label>
                            <select class="form-select" name="group_name" required>
                                @foreach($groupOptions as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ db_trans('amount') }}</label>
                            <input class="form-control" type="number" step="0.01" min="0" name="amount" placeholder="{{ db_trans('amount') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ db_trans('year') }}</label>
                            <input class="form-control" type="number" name="budget_year" min="2020" max="2100" value="{{ $year }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ db_trans('notes') }}</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn ui-btn-primary w-100">{{ db_trans('save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card ui-panel border-0 h-100">
                <div class="card-body">
                    <div class="ui-panel-head">
                        <div><h5 class="ui-panel-title">{{ db_trans('budget_vs_actual') }}</h5></div>
                        <div class="ui-panel-icon"><i class="fas fa-chart-column"></i></div>
                    </div>
                    <div class="ui-chart-shell ui-chart-shell-lg"><canvas id="budgetOverviewChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card ui-panel border-0 h-100">
                <div class="card-body">
                    <div class="ui-panel-head">
                        <div><h5 class="ui-panel-title">{{ db_trans('budget_summary') }}</h5></div>
                        <div class="ui-panel-icon"><i class="fas fa-scale-balanced"></i></div>
                    </div>
                    <div class="ui-inline-stat-row"><span>{{ db_trans('income_budget_total') }}</span><strong>{{ $currency($incomeBudgetTotal) }}</strong></div>
                    <div class="ui-inline-stat-row"><span>{{ db_trans('income_actual_total') }}</span><strong>{{ $currency($incomeActualTotal) }}</strong></div>
                    <div class="ui-inline-stat-row"><span>{{ db_trans('expense_budget_total') }}</span><strong>{{ $currency($expenseBudgetTotal) }}</strong></div>
                    <div class="ui-inline-stat-row"><span>{{ db_trans('expense_actual_total') }}</span><strong>{{ $currency($expenseActualTotal) }}</strong></div>
                    <div class="ui-inline-stat-row"><span>{{ db_trans('balance') }}</span><strong>{{ $currency($incomeActualTotal - $expenseActualTotal) }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card ui-table-card border-0 h-100">
                <div class="card-header"><h5 class="ui-panel-title mb-1">{{ db_trans('income_budget_lines') }}</h5></div>
                <div class="card-body p-0 table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ db_trans('source_type') }}</th>
                                <th>{{ db_trans('budget_item_name') }}</th>
                                <th>{{ db_trans('budget_group') }}</th>
                                <th class="text-end">{{ db_trans('budget') }}</th>
                                <th class="text-end">{{ db_trans('actual') }}</th>
                                <th class="text-end">{{ db_trans('variance') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($incomeRows as $row)
                            <tr>
                                <td>{{ $row['category_label'] }}</td>
                                <td><div class="fw-semibold">{{ $row['category_name'] }}</div><div class="text-muted small">{{ $row['category_code'] }}</div></td>
                                <td>{{ $row['group_label'] }}</td>
                                <td class="text-end">{{ $currency($row['budget_amount']) }}</td>
                                <td class="text-end">{{ $currency($row['actual_amount']) }}</td>
                                <td class="text-end fw-semibold">{{ $currency($row['variance']) }}</td>
                                <td class="text-end">
                                    @can('reports.budgets.delete')
                                    <form method="POST" action="{{ route('reports.budgets.income.destroy', $row['id']) }}" class="budget-delete-form" data-confirm="{{ db_trans('this_action_cannot_be_undone') }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card ui-table-card border-0 h-100">
                <div class="card-header"><h5 class="ui-panel-title mb-1">{{ db_trans('expense_budget_lines') }}</h5></div>
                <div class="card-body p-0 table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ db_trans('expense_type') }}</th>
                                <th>{{ db_trans('budget_item_name') }}</th>
                                <th>{{ db_trans('budget_group') }}</th>
                                <th class="text-end">{{ db_trans('budget') }}</th>
                                <th class="text-end">{{ db_trans('actual') }}</th>
                                <th class="text-end">{{ db_trans('variance') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($expenseRows as $row)
                            <tr>
                                <td>{{ $row['category_label'] }}</td>
                                <td><div class="fw-semibold">{{ $row['category_name'] }}</div><div class="text-muted small">{{ $row['category_code'] }}</div></td>
                                <td>{{ $row['group_label'] }}</td>
                                <td class="text-end">{{ $currency($row['budget_amount']) }}</td>
                                <td class="text-end">{{ $currency($row['actual_amount']) }}</td>
                                <td class="text-end fw-semibold">{{ $currency($row['variance']) }}</td>
                                <td class="text-end">
                                    @can('reports.budgets.delete')
                                    <form method="POST" action="{{ route('reports.budgets.expense.destroy', $row['id']) }}" class="budget-delete-form" data-confirm="{{ db_trans('this_action_cannot_be_undone') }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-budget-form').forEach(function (form) {
        const source = form.querySelector('.js-budget-source');
        const name = form.querySelector('.js-budget-name');

        if (!source || !name) {
            return;
        }

        source.addEventListener('change', function () {
            const selected = source.options[source.selectedIndex];

            if (!name.value.trim() && selected && selected.dataset.label) {
                name.value = selected.dataset.label;
            }
        });
    });

    document.querySelectorAll('.budget-delete-form').forEach(function(form){
        form.addEventListener('submit', function(e){
            if (typeof Swal === 'undefined') return;
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: @json(db_trans('are_you_sure')),
                text: form.dataset.confirm || @json(db_trans('this_action_cannot_be_undone')),
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                confirmButtonText: @json(db_trans('delete')),
                cancelButtonText: @json(db_trans('cancel'))
            }).then(function(result){
                if(result.isConfirmed){
                    form.submit();
                }
            });
        });
    });

    if (!window.Chart) return;
    const ctx = document.getElementById('budgetOverviewChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [@json(db_trans('income')), @json(db_trans('expense'))],
            datasets: [
                { label: @json(db_trans('budget')), data: [{{ (float)$incomeBudgetTotal }}, {{ (float)$expenseBudgetTotal }}], borderWidth: 0 },
                { label: @json(db_trans('actual')), data: [{{ (float)$incomeActualTotal }}, {{ (float)$expenseActualTotal }}], borderWidth: 0 }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
@endpush
