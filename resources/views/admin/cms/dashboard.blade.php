@extends('layouts.admin')

@section('title', db_trans('content_management_center'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    <div class="cms-shell">
        <div class="cms-hero cms-hero-dashboard">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <span class="cms-hero-badge">
                        <i class="fas fa-layer-group"></i>
                        {{ db_trans('cms') }}
                    </span>

                    <h2 class="cms-hero-title">
                        {{ $page['title'] ?? db_trans('content_management_center') }}
                    </h2>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="cms-hero-pill">
                            <i class="fas fa-clock"></i>
                            {{ db_trans('last_updated') }}:
                            {{ optional($page['updated_at'] ?? now())->format('d M Y, h:i A') }}
                        </span>
                    </div>
                </div>

                <div class="cms-quick-links">
                    @can('cms.homepage.view')
                        <a href="{{ route('cms.homepage.edit') }}" class="btn btn-light">
                            <i class="fas fa-home me-2"></i>{{ db_trans('homepage_builder') }}
                        </a>
                    @endcan

                    @can('cms.heroes.view')
                        <a href="{{ route('cms.heroes.index') }}" class="btn btn-outline-light">
                            <i class="fas fa-images me-2"></i>{{ db_trans('hero_banners') }}
                        </a>
                    @endcan

                    @can('cms.histories.view')
                        <a href="{{ route('cms.histories.index') }}" class="btn btn-outline-light">
                            <i class="fas fa-landmark me-2"></i>{{ db_trans('histories') }}
                        </a>
                    @endcan

                    <a href="{{ url('/cms/footer-center-details') }}" class="btn btn-outline-light">
                        <i class="fas fa-shoe-prints me-2"></i>{{ db_trans('footer_center_details') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @foreach($kpis as $card)
                <div class="col-xxl-2 col-xl-4 col-md-6">
                    <div class="card cms-kpi-card">
                        <div class="card-body">
                            <div class="cms-kpi-top">
                                <span class="cms-kpi-icon tone-{{ $card['tone'] }}">
                                    <i class="{{ $card['icon'] }}"></i>
                                </span>
                            </div>

                            <div class="cms-kpi-title">
                                {{ $card['title'] }}
                            </div>

                            <div class="cms-kpi-value">
                                {{ $card['value'] }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card cms-panel">
            <div class="card-body">
                <div class="cms-panel-head">
                    <div>
                        <h4 class="cms-panel-title">{{ db_trans('visitor_trend') }}</h4>
                    </div>

                    <span class="cms-panel-icon">
                        <i class="fas fa-chart-line"></i>
                    </span>
                </div>

                <div class="cms-chart-shell">
                    <canvas id="cmsVisitorTrendChart"></canvas>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @php
                $recentGroups = [
                    [
                        'title' => db_trans('recent_hero_banners'),
                        'items' => $recent['hero_banners'],
                        'route' => 'cms.heroes.index',
                    ],
                    [
                        'title' => db_trans('recent_histories'),
                        'items' => $recent['histories'],
                        'route' => 'cms.histories.index',
                    ],
                    [
                        'title' => db_trans('recent_announcements'),
                        'items' => $recent['announcements'],
                        'route' => 'cms.announcements.index',
                    ],
                    [
                        'title' => db_trans('recent_pages'),
                        'items' => $recent['pages'],
                        'route' => 'cms.pages.index',
                    ],
                    [
                        'title' => db_trans('recent_galleries'),
                        'items' => $recent['galleries'],
                        'route' => 'cms.galleries.index',
                    ],
                ];
            @endphp

            @foreach($recentGroups as $group)
                <div class="col-xl-4">
                    <div class="card cms-panel">
                        <div class="card-body">
                            <div class="cms-panel-head">
                                <div>
                                    <h4 class="cms-panel-title">
                                        {{ $group['title'] }}
                                    </h4>
                                </div>
                            </div>

                            <div class="cms-list">
                                @forelse($group['items'] as $item)
                                    <div class="cms-list-item">
                                        <div>
                                            <div class="cms-list-title">
                                                {{ $item->title }}
                                            </div>

                                            <div class="cms-list-sub">
                                                {{ optional($item->created_at)->format('d M Y') }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">
                                        {{ db_trans('no_records_found') }}
                                    </div>
                                @endforelse
                            </div>

                            <div class="mt-3">
                                <a href="{{ route($group['route']) }}" class="btn btn-outline-secondary w-100">
                                    {{ db_trans('view_all') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/cms.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('cmsVisitorTrendChart');

            if (!ctx || typeof Chart === 'undefined') {
                return;
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json(data_get($visitorTrend ?? [], 'labels', [])),
                    datasets: [{
                        label: @json(db_trans('visitors')),
                        data: @json(data_get($visitorTrend ?? [], 'values', [])),
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, .14)',
                        pointBackgroundColor: '#7c3aed',
                        fill: true,
                        tension: .35,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
@endpush