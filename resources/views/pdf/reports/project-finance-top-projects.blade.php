@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('top_projects') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ db_trans('project') }}</th>
                    <th>{{ db_trans('status') }}</th>
                    <th class="text-right">{{ db_trans('income') }}</th>
                    <th class="text-right">{{ db_trans('expense') }}</th>
                    <th class="text-right">{{ db_trans('balance') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->sn ?? '—' }}</td>
                        <td>{{ $row->project ?? '—' }}</td>
                        <td>{{ $row->status ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) ($row->income ?? 0), 2) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) ($row->expense ?? 0), 2) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) ($row->balance ?? 0), 2) }}</td>
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
                        <td colspan="3">{{ db_trans('grand_total') }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $rows->sum('income'), 2) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $rows->sum('expense'), 2) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $rows->sum('balance'), 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection