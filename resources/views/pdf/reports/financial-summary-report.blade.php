@extends('pdf.layouts.report')

@section('content')
@php
    $currency = fn ($amount) => number_format((float) $amount, 2);

    $incomeTotal = (float) data_get($stats, 'income_total', 0);
    $expenseTotal = (float) data_get($stats, 'expense_total', 0);
    $balance = (float) data_get($stats, 'balance', 0);

    $monthlyRows = collect($monthlyTrendRows ?? []);
    $contributionRows = collect($contributionTypeRows ?? []);

    $maxTrendValue = max(
        1,
        (float) $monthlyRows->max('income'),
        (float) $monthlyRows->max('expense'),
        (float) $monthlyRows->max('balance')
    );

    $maxIncomeValue = max(1, (float) collect($incomeItems ?? [])->max('amount'));
@endphp

<div class="section-header">
    <h3>{{ db_trans('financial_summary') }}</h3>
    <div class="accent-line"></div>
</div>

<table class="summary-table" style="width: 100%; margin-bottom: 18px;">
    <tr>
        <td>
            <strong>{{ db_trans('total_income') }}</strong><br>
            {{ $currency($incomeTotal) }}
        </td>
        <td>
            <strong>{{ db_trans('total_expense') }}</strong><br>
            {{ $currency($expenseTotal) }}
        </td>
        <td>
            <strong>{{ db_trans('balance') }}</strong><br>
            {{ $currency($balance) }}
        </td>
        <td>
            <strong>{{ db_trans('records') }}</strong><br>
            {{ number_format((int) data_get($stats, 'income_records', 0) + (int) data_get($stats, 'expense_records', 0)) }}
        </td>
    </tr>
</table>

<div class="section-header">
    <h3>{{ db_trans('income_breakdown') }}</h3>
    <div class="accent-line"></div>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>{{ db_trans('source') }}</th>
            <th class="text-right">{{ db_trans('amount') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($incomeItems as $item)
            <tr>
                <td>{{ $item['label'] ?? '—' }}</td>
                <td class="text-right tabular-nums">{{ $currency($item['amount'] ?? 0) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th>{{ db_trans('total_income') }}</th>
            <th class="text-right tabular-nums">{{ $currency($incomeTotal) }}</th>
        </tr>
    </tfoot>
</table>

<div style="margin: 18px 0 26px;">
    <h4 style="margin-bottom: 8px;">{{ db_trans('income_breakdown_chart') }}</h4>

    @foreach($incomeItems as $item)
        @php
            $amount = (float) ($item['amount'] ?? 0);
            $width = $maxIncomeValue > 0 ? max(1, ($amount / $maxIncomeValue) * 100) : 1;
        @endphp

        <div style="margin-bottom: 7px;">
            <div style="font-size: 11px; margin-bottom: 2px;">
                {{ $item['label'] ?? '—' }} - {{ $currency($amount) }}
            </div>
            <div style="height: 10px; background: #e5e7eb; border-radius: 4px;">
                <div style="height: 10px; width: {{ $width }}%; background: #7c3aed; border-radius: 4px;"></div>
            </div>
        </div>
    @endforeach
</div>

<div class="section-header">
    <h3>{{ db_trans('expense_breakdown') }}</h3>
    <div class="accent-line"></div>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>{{ db_trans('source') }}</th>
            <th class="text-right">{{ db_trans('amount') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($expenseItems as $item)
            <tr>
                <td>{{ $item['label'] ?? '—' }}</td>
                <td class="text-right tabular-nums">{{ $currency($item['amount'] ?? 0) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="text-center">{{ db_trans('no_records_found') }}</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th>{{ db_trans('total_expense') }}</th>
            <th class="text-right tabular-nums">{{ $currency($expenseTotal) }}</th>
        </tr>
    </tfoot>
</table>

<div style="page-break-inside: avoid; margin: 22px 0;">
    <div class="section-header">
        <h3>{{ db_trans('monthly_finance_trend') }}</h3>
        <div class="accent-line"></div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>{{ db_trans('month') }}</th>
                <th class="text-right">{{ db_trans('income') }}</th>
                <th class="text-right">{{ db_trans('expense') }}</th>
                <th class="text-right">{{ db_trans('balance') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyRows as $row)
                <tr>
                    <td>{{ $row['label'] ?? '—' }}</td>
                    <td class="text-right tabular-nums">{{ $currency($row['income'] ?? 0) }}</td>
                    <td class="text-right tabular-nums">{{ $currency($row['expense'] ?? 0) }}</td>
                    <td class="text-right tabular-nums">{{ $currency($row['balance'] ?? 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h4 style="margin: 14px 0 8px;">{{ db_trans('monthly_finance_trend_chart') }}</h4>

    @foreach($monthlyRows as $row)
        @php
            $incomeWidth = ((float) ($row['income'] ?? 0) / $maxTrendValue) * 100;
            $expenseWidth = ((float) ($row['expense'] ?? 0) / $maxTrendValue) * 100;
            $balanceWidth = abs((float) ($row['balance'] ?? 0)) / $maxTrendValue * 100;
        @endphp

        <div style="margin-bottom: 8px;">
            <div style="font-size: 11px; margin-bottom: 2px;">{{ $row['label'] ?? '—' }}</div>

            <div style="height: 7px; background: #e5e7eb; border-radius: 4px; margin-bottom: 2px;">
                <div style="height: 7px; width: {{ max(1, $incomeWidth) }}%; background: #22c55e; border-radius: 4px;"></div>
            </div>

            <div style="height: 7px; background: #e5e7eb; border-radius: 4px; margin-bottom: 2px;">
                <div style="height: 7px; width: {{ max(1, $expenseWidth) }}%; background: #ef4444; border-radius: 4px;"></div>
            </div>

            <div style="height: 7px; background: #e5e7eb; border-radius: 4px;">
                <div style="height: 7px; width: {{ max(1, $balanceWidth) }}%; background: #7c3aed; border-radius: 4px;"></div>
            </div>
        </div>
    @endforeach

    <div style="font-size: 10px; color: #475569; margin-top: 6px;">
        <span style="color:#22c55e;">■</span> {{ db_trans('income') }}
        &nbsp;&nbsp;
        <span style="color:#ef4444;">■</span> {{ db_trans('expense') }}
        &nbsp;&nbsp;
        <span style="color:#7c3aed;">■</span> {{ db_trans('balance') }}
    </div>
</div>

<div style="page-break-inside: avoid;">
    <div class="section-header">
        <h3>{{ db_trans('contribution_type_breakdown') }}</h3>
        <div class="accent-line"></div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ db_trans('contribution_type') }}</th>
                <th class="text-right">{{ db_trans('cash') }}</th>
                <th class="text-right">{{ db_trans('bank') }}</th>
                <th class="text-right">{{ db_trans('total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contributionRows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ data_get($row, 'name', '—') }}</td>
                    <td class="text-right tabular-nums">{{ $currency(data_get($row, 'cash_total', 0)) }}</td>
                    <td class="text-right tabular-nums">{{ $currency(data_get($row, 'bank_total', 0)) }}</td>
                    <td class="text-right tabular-nums">{{ $currency(data_get($row, 'total', 0)) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">{{ db_trans('no_records_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection