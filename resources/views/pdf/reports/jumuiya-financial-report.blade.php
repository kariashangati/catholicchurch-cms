@extends('pdf.layouts.report')

@php
    $money = fn ($value) => number_format((float) $value, 2);
    $types = collect($contributionTypes ?? []);
    $reports = collect($reports ?? []);
    $familyBreakdown = collect($familyBreakdown ?? []);
    $recentTransactions = collect($recentTransactions ?? []);
@endphp

@section('content')
    <div class="section-header">
        <h3>{{ $reportTitle ?? $pageTitle }}</h3>
        <div class="accent-line"></div>
    </div>

    @include('pdf.partials.report-meta', [
        'items' => $metaItems ?? [],
    ])

    @if(! $isShowReport)
        <div class="section-header">
            <h3>{{ db_trans('jumuiya_financial_reports') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ db_trans('jumuiya') }}</th>
                        <th>{{ db_trans('kanda') }}</th>
                        <th class="text-right">{{ db_trans('familias') }}</th>
                        <th class="text-right">{{ db_trans('members') }}</th>
                        <th class="text-right">{{ db_trans('tithes') }}</th>
                        <th class="text-right">{{ db_trans('offerings') }}</th>
                        @foreach($types as $type)
                            <th class="text-right">{{ $type->name }}</th>
                        @endforeach
                        <th class="text-right">{{ db_trans('grand_total') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->name ?? '—' }}</td>
                            <td>{{ $report->kanda?->name ?? '—' }}</td>
                            <td class="text-right">{{ number_format((int) ($report->familias_count ?? 0)) }}</td>
                            <td class="text-right">{{ number_format((int) ($report->members_count ?? 0)) }}</td>
                            <td class="text-right">{{ $money($report->tithe_total ?? 0) }}</td>
                            <td class="text-right">{{ $money($report->offering_total ?? 0) }}</td>
                            @foreach($types as $type)
                                <td class="text-right">{{ $money(data_get($report, 'contribution_type_totals.' . $type->id, 0)) }}</td>
                            @endforeach
                            <td class="text-right">{{ $money($report->grand_total ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 7 + $types->count() }}" class="text-center">{{ db_trans('no_data_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <td>{{ db_trans('grand_total') }}</td>
                        <td>—</td>
                        <td class="text-right">{{ number_format((int) data_get($stats, 'total_familias', 0)) }}</td>
                        <td class="text-right">{{ number_format((int) data_get($stats, 'total_members', 0)) }}</td>
                        <td class="text-right">{{ $money(data_get($stats, 'total_tithes', 0)) }}</td>
                        <td class="text-right">{{ $money(data_get($stats, 'total_offerings', 0)) }}</td>
                        @foreach($types as $type)
                            <td class="text-right">{{ $money(data_get($stats, 'contribution_type_totals.' . $type->id, 0)) }}</td>
                        @endforeach
                        <td class="text-right">{{ $money(data_get($stats, 'grand_total', 0)) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="section-header">
            <h3>{{ db_trans('familias') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ db_trans('familia') }}</th>
                        <th class="text-right">{{ db_trans('members') }}</th>
                        <th class="text-right">{{ db_trans('tithes') }}</th>
                        @foreach($types as $type)
                            <th class="text-right">{{ $type->name }}</th>
                        @endforeach
                        <th class="text-right">{{ db_trans('grand_total') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($familyBreakdown as $familia)
                        <tr>
                            <td>{{ $familia->name ?? '—' }}</td>
                            <td class="text-right">{{ number_format((int) ($familia->members_count ?? 0)) }}</td>
                            <td class="text-right">{{ $money($familia->tithe_total ?? 0) }}</td>
                            @foreach($types as $type)
                                <td class="text-right">{{ $money(data_get($familia, 'contribution_type_totals.' . $type->id, 0)) }}</td>
                            @endforeach
                            <td class="text-right">{{ $money($familia->grand_total ?? 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + $types->count() }}" class="text-center">{{ db_trans('no_data_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <td>{{ db_trans('grand_total') }}</td>
                        <td class="text-right">{{ number_format((int) $familyBreakdown->sum('members_count')) }}</td>
                        <td class="text-right">{{ $money($familyBreakdown->sum('tithe_total')) }}</td>
                        @foreach($types as $type)
                            <td class="text-right">{{ $money($familyBreakdown->sum(fn ($row) => (float) data_get($row, 'contribution_type_totals.' . $type->id, 0))) }}</td>
                        @endforeach
                        <td class="text-right">{{ $money($familyBreakdown->sum('grand_total')) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="section-header">
            <h3>{{ db_trans('recent_transactions') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ db_trans('source') }}</th>
                        <th>{{ db_trans('type') }}</th>
                        <th>{{ db_trans('member') }}</th>
                        <th>{{ db_trans('familia') }}</th>
                        <th class="text-right">{{ db_trans('amount') }}</th>
                        <th>{{ db_trans('date') }}</th>
                        <th>{{ db_trans('status') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recentTransactions as $item)
                        <tr>
                            <td>{{ $item->source ?? '—' }}</td>
                            <td>{{ $item->category ?? '—' }}</td>
                            <td>{{ $item->member_name ?? '—' }}</td>
                            <td>{{ $item->familia_name ?? '—' }}</td>
                            <td class="text-right">{{ $money($item->amount ?? 0) }}</td>
                            <td>{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('Y-m-d') : '—' }}</td>
                            <td>{{ $item->status ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ db_trans('no_data_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection