@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('project_transactions') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ db_trans('date') }}</th>
                    <th>{{ db_trans('project') }}</th>
                    <th>{{ db_trans('type') }}</th>
                    <th class="text-right">{{ db_trans('amount') }}</th>
                    <th>{{ db_trans('status') }}</th>
                    <th>{{ db_trans('payment_method') }}</th>
                    <th>{{ db_trans('reference_no') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->sn }}</td>
                        <td>{{ $row->date }}</td>
                        <td>{{ $row->project }}</td>
                        <td>{{ $row->type }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $row->amount, 2) }}</td>
                        <td>{{ $row->status }}</td>
                        <td>{{ $row->payment_method }}</td>
                        <td>{{ $row->reference_no }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">{{ db_trans('no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>

            @if($rows->count())
                <tfoot>
                    <tr>
                        <td colspan="4">{{ db_trans('total') }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $rows->sum('amount'), 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection