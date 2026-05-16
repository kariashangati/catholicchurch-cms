@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('all_records') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ db_trans('member') }}</th>
                    <th>{{ db_trans('phone') }}</th>
                    <th>{{ db_trans('kanda') }}</th>
                    <th>{{ db_trans('jumuiya') }}</th>
                    <th>{{ db_trans('payment_method') }}</th>
                    <th>{{ db_trans('status') }}</th>
                    <th class="text-right">{{ db_trans('amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->sn ?? '—' }}</td>
                        <td>{{ $row->member ?? '—' }}</td>
                        <td>{{ $row->phone ?? '—' }}</td>
                        <td>{{ $row->kanda ?? '—' }}</td>
                        <td>{{ $row->jumuiya ?? '—' }}</td>
                        <td>{{ $row->payment_method ?? '—' }}</td>
                        <td>{{ $row->status ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) ($row->amount ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">{{ db_trans('no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>

            @if(!empty($rows) && $rows->count())
                <tfoot>
                    <tr>
                        <td colspan="7">{{ db_trans('grand_total') }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $rows->sum('amount'), 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection