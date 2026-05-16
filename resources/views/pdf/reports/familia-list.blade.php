@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('family_directory') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 6%;">#</th>
                    <th style="width: 24%;">{{ db_trans('family') }}</th>
                    <th style="width: 22%;">{{ db_trans('jumuiya') }}</th>
                    <th style="width: 16%;">{{ db_trans('phone') }}</th>
                    <th style="width: 14%;">{{ db_trans('envelope_no') }}</th>
                    <th class="text-right" style="width: 10%;">{{ db_trans('members_count') }}</th>
                    <th class="text-center" style="width: 8%;">{{ db_trans('status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->jumuiya_name }}</td>
                        <td>{{ $row->phone }}</td>
                        <td>{{ $row->envelope_no }}</td>
                        <td class="text-right tabular-nums">{{ number_format($row->members_count) }}</td>
                        <td class="text-center">{{ $row->status_label }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ db_trans('no_data_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="chart-section">
        <div class="section-header">
            <h3>{{ db_trans('families_distribution_by_jumuiya') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="chart-card">
            @if($jumuiyaDistribution->isNotEmpty())
                @php $maxFamilias = max(1, (int) $jumuiyaDistribution->max('familias_count')); @endphp

                <div class="bar-chart">
                    @foreach($jumuiyaDistribution as $item)
                        @php
                            $percent = $maxFamilias > 0 ? round(($item->familias_count / $maxFamilias) * 100, 2) : 0;
                        @endphp

                        <div class="bar-row">
                            <div class="bar-label-line">
                                <span class="bar-label-name">{{ $item->name }}</span>
                                <span class="bar-label-value">{{ number_format($item->familias_count) }}</span>
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
    </div>
@endsection