@extends('pdf.layouts.report')

@php
    use Illuminate\Support\Str;

    $locale = app()->getLocale() ?? 'sw';
    $isSwahili = str_starts_with(strtolower($locale), 'sw');

    $types = collect($breakdownContributionTypes ?? [])->values();
    $matrixRows = collect($pdfMatrixRows ?? $kandaBreakdownRows ?? [])->values();
    $pdfCharts = $pdfCharts ?? [];

    $topKandas = collect($pdfCharts['top_kandas'] ?? []);
    $topJumuiyas = collect($pdfCharts['top_jumuiyas'] ?? []);
    $incomeMix = collect($pdfCharts['income_mix'] ?? []);

    $typeNamesText = $types->pluck('name')->filter()->implode(', ');

    $reportTitle = $isSwahili
        ? trim('TAARIFA ZA ZAKA, SADAKA NA MICHANGO' . ($typeNamesText ? ' (' . Str::upper($typeNamesText) . ')' : ''))
        : trim('REPORT OF TITHES, OFFERINGS AND CONTRIBUTIONS' . ($typeNamesText ? ' (' . Str::upper($typeNamesText) . ')' : ''));

    $summaryHeading = $isSwahili ? 'Muhtasari wa Ripoti' : 'Report Summary';
    $periodLabel = $dateFilters['period_label'] ?? db_trans('all_time');
    $snLabel = 'S/N';
    $kandaLabel = db_trans('kanda');
    $jumuiyaBlockLabel = $isSwahili ? 'Jumuiya / Zaka / Sadaka / Michango' : 'Jumuiya / Tithes / Offerings / Contributions';
    $overallTotalsLabel = db_trans('overall_totals');

    $topKandaTitle = $isSwahili ? 'Kanda Zinazoongoza kwa Makusanyo' : 'Top Kandas by Collections';
    $topJumuiyaTitle = $isSwahili ? 'Jumuiya Zinazoongoza kwa Makusanyo' : 'Top Jumuiyas by Collections';
    $mixTitle = $isSwahili ? 'Mgawanyo wa Mapato' : 'Income Distribution';

    $maxKandaValue = max((float) $topKandas->max('value'), 1);
    $maxJumuiyaValue = max((float) $topJumuiyas->max('value'), 1);
    $mixTotal = max((float) $incomeMix->sum('value'), 1);
@endphp

@section('content')
    <div class="section-header">
        <h3>{{ $summaryHeading }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container report-meta-box">
        <table class="data-table">
            <tbody>
                <tr>
                    <th style="width: 24%;">{{ db_trans('report_period') }}</th>
                    <td>{{ $periodLabel }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="table-container matrix-table-container">
        <table class="data-table matrix-table">
            <thead>
                <tr>
                    <th style="width: 6%;">{{ $snLabel }}</th>
                    <th style="width: 20%;">{{ $kandaLabel }}</th>
                    <th style="width: 74%;">{{ $jumuiyaBlockLabel }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse($matrixRows as $row)
                    <tr>
                        <td class="matrix-top-cell text-center">{{ $row['sn'] ?? '' }}</td>

                        <td class="matrix-top-cell">
                            <div class="matrix-kanda-name">{{ $row['name'] }}</div>
                            <div class="matrix-kanda-meta">
                                {{ db_trans('jumuiyas') }}: {{ number_format($row['jumuiyas_count'] ?? 0) }}
                            </div>
                        </td>

                        <td class="matrix-no-padding">
                            <table class="nested-table">
                                <thead>
                                    <tr>
                                        <th style="width: 6%;">{{ $snLabel }}</th>
                                        <th style="width: 28%;">{{ db_trans('jumuiya') }}</th>
                                        <th class="text-right nowrap" style="width: 14%;">{{ db_trans('tithes') }}</th>
                                        <th class="text-right nowrap" style="width: 14%;">{{ db_trans('offerings') }}</th>

                                        @foreach($types as $type)
                                            <th class="text-right nowrap" style="width: 12%;">{{ $type['name'] }}</th>
                                        @endforeach

                                        <th class="text-right nowrap" style="width: 14%;">{{ db_trans('grand_total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($row['jumuiya_rows'] ?? []) as $jumuiya)
                                        <tr>
                                            <td>{{ $jumuiya['sn'] ?? '' }}</td>
                                            <td class="nested-jumuiya-name">{{ $jumuiya['name'] }}</td>
                                            <td class="text-right tabular-nums nowrap">
                                                {{ number_format($jumuiya['tithes'] ?? 0, 2) }}
                                            </td>
                                            <td class="text-right tabular-nums nowrap">
                                                {{ number_format($jumuiya['offerings'] ?? 0, 2) }}
                                            </td>

                                            @foreach($types as $type)
                                                <td class="text-right tabular-nums nowrap">
                                                    {{ number_format($jumuiya['contribution_type_totals'][$type['slug']]['amount'] ?? 0, 2) }}
                                                </td>
                                            @endforeach

                                            <td class="text-right tabular-nums nowrap nested-grand-total">
                                                {{ number_format($jumuiya['grand_total'] ?? 0, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 5 + $types->count() }}" class="text-center">
                                                {{ db_trans('no_jumuiyas_found') }}
                                            </td>
                                        </tr>
                                    @endforelse

                                    <tr class="nested-kanda-total">
                                        <td colspan="2">
                                            {{ db_trans('kanda_subtotal') }}: {{ $row['name'] }}
                                        </td>
                                        <td class="text-right tabular-nums nowrap">
                                            {{ number_format($row['totals']['tithes'] ?? 0, 2) }}
                                        </td>
                                        <td class="text-right tabular-nums nowrap">
                                            {{ number_format($row['totals']['offerings'] ?? 0, 2) }}
                                        </td>

                                        @foreach($types as $type)
                                            <td class="text-right tabular-nums nowrap">
                                                {{ number_format($row['totals']['contribution_type_totals'][$type['slug']]['amount'] ?? 0, 2) }}
                                            </td>
                                        @endforeach

                                        <td class="text-right tabular-nums nowrap">
                                            {{ number_format($row['totals']['grand_total'] ?? 0, 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">
                            {{ db_trans('no_kandas_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="2" style="font-weight: 800;">{{ $overallTotalsLabel }}</td>
                    <td class="matrix-no-padding">
                        <table class="nested-table">
                            <tbody>
                                <tr class="nested-kanda-total">
                                    <td colspan="2"></td>
                                    <td class="text-right tabular-nums nowrap">
                                        {{ number_format($globalTotals['tithes'] ?? 0, 2) }}
                                    </td>
                                    <td class="text-right tabular-nums nowrap">
                                        {{ number_format($globalTotals['offerings'] ?? 0, 2) }}
                                    </td>

                                    @foreach($types as $type)
                                        <td class="text-right tabular-nums nowrap">
                                            {{ number_format($globalTotals['contribution_type_totals'][$type['slug']]['amount'] ?? 0, 2) }}
                                        </td>
                                    @endforeach

                                    <td class="text-right tabular-nums nowrap">
                                        {{ number_format($globalTotals['grand_total'] ?? 0, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="chart-section">
        <div class="section-header">
            <h3>{{ $isSwahili ? 'Mchoro wa Takwimu' : 'Data Charts' }}</h3>
            <div class="accent-line"></div>
        </div>

        <table class="chart-grid">
            <tr>
                <td>
                    <div class="chart-card">
                        <div class="chart-card-title">{{ $topKandaTitle }}</div>

                        @if($topKandas->isNotEmpty())
                            <div class="bar-chart">
                                @foreach($topKandas as $item)
                                    @php
                                        $width = (($item['value'] ?? 0) / $maxKandaValue) * 100;
                                    @endphp
                                    <div class="bar-row">
                                        <div class="bar-label-line">
                                            <span class="bar-label-name">{{ $item['label'] }}</span>
                                            <span class="bar-label-value">{{ number_format((float) $item['value'], 2) }}</span>
                                        </div>
                                        <div class="bar-track">
                                            <div class="bar-fill bar-fill-kanda" style="width: {{ $width }}%;"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-chart-note">{{ db_trans('no_data_available') }}</div>
                        @endif
                    </div>
                </td>

                <td>
                    <div class="chart-card">
                        <div class="chart-card-title">{{ $topJumuiyaTitle }}</div>

                        @if($topJumuiyas->isNotEmpty())
                            <div class="bar-chart">
                                @foreach($topJumuiyas as $item)
                                    @php
                                        $width = (($item['value'] ?? 0) / $maxJumuiyaValue) * 100;
                                    @endphp
                                    <div class="bar-row">
                                        <div class="bar-label-line">
                                            <span class="bar-label-name">{{ $item['label'] }}</span>
                                            <span class="bar-label-value">{{ number_format((float) $item['value'], 2) }}</span>
                                        </div>
                                        <div class="bar-track">
                                            <div class="bar-fill bar-fill-jumuiya" style="width: {{ $width }}%;"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-chart-note">{{ db_trans('no_data_available') }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top: 12px;">
            <div class="chart-card">
                <div class="chart-card-title">{{ $mixTitle }}</div>

                @if($incomeMix->isNotEmpty())
                    <div class="pie-list">
                        @foreach($incomeMix as $index => $item)
                            @php
                                $width = (($item['value'] ?? 0) / $mixTotal) * 100;
                                $swatchClass = 'swatch-' . (($index % 8) + 1);
                            @endphp
                            <div class="pie-item">
                                <div class="pie-item-top">
                                    <span class="pie-swatch {{ $swatchClass }}"></span>
                                    <span class="pie-name">{{ $item['label'] }}</span>
                                    <span class="pie-value">
                                        {{ number_format((float) $item['value'], 2) }}
                                        ({{ number_format($width, 1) }}%)
                                    </span>
                                </div>
                                <div class="pie-track">
                                    <div class="pie-fill {{ $swatchClass }}" style="width: {{ $width }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-chart-note">{{ db_trans('no_data_available') }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection