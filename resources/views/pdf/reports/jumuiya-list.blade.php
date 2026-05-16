@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('jumuiya_list') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 6%;">#</th>
                    <th style="width: 28%;">{{ db_trans('jumuiya') }}</th>
                    <th style="width: 24%;">{{ db_trans('kanda') }}</th>
                    <th class="text-right" style="width: 14%;">{{ db_trans('members') }}</th>
                    <th class="text-center" style="width: 14%;">{{ db_trans('status') }}</th>
                    <th class="text-center" style="width: 14%;">{{ db_trans('created_at') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->kanda_name }}</td>
                        <td class="text-right tabular-nums">{{ number_format($row->members_count) }}</td>
                        <td class="text-center">{{ $row->status_label }}</td>
                        <td class="text-center">{{ $row->created_at_label }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ db_trans('no_data_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="chart-section">
        <div class="section-header">
            <h3>{{ db_trans('analytics') }}</h3>
            <div class="accent-line"></div>
        </div>

        <table class="chart-grid">
            <tr>
                <td>
                    <div class="chart-card">
                        <div class="chart-card-title">{{ db_trans('members_distribution') }}</div>

                        @if($topByMembers->isNotEmpty())
                            @php $maxMembers = max(1, (int) $topByMembers->max('members_count')); @endphp

                            <div class="bar-chart">
                                @foreach($topByMembers as $item)
                                    @php
                                        $percent = $maxMembers > 0 ? round(($item->members_count / $maxMembers) * 100, 2) : 0;
                                    @endphp
                                    <div class="bar-row">
                                        <div class="bar-label-line">
                                            <span class="bar-label-name">{{ $item->name }}</span>
                                            <span class="bar-label-value">{{ number_format($item->members_count) }}</span>
                                        </div>
                                        <div class="bar-track">
                                            <div class="bar-fill bar-fill-jumuiya" style="width: {{ $percent }}%;"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-chart-note">{{ db_trans('no_data_found') }}</div>
                        @endif
                    </div>
                </td>

                <td>
                    <div class="chart-card">
                        <div class="chart-card-title">{{ db_trans('families_count') }}</div>

                        @if($topByFamilias->isNotEmpty())
                            @php $maxFamilias = max(1, (int) $topByFamilias->max('familias_count')); @endphp

                            <div class="bar-chart">
                                @foreach($topByFamilias as $item)
                                    @php
                                        $percent = $maxFamilias > 0 ? round(($item->familias_count / $maxFamilias) * 100, 2) : 0;
                                    @endphp
                                    <div class="bar-row">
                                        <div class="bar-label-line">
                                            <span class="bar-label-name">{{ $item->name }}</span>
                                            <span class="bar-label-value">{{ number_format($item->familias_count) }}</span>
                                        </div>
                                        <div class="bar-track">
                                            <div class="bar-fill bar-fill-kanda" style="width: {{ $percent }}%;"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-chart-note">{{ db_trans('no_data_found') }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>
@endsection