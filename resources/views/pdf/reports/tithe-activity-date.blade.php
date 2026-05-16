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
                    <th>{{ db_trans('recorded_by') }}</th>
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
                        <td>{{ $row->recorded_by ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) ($row->amount ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ db_trans('no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>

            @if(!empty($rows) && $rows->count())
                <tfoot>
                    <tr>
                        <td colspan="6">{{ db_trans('grand_total') }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $rows->sum('amount'), 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if(!empty($recorderRows) && $recorderRows->count())
        <div class="section-header">
            <h3>{{ db_trans('recorders') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ db_trans('recorded_by') }}</th>
                        <th class="text-right">{{ db_trans('number_of_records') }}</th>
                        <th class="text-right">{{ db_trans('amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recorderRows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row->recorder_name ?? '—' }}</td>
                            <td class="text-right tabular-nums">{{ number_format((int) ($row->records_count ?? 0)) }}</td>
                            <td class="text-right tabular-nums">{{ number_format((float) ($row->total_amount ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection