@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('tithes') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ db_trans('date') }}</th>
                    <th>{{ db_trans('member') }}</th>
                    <th>{{ db_trans('phone') }}</th>
                    <th>{{ db_trans('jumuiya') }}</th>
                    <th class="text-right">{{ db_trans('amount') }}</th>
                    <th>{{ db_trans('payment_method') }}</th>
                    <th>{{ db_trans('status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->date ?? '—' }}</td>
                        <td>{{ $row->member ?? '—' }}</td>
                        <td>{{ $row->phone ?? '—' }}</td>
                        <td>{{ $row->jumuiya ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) ($row->amount ?? 0), 2) }}</td>
                        <td>{{ $row->payment_method ?? '—' }}</td>
                        <td>{{ $row->status ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ db_trans('no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection