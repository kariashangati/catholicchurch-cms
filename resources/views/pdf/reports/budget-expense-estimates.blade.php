@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('expense_budget_estimates') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ db_trans('category') }}</th>
                    <th>{{ db_trans('group') }}</th>
                    <th class="text-right">{{ db_trans('amount') }}</th>
                    <th>{{ db_trans('year') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->sn }}</td>
                        <td>{{ $row->category }}</td>
                        <td>{{ $row->group }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $row->amount, 2) }}</td>
                        <td>{{ $row->year }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">{{ db_trans('no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>

            @if($rows->count())
                <tfoot>
                    <tr>
                        <td colspan="3">{{ db_trans('grand_total') }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $rows->sum('amount'), 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection