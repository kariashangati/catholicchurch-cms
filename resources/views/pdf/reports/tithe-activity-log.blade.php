@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('tithe_activity_log') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ db_trans('id') }}</th>
                    <th>{{ db_trans('activity_date') }}</th>
                    <th class="text-right">{{ db_trans('number_of_records') }}</th>
                    <th class="text-right">{{ db_trans('recorders') }}</th>
                    <th class="text-right">{{ db_trans('bulk_batches') }}</th>
                    <th class="text-right">{{ db_trans('amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->sn ?? '—' }}</td>
                        <td>{{ $row->activity_date ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) ($row->records_count ?? 0)) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) ($row->recorders_count ?? 0)) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) ($row->batches_count ?? 0)) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) ($row->amount ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ db_trans('no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>

            @if(!empty($rows) && $rows->count())
                <tfoot>
                    <tr>
                        <td colspan="2">{{ db_trans('grand_total') }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) $rows->sum('records_count')) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) $rows->sum('recorders_count')) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) $rows->sum('batches_count')) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $rows->sum('amount'), 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection