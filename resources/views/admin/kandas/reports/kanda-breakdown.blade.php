<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale ?? app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ data_get($report, 'title', db_trans('report')) }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .page {
            padding: 24px 28px 40px;
        }

        .header {
            border-bottom: 2px solid #dbeafe;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo-cell {
            width: 90px;
        }

        .logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
        }

        .header-center {
            text-align: center;
        }

        .site-name {
            font-size: 24px;
            font-weight: 800;
            color: #1d4ed8;
            line-height: 1.2;
        }

        .site-tagline {
            font-size: 14px;
            font-weight: 700;
            color: #2563eb;
            margin-top: 3px;
        }

        .site-meta {
            font-size: 12px;
            color: #111827;
            margin-top: 4px;
        }

        .report-title {
            margin-top: 8px;
            font-size: 20px;
            font-weight: 800;
            color: #2563eb;
        }

        .report-subtitle {
            margin-top: 4px;
            font-size: 12px;
            color: #4b5563;
        }

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 800;
            color: #111827;
            margin: 0 0 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }

        .meta-grid,
        .cards-table,
        .two-col,
        .data-table,
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-grid td,
        .cards-table td,
        .two-col td {
            vertical-align: top;
            padding: 4px;
        }

        .meta-card,
        .card,
        .chart-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
            background: #f8fafc;
        }

        .meta-label,
        .card-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .meta-value,
        .card-value {
            font-size: 14px;
            font-weight: 800;
            color: #111827;
        }

        .bar-chart {
            margin-top: 8px;
        }

        .bar-row {
            margin-bottom: 10px;
        }

        .bar-label-wrap {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 4px;
        }

        .bar-track {
            width: 100%;
            height: 10px;
            background: #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
        }

        .bar-fill {
            height: 10px;
            border-radius: 999px;
        }

        .bar-fill-blue { background: #2563eb; }
        .bar-fill-green { background: #16a34a; }
        .bar-fill-purple { background: #7c3aed; }
        .bar-fill-yellow { background: #d97706; }
        .bar-fill-slate { background: #475569; }

        .summary-table th,
        .summary-table td,
        .data-table th,
        .data-table td {
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            font-size: 11px;
        }

        .summary-table th,
        .data-table th {
            background: #eff6ff;
            color: #111827;
            font-weight: 800;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 22px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }

        .empty-note {
            padding: 12px;
            border: 1px dashed #d1d5db;
            color: #6b7280;
            text-align: center;
            border-radius: 8px;
        }

        .spacer-sm {
            height: 8px;
        }

        .spacer-md {
            height: 14px;
        }
    </style>
</head>
<body>
    @php
        $siteData = $site ?? [];
        $summaryCards = collect($summaryCards ?? []);
        $financeCards = collect($financeCards ?? []);
        $summaryRows = collect($summaryRows ?? []);
        $detailSections = collect($detailSections ?? []);
        $monthlyChart = $monthlyChart ?? [];
        $methodChart = $methodChart ?? [];
        $contributionTypeChart = $contributionTypeChart ?? [];

        $methodLabels = collect(data_get($methodChart, 'labels', []));
        $methodValues = $methodLabels->map(function ($label, $index) use ($methodChart) {
            return
                ((float) data_get($methodChart, 'tithes.' . $index, 0)) +
                ((float) data_get($methodChart, 'offerings.' . $index, 0)) +
                ((float) data_get($methodChart, 'cash.' . $index, 0)) +
                ((float) data_get($methodChart, 'bank.' . $index, 0));
        });
        $methodMax = max($methodValues->max() ?? 0, 1);

        $typeLabels = collect(data_get($contributionTypeChart, 'labels', []));
        $typeTotals = collect(data_get($contributionTypeChart, 'totals', []))->map(fn ($v) => (float) $v);
        $typeMax = max($typeTotals->max() ?? 0, 1);

        $monthlyLabels = collect(data_get($monthlyChart, 'labels', []));
        $monthlyTotals = $monthlyLabels->map(function ($label, $index) use ($monthlyChart) {
            return
                ((float) data_get($monthlyChart, 'tithes.' . $index, 0)) +
                ((float) data_get($monthlyChart, 'offerings.' . $index, 0)) +
                ((float) data_get($monthlyChart, 'cash.' . $index, 0)) +
                ((float) data_get($monthlyChart, 'bank.' . $index, 0));
        });
        $monthlyMax = max($monthlyTotals->max() ?? 0, 1);

        $colors = ['blue', 'green', 'purple', 'yellow', 'slate'];
    @endphp

    <div class="page">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell" style="text-align:left;">
                        @if(!empty($siteData['logo_data_uri']))
                            <img src="{{ $siteData['logo_data_uri'] }}" alt="Logo" class="logo">
                        @endif
                    </td>

                    <td class="header-center">
                        <div class="site-name">{{ $siteData['site_name'] ?? config('app.name') }}</div>

                        @if(!empty($siteData['site_tagline']))
                            <div class="site-tagline">{{ $siteData['site_tagline'] }}</div>
                        @endif

                        @if(!empty($siteData['church_address']))
                            <div class="site-meta">
                                {{ app()->getLocale() === 'sw' ? 'Anwani' : 'Address' }}:
                                {{ $siteData['church_address'] }}
                            </div>
                        @endif

                        @if(!empty($siteData['church_phone']) || !empty($siteData['church_email']))
                            <div class="site-meta">
                                @if(!empty($siteData['church_phone']))
                                    {{ app()->getLocale() === 'sw' ? 'Simu ya mezani' : 'Phone' }}:
                                    {{ $siteData['church_phone'] }}
                                @endif

                                @if(!empty($siteData['church_phone']) && !empty($siteData['church_email']))
                                    |
                                @endif

                                @if(!empty($siteData['church_email']))
                                    {{ app()->getLocale() === 'sw' ? 'Barua pepe' : 'Email' }}:
                                    {{ $siteData['church_email'] }}
                                @endif
                            </div>
                        @endif

                        <div class="report-title">{{ data_get($report, 'title', db_trans('report')) }}</div>

                        @if(!empty(data_get($report, 'subtitle')))
                            <div class="report-subtitle">{{ data_get($report, 'subtitle') }}</div>
                        @endif
                    </td>

                    <td class="logo-cell" style="text-align:right;">
                        @if(!empty($siteData['logo_data_uri']))
                            <img src="{{ $siteData['logo_data_uri'] }}" alt="Logo" class="logo">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <table class="meta-grid">
                <tr>
                    <td>
                        <div class="meta-card">
                            <div class="meta-label">{{ db_trans('scope') ?: 'Scope' }}</div>
                            <div class="meta-value">{{ data_get($report, 'scope_name', '-') }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="meta-card">
                            <div class="meta-label">{{ db_trans('period') ?: 'Period' }}</div>
                            <div class="meta-value">{{ data_get($report, 'period_label', '-') }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="meta-card">
                            <div class="meta-label">{{ db_trans('generated_on') ?: 'Generated On' }}</div>
                            <div class="meta-value">{{ data_get($report, 'generated_at', '-') }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="meta-card">
                            <div class="meta-label">{{ db_trans('generated_by') ?: 'Generated By' }}</div>
                            <div class="meta-value">{{ data_get($report, 'generated_by', '-') }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        @if($summaryCards->isNotEmpty())
            <div class="section">
                <h3 class="section-title">{{ db_trans('summary') ?: 'Summary' }}</h3>
                <table class="cards-table">
                    @foreach($summaryCards->chunk(4) as $row)
                        <tr>
                            @foreach($row as $card)
                                <td>
                                    <div class="card">
                                        <div class="card-label">{{ $card['label'] ?? '' }}</div>
                                        <div class="card-value">{{ $card['value'] ?? '0' }}</div>
                                    </div>
                                </td>
                            @endforeach
                            @for($i = $row->count(); $i < 4; $i++)
                                <td></td>
                            @endfor
                        </tr>
                        @if(! $loop->last)
                            <tr><td colspan="4" class="spacer-sm"></td></tr>
                        @endif
                    @endforeach
                </table>
            </div>
        @endif

        @if($financeCards->isNotEmpty())
            <div class="section">
                <h3 class="section-title">{{ db_trans('financial_summary') ?: 'Financial Summary' }}</h3>
                <table class="cards-table">
                    @foreach($financeCards->chunk(3) as $row)
                        <tr>
                            @foreach($row as $card)
                                <td style="width:33.3333%;">
                                    <div class="card">
                                        <div class="card-label">{{ $card['label'] ?? '' }}</div>
                                        <div class="card-value">{{ $card['value'] ?? '0.00' }}</div>
                                    </div>
                                </td>
                            @endforeach
                            @for($i = $row->count(); $i < 3; $i++)
                                <td></td>
                            @endfor
                        </tr>
                        @if(! $loop->last)
                            <tr><td colspan="3" class="spacer-sm"></td></tr>
                        @endif
                    @endforeach
                </table>
            </div>
        @endif

        <div class="section">
            <table class="two-col">
                <tr>
                    <td style="width:50%;">
                        <div class="chart-box">
                            <div class="section-title" style="border-bottom:none; margin-bottom:8px;">
                                {{ db_trans('finance_by_kanda') ?: 'Finance Overview' }}
                            </div>

                            @if($methodLabels->isNotEmpty())
                                <div class="bar-chart">
                                    @foreach($methodLabels as $index => $label)
                                        @php
                                            $value = (float) ($methodValues[$index] ?? 0);
                                            $width = $methodMax > 0 ? max(($value / $methodMax) * 100, 2) : 0;
                                            $color = $colors[$index % count($colors)];
                                        @endphp
                                        <div class="bar-row">
                                            <div class="bar-label-wrap">
                                                <span>{{ $label }}</span>
                                                <span>{{ number_format($value, 2) }}</span>
                                            </div>
                                            <div class="bar-track">
                                                <div class="bar-fill bar-fill-{{ $color }}" style="width: {{ $width }}%;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-note">{{ db_trans('no_data_available') ?: 'No data available' }}</div>
                            @endif
                        </div>
                    </td>

                    <td style="width:50%;">
                        <div class="chart-box">
                            <div class="section-title" style="border-bottom:none; margin-bottom:8px;">
                                {{ db_trans('contribution_type_analysis') ?: 'Contribution Type Analysis' }}
                            </div>

                            @if($typeLabels->isNotEmpty())
                                <div class="bar-chart">
                                    @foreach($typeLabels as $index => $label)
                                        @php
                                            $value = (float) ($typeTotals[$index] ?? 0);
                                            $width = $typeMax > 0 ? max(($value / $typeMax) * 100, 2) : 0;
                                            $color = $colors[$index % count($colors)];
                                        @endphp
                                        <div class="bar-row">
                                            <div class="bar-label-wrap">
                                                <span>{{ $label }}</span>
                                                <span>{{ number_format($value, 2) }}</span>
                                            </div>
                                            <div class="bar-track">
                                                <div class="bar-fill bar-fill-{{ $color }}" style="width: {{ $width }}%;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-note">{{ db_trans('no_data_available') ?: 'No data available' }}</div>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="chart-box">
                <div class="section-title" style="border-bottom:none; margin-bottom:8px;">
                    {{ db_trans('monthly_finance_trends') ?: 'Monthly Finance Trends' }}
                </div>

                @if($monthlyLabels->isNotEmpty())
                    <div class="bar-chart">
                        @foreach($monthlyLabels as $index => $label)
                            @php
                                $value = (float) ($monthlyTotals[$index] ?? 0);
                                $width = $monthlyMax > 0 ? max(($value / $monthlyMax) * 100, 2) : 0;
                            @endphp
                            <div class="bar-row">
                                <div class="bar-label-wrap">
                                    <span>{{ $label }}</span>
                                    <span>{{ number_format($value, 2) }}</span>
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill bar-fill-blue" style="width: {{ $width }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="spacer-md"></div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ db_trans('month') ?: 'Month' }}</th>
                                <th class="text-right">{{ db_trans('tithes') }}</th>
                                <th class="text-right">{{ db_trans('offerings') }}</th>
                                <th class="text-right">{{ db_trans('cash_contributions') }}</th>
                                <th class="text-right">{{ db_trans('bank_contributions') }}</th>
                                <th class="text-right">{{ db_trans('grand_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyLabels as $index => $label)
                                @php
                                    $tithe = (float) data_get($monthlyChart, 'tithes.' . $index, 0);
                                    $offering = (float) data_get($monthlyChart, 'offerings.' . $index, 0);
                                    $cash = (float) data_get($monthlyChart, 'cash.' . $index, 0);
                                    $bank = (float) data_get($monthlyChart, 'bank.' . $index, 0);
                                    $total = $tithe + $offering + $cash + $bank;
                                @endphp
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-right">{{ number_format($tithe, 2) }}</td>
                                    <td class="text-right">{{ number_format($offering, 2) }}</td>
                                    <td class="text-right">{{ number_format($cash, 2) }}</td>
                                    <td class="text-right">{{ number_format($bank, 2) }}</td>
                                    <td class="text-right">{{ number_format($total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-note">{{ db_trans('no_data_available') ?: 'No data available' }}</div>
                @endif
            </div>
        </div>

        <div class="section">
            <h3 class="section-title">{{ $summaryTableTitle ?? (db_trans('details') ?: 'Details') }}</h3>

            @if($summaryRows->isNotEmpty())
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>{{ db_trans('kanda') ?: 'Kanda' }}</th>
                            <th>{{ db_trans('jumuiyas') ?: 'Jumuiyas' }}</th>
                            <th>{{ db_trans('familias') ?: 'Familias' }}</th>
                            <th>{{ db_trans('members') ?: 'Members' }}</th>
                            <th class="text-right">{{ db_trans('offerings') }}</th>
                            <th class="text-right">{{ db_trans('tithes') }}</th>
                            <th class="text-right">{{ db_trans('mavuno') ?: 'Mavuno' }}</th>
                            <th class="text-right">{{ db_trans('grand_total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summaryRows as $row)
                            <tr>
                                <td>{{ $row['name'] ?? '-' }}</td>
                                <td>{{ number_format((int) ($row['jumuiyas_count'] ?? 0)) }}</td>
                                <td>{{ number_format((int) ($row['familias_count'] ?? 0)) }}</td>
                                <td>{{ number_format((int) ($row['members_count'] ?? 0)) }}</td>
                                <td class="text-right">{{ number_format((float) ($row['offering_total'] ?? 0), 2) }}</td>
                                <td class="text-right">{{ number_format((float) ($row['tithe_total'] ?? 0), 2) }}</td>
                                <td class="text-right">{{ number_format((float) ($row['mavuno_total'] ?? 0), 2) }}</td>
                                <td class="text-right">{{ number_format((float) ($row['grand_total'] ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-note">{{ db_trans('no_data_available') ?: 'No data available' }}</div>
            @endif
        </div>

        @if($detailSections->isNotEmpty())
            @foreach($detailSections as $section)
                <div class="section">
                    <h3 class="section-title">{{ $section['title'] ?? db_trans('details') }}</h3>

                    @if(!empty($section['subtitle']))
                        <div style="margin-bottom:8px; color:#6b7280; font-size:11px;">
                            {{ $section['subtitle'] }}
                        </div>
                    @endif

                    @php
                        $rows = collect($section['rows'] ?? []);
                    @endphp

                    @if($rows->isNotEmpty())
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ db_trans('jumuiya') ?: 'Jumuiya' }}</th>
                                    <th>{{ db_trans('familias') ?: 'Familias' }}</th>
                                    <th>{{ db_trans('members') ?: 'Members' }}</th>
                                    <th class="text-right">{{ db_trans('offerings') }}</th>
                                    <th class="text-right">{{ db_trans('tithes') }}</th>
                                    <th class="text-right">{{ db_trans('mavuno') ?: 'Mavuno' }}</th>
                                    <th class="text-right">{{ db_trans('grand_total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                    <tr>
                                        <td>{{ $row['name'] ?? '-' }}</td>
                                        <td>{{ number_format((int) ($row['familias_count'] ?? 0)) }}</td>
                                        <td>{{ number_format((int) ($row['members_count'] ?? 0)) }}</td>
                                        <td class="text-right">{{ number_format((float) ($row['offering_total'] ?? 0), 2) }}</td>
                                        <td class="text-right">{{ number_format((float) ($row['tithe_total'] ?? 0), 2) }}</td>
                                        <td class="text-right">{{ number_format((float) ($row['mavuno_total'] ?? 0), 2) }}</td>
                                        <td class="text-right">{{ number_format((float) ($row['grand_total'] ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-note">{{ db_trans('no_data_available') ?: 'No data available' }}</div>
                    @endif
                </div>
            @endforeach
        @endif

        <div class="footer">
            {{ $siteData['site_name'] ?? config('app.name') }}
            —
            {{ db_trans('generated_on') ?: 'Generated on' }}
            {{ data_get($report, 'generated_at', now()->translatedFormat('d M Y H:i')) }}
        </div>
    </div>
</body>
</html>