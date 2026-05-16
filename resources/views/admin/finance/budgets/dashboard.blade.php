@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div><h3 class="mb-1">{{ db_trans('budget_estimates') }}</h3><div class="text-muted">{{ db_trans('year') }}: {{ $year }}</div></div>
    <form class="d-flex gap-2" method="GET"><input type="number" name="year" class="form-control" value="{{ $year }}"><button class="btn btn-primary">{{ db_trans('filter_records') }}</button></form>
</div>
<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="finance-stat-card"><div class="finance-stat-label">{{ db_trans('income_budget') }}</div><div class="finance-stat-value">{{ number_format($income_total, 2) }}</div></div></div>
    <div class="col-md-4"><div class="finance-stat-card"><div class="finance-stat-label">{{ db_trans('expense_budget') }}</div><div class="finance-stat-value">{{ number_format($expense_total, 2) }}</div></div></div>
    <div class="col-md-4"><div class="finance-stat-card"><div class="finance-stat-label">{{ db_trans('net_budget') }}</div><div class="finance-stat-value">{{ number_format($net_total, 2) }}</div></div></div>
</div>
<div class="row g-4">
    <div class="col-md-6"><div class="budget-link-card"><h5>{{ db_trans('income_budget_estimates') }}</h5><a href="{{ route('finance.budgets.income.index', ['year' => $year]) }}" class="btn btn-primary">{{ db_trans('open_income_budget') }}</a></div></div>
    <div class="col-md-6"><div class="budget-link-card"><h5>{{ db_trans('expense_budget_estimates') }}</h5><a href="{{ route('finance.budgets.expense.index', ['year' => $year]) }}" class="btn btn-primary">{{ db_trans('open_expense_budget') }}</a></div></div>
</div>
@endsection
