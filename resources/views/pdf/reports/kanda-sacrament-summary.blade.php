@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ $reportTitle }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 4%;">#</th>
                    <th style="width: 16%;">{{ db_trans('kanda') }}</th>
                    <th style="width: 8%;">{{ db_trans('code') }}</th>
                    <th class="text-right" style="width: 8%;">{{ db_trans('jumuiyas') }}</th>
                    <th class="text-right" style="width: 8%;">{{ db_trans('familias') }}</th>
                    <th class="text-right" style="width: 9%;">{{ db_trans('members') }}</th>
                    <th class="text-right" style="width: 9%;">{{ db_trans('baptized') }}</th>
                    <th class="text-right" style="width: 9%;">{{ db_trans('communion') }}</th>
                    <th class="text-right" style="width: 10%;">{{ db_trans('confirmation') }}</th>
                    <th class="text-right" style="width: 9%;">{{ db_trans('married') }}</th>
                    <th class="text-right" style="width: 10%;">{{ db_trans('receiving_eucharist') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="text-center">{{ $row->sn }}</td>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->code }}</td>
                        <td class="text-right tabular-nums">{{ number_format($row->jumuiyas) }}</td>
                        <td class="text-right tabular-nums">{{ number_format($row->familias) }}</td>
                        <td class="text-right tabular-nums">{{ number_format($row->members) }}</td>
                        <td class="text-right tabular-nums">{{ number_format($row->baptized) }}</td>
                        <td class="text-right tabular-nums">{{ number_format($row->communion) }}</td>
                        <td class="text-right tabular-nums">{{ number_format($row->confirmation) }}</td>
                        <td class="text-right tabular-nums">{{ number_format($row->married) }}</td>
                        <td class="text-right tabular-nums">{{ number_format($row->eucharist) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">{{ db_trans('no_data_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection