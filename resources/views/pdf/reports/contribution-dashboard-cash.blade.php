@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('recent_cash_contributions') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ db_trans('member') }}</th>
                    <th>{{ db_trans('contribution_type') }}</th>
                    <th class="text-right">{{ db_trans('amount') }}</th>
                    <th>{{ db_trans('contribution_date') }}</th>
                    <th>{{ db_trans('status') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->sn }}</td>
                        <td>{{ $row->member }}</td>
                        <td>{{ $row->contribution_type }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $row->amount, 2) }}</td>
                        <td>{{ $row->contribution_date }}</td>
                        <td>{{ $row->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ db_trans('no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>

            @if($rows->count())
                <tfoot>
                    <tr>
                        <td colspan="3">{{ db_trans('grand_total') }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $rows->sum('amount'), 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection