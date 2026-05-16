resources/views/pdf/kanda-reports/financial-report.blade.php@extends('pdf.layouts.report')

@section('content')
@php
    $money = fn ($value) => number_format((float) $value, 2);
    $contributionTypes = collect($contributionTypes ?? []);
    $reports = collect($reports ?? []);
    $jumuiyas = collect($jumuiyas ?? []);
@endphp

<div class="section-header">
    <h3>{{ db_trans('summary') }}</h3>
    <div class="accent-line"></div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                @if($isShowReport)
                    <th class="text-right">{{ db_trans('jumuiyas') }}</th>
                    <th class="text-right">{{ db_trans('familias') }}</th>
                    <th class="text-right">{{ db_trans('members') }}</th>
                @else
                    <th class="text-right">{{ db_trans('kandas') }}</th>
                    <th class="text-right">{{ db_trans('jumuiyas') }}</th>
                    <th class="text-right">{{ db_trans('familias') }}</th>
                    <th class="text-right">{{ db_trans('members') }}</th>
                @endif

                <th class="text-right">{{ db_trans('tithes') }}</th>
                <th class="text-right">{{ db_trans('offerings') }}</th>
                <th class="text-right">{{ db_trans('grand_total') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                @if($isShowReport)
                    <td class="text-right tabular-nums">{{ number_format((int) data_get($stats, 'jumuiyas_count', $jumuiyas->count())) }}</td>
                    <td class="text-right tabular-nums">{{ number_format((int) data_get($stats, 'familias_count', $jumuiyas->sum('familias_count'))) }}</td>
                    <td class="text-right tabular-nums">{{ number_format((int) data_get($stats, 'members_count', $jumuiyas->sum('members_count'))) }}</td>
                    <td class="text-right tabular-nums">{{ $money(data_get($stats, 'total_tithes', $jumuiyas->sum('tithe_total'))) }}</td>
                    <td class="text-right tabular-nums">{{ $money(data_get($stats, 'total_offerings', $jumuiyas->sum('offering_total'))) }}</td>
                    <td class="text-right tabular-nums">{{ $money(data_get($stats, 'grand_total', $jumuiyas->sum('grand_total'))) }}</td>
                @else
                    <td class="text-right tabular-nums">{{ number_format((int) data_get($stats, 'total_kandas', $reports->count())) }}</td>
                    <td class="text-right tabular-nums">{{ number_format((int) data_get($stats, 'total_jumuiyas', 0)) }}</td>
                    <td class="text-right tabular-nums">{{ number_format((int) data_get($stats, 'total_familias', 0)) }}</td>
                    <td class="text-right tabular-nums">{{ number_format((int) data_get($stats, 'total_members', 0)) }}</td>
                    <td class="text-right tabular-nums">{{ $money(data_get($stats, 'total_tithes', 0)) }}</td>
                    <td class="text-right tabular-nums">{{ $money(data_get($stats, 'total_offerings', 0)) }}</td>
                    <td class="text-right tabular-nums">{{ $money(data_get($stats, 'grand_total', 0)) }}</td>
                @endif
            </tr>
        </tbody>
    </table>
</div>

<div class="section-header">
    <h3>{{ $isShowReport ? db_trans('jumuiya_breakdown') : db_trans('kanda_financial_reports') }}</h3>
    <div class="accent-line"></div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ $isShowReport ? db_trans('jumuiya') : db_trans('kanda') }}</th>
                @if(!$isShowReport)
                    <th class="text-right">{{ db_trans('jumuiyas') }}</th>
                @endif
                <th class="text-right">{{ db_trans('familias') }}</th>
                <th class="text-right">{{ db_trans('members') }}</th>
                <th class="text-right">{{ db_trans('tithes') }}</th>
                <th class="text-right">{{ db_trans('offerings') }}</th>

                @foreach($contributionTypes as $type)
                    <th class="text-right">{{ $type->name }}</th>
                @endforeach

                <th class="text-right">{{ db_trans('grand_total') }}</th>
            </tr>
        </thead>

        <tbody>
            @if($isShowReport)
                @forelse($jumuiyas as $jumuiya)
                    <tr>
                        <td>{{ data_get($jumuiya, 'name', '—') }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) data_get($jumuiya, 'familias_count', 0)) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) data_get($jumuiya, 'members_count', 0)) }}</td>
                        <td class="text-right tabular-nums">{{ $money(data_get($jumuiya, 'tithe_total', 0)) }}</td>
                        <td class="text-right tabular-nums">{{ $money(data_get($jumuiya, 'offering_total', 0)) }}</td>

                        @foreach($contributionTypes as $type)
                            <td class="text-right tabular-nums">{{ $money(data_get($jumuiya, 'contribution_type_totals.' . $type->id, 0)) }}</td>
                        @endforeach

                        <td class="text-right tabular-nums">{{ $money(data_get($jumuiya, 'grand_total', 0)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 6 + $contributionTypes->count() }}" class="text-center">{{ db_trans('no_data_found') }}</td>
                    </tr>
                @endforelse
            @else
                @forelse($reports as $report)
                    <tr>
                        <td>{{ $report->name ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) ($report->jumuiyas_count ?? 0)) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) ($report->familias_count ?? 0)) }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) ($report->members_count ?? 0)) }}</td>
                        <td class="text-right tabular-nums">{{ $money($report->tithe_total ?? 0) }}</td>
                        <td class="text-right tabular-nums">{{ $money($report->offering_total ?? 0) }}</td>

                        @foreach($contributionTypes as $type)
                            <td class="text-right tabular-nums">{{ $money(data_get($report, 'contribution_type_totals.' . $type->id, 0)) }}</td>
                        @endforeach

                        <td class="text-right tabular-nums">{{ $money($report->grand_total ?? 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 7 + $contributionTypes->count() }}" class="text-center">{{ db_trans('no_data_found') }}</td>
                    </tr>
                @endforelse
            @endif
        </tbody>

        <tfoot>
            <tr>
                <th>{{ db_trans('grand_total') }}</th>

                @if($isShowReport)
                    <th class="text-right tabular-nums">{{ number_format((int) $jumuiyas->sum('familias_count')) }}</th>
                    <th class="text-right tabular-nums">{{ number_format((int) $jumuiyas->sum('members_count')) }}</th>
                    <th class="text-right tabular-nums">{{ $money($jumuiyas->sum('tithe_total')) }}</th>
                    <th class="text-right tabular-nums">{{ $money($jumuiyas->sum('offering_total')) }}</th>

                    @foreach($contributionTypes as $type)
                        <th class="text-right tabular-nums">
                            {{ $money($jumuiyas->sum(fn ($row) => (float) data_get($row, 'contribution_type_totals.' . $type->id, 0))) }}
                        </th>
                    @endforeach

                    <th class="text-right tabular-nums">{{ $money($jumuiyas->sum('grand_total')) }}</th>
                @else
                    <th class="text-right tabular-nums">{{ number_format((int) data_get($stats, 'total_jumuiyas', 0)) }}</th>
                    <th class="text-right tabular-nums">{{ number_format((int) data_get($stats, 'total_familias', 0)) }}</th>
                    <th class="text-right tabular-nums">{{ number_format((int) data_get($stats, 'total_members', 0)) }}</th>
                    <th class="text-right tabular-nums">{{ $money(data_get($stats, 'total_tithes', 0)) }}</th>
                    <th class="text-right tabular-nums">{{ $money(data_get($stats, 'total_offerings', 0)) }}</th>

                    @foreach($contributionTypes as $type)
                        <th class="text-right tabular-nums">{{ $money(data_get($stats, 'contribution_type_totals.' . $type->id, 0)) }}</th>
                    @endforeach

                    <th class="text-right tabular-nums">{{ $money(data_get($stats, 'grand_total', 0)) }}</th>
                @endif
            </tr>
        </tfoot>
    </table>
</div>
@endsection