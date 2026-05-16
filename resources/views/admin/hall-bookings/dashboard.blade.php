@extends('layouts.admin')

@section('title', db_trans('hall_booking_center'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/hall-booking-v1.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="hall-booking-module">
    <section class="hb-hero mb-4">
        <div>
            <span class="hb-hero-badge"><i class="fas fa-building"></i> {{ db_trans('hall_booking') }}</span>
            <h2 class="hb-hero-title">{{ $page['title'] }}</h2>
            <p class="hb-hero-subtitle">{{ $page['subtitle'] }}</p>
        </div>
        <div class="hb-hero-actions">
            @can('hall-bookings.halls.view')
                <a href="{{ route('hall-bookings.halls.index') }}" class="hb-hero-action"><i class="fas fa-building"></i>{{ db_trans('halls') }}</a>
            @endcan
            @can('hall-bookings.bookings.view')
                <a href="{{ route('hall-bookings.bookings.index') }}" class="hb-hero-action"><i class="fas fa-calendar-check"></i>{{ db_trans('bookings') }}</a>
            @endcan
            @can('hall-bookings.prices.manage')
                <a href="{{ route('hall-bookings.prices.index') }}" class="hb-hero-action"><i class="fas fa-tags"></i>{{ db_trans('hall_prices') }}</a>
            @endcan
            @can('hall-bookings.blocked-dates.manage')
                <a href="{{ route('hall-bookings.blocked-dates.index') }}" class="hb-hero-action"><i class="fas fa-ban"></i>{{ db_trans('blocked_dates') }}</a>
            @endcan
        </div>
    </section>

    <div class="row g-4 mb-4">
        @foreach($kpis as $card)
            <div class="col-xl-3 col-md-6">
                <div class="card hb-kpi-card hb-tone-{{ $card['tone'] }} border-0 h-100">
                    <div class="card-body">
                        <div class="hb-kpi-top">
                            <span class="hb-kpi-icon"><i class="{{ $card['icon'] }}"></i></span>
                            <span class="hb-kpi-chip">{{ db_trans('overview') }}</span>
                        </div>
                        <div class="hb-kpi-label">{{ $card['label'] }}</div>
                        <div class="hb-kpi-value">{{ number_format((float) $card['value']) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card hb-panel border-0 h-100">
                <div class="card-body">
                    <div class="hb-panel-head"><h5>{{ db_trans('booking_trend') }}</h5><span class="hb-panel-badge">{{ now()->year }}</span></div>
                    <div class="hb-chart-shell"><canvas id="hbMonthlyChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card hb-panel border-0 h-100">
                <div class="card-body">
                    <div class="hb-panel-head"><h5>{{ db_trans('booking_status_distribution') }}</h5><span class="hb-panel-badge">{{ db_trans('status') }}</span></div>
                    <div class="hb-chart-shell"><canvas id="hbStatusChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            @include('admin.hall-bookings.partials.dashboard-table', ['title' => db_trans('latest_bookings'), 'rows' => $latestBookings])
        </div>
        <div class="col-xl-4">
            @include('admin.hall-bookings.partials.dashboard-table', ['title' => db_trans('upcoming_booked_dates'), 'rows' => $upcomingBookings])
        </div>
        <div class="col-xl-4">
            @include('admin.hall-bookings.partials.dashboard-table', ['title' => db_trans('pending_payment_verification'), 'rows' => $pendingPayments])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
window.hallBookingDashboardData = {
    monthly: @json($monthlyChart),
    status: @json($statusChart)
};
</script>
<script src="{{ asset('admin/js/hall-booking-v1.js') }}"></script>
@endpush
