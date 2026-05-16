@extends('layouts.admin')

@section('title', db_trans('expense_budget_estimates'))

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-1">{{ db_trans('expense_budget_estimates') }}</h3>
        <div class="text-muted">{{ db_trans('year') }}: {{ $year }}</div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <form class="d-flex gap-2" method="GET">
            <input type="number" name="year" class="form-control" value="{{ $year }}">
            <button class="btn btn-light">{{ db_trans('filter_records') }}</button>
        </form>

        @if(Route::has('pdf.finance.budgets.expense.export'))
            <a href="{{ route('pdf.finance.budgets.expense.export', ['year' => $year]) }}" target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
            </a>
        @endif

        @if(Route::has('finance.budgets.expense.export.excel'))
            <a href="{{ route('finance.budgets.expense.export.excel', ['year' => $year]) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
            </a>
        @endif

        @can('finance.budgets.expense.create')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createExpenseBudgetModal">
                {{ db_trans('add_record') }}
            </button>
        @endcan
    </div>
</div>

<div class="card finance-soft-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table finance-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ db_trans('category') }}</th>
                        <th>{{ db_trans('group') }}</th>
                        <th>{{ db_trans('amount') }}</th>
                        <th>{{ db_trans('year') }}</th>
                        <th>{{ db_trans('action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $record->category_name }}</td>
                            <td>{{ $record->group_label }}</td>
                            <td>{{ number_format($record->amount, 2) }}</td>
                            <td>{{ $record->budget_year }}</td>
                            <td class="text-nowrap">
                                @can('finance.budgets.expense.update')
                                    <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editExpenseBudgetModal{{ $record->id }}">
                                        {{ db_trans('edit') }}
                                    </button>
                                @endcan

                                @can('finance.budgets.expense.delete')
                                    <form method="POST" action="{{ route('finance.budgets.expense.destroy', $record) }}" class="d-inline" onsubmit="return confirm('{{ db_trans('delete_confirmation') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>

                        @include('admin.finance.budgets.partials.expense-edit-modal', [
                            'record' => $record,
                            'groupOptions' => $groupOptions,
                            'suggestedCategories' => $suggestedCategories,
                            'year' => $year,
                        ])
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">{{ db_trans('no_records_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">{{ db_trans('grand_total') }}</th>
                        <th colspan="3">{{ number_format($total, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@include('admin.finance.budgets.partials.expense-create-modal', [
    'groupOptions' => $groupOptions,
    'suggestedCategories' => $suggestedCategories,
    'year' => $year,
])
@endsection