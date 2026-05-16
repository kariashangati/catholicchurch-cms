@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('waliotoa_wasiotoa') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ db_trans('sn') }}</th>
                    <th>{{ db_trans('member') }}</th>
                    <th>{{ db_trans('phone') }}</th>

                    @if($showJumuiyaColumn)
                        <th>{{ db_trans('jumuiya') }}</th>
                    @endif

                    <th>{{ db_trans('date') }}</th>
                    <th class="text-right">{{ $selectedDataLabel ?? db_trans('amount') }}</th>
                    <th>{{ db_trans('status') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->sn }}</td>
                        <td>
                            {{ $row->member }}
                            <br>
                            <span style="font-size: 10px; color: #64748b;">{{ $row->member_code }}</span>
                        </td>
                        <td>{{ $row->phone }}</td>

                        @if($showJumuiyaColumn)
                            <td>{{ $row->jumuiya }}</td>
                        @endif

                        <td>{{ $row->date }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) $row->amount, 2) }}</td>
                        <td>{{ $row->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $showJumuiyaColumn ? 7 : 6 }}" class="text-center">
                            {{ db_trans('no_records_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if($rows->count())
                <tfoot>
                    <tr>
                        <td colspan="{{ $showJumuiyaColumn ? 5 : 4 }}" class="text-right">
                            {{ db_trans('total_amount') }}
                        </td>
                        <td class="text-right tabular-nums">
                            {{ number_format((float) $rows->sum('amount'), 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection