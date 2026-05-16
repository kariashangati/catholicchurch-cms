@extends('layouts.admin')

@section('title', db_trans('dashboard'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/dashboard-v3.css') }}">
@endpush

@section('content')
    <div class="dashboard-v3">
        <div class="dashboard-hero mb-4">
            <div class="hero-pattern"></div>
            <div class="row align-items-center g-4 position-relative">
                <div class="col-lg-8">
                    <span class="dashboard-hero-badge">{{ $hero['eyebrow'] }}</span>
                    <h2 class="dashboard-title mb-2">{{ $page['title'] }}</h2>
                    <p class="dashboard-subtitle mb-3">
                        {{ $hero['title'] }} · {{ $page['today'] }}
                    </p>

                    <div class="hero-meta-wrap">
                        <span class="hero-pill">
                            <i class="fas fa-compass me-2"></i>{{ $hero['scope_badge'] }}
                        </span>
                        <span class="hero-pill hero-pill-warning">
                            <i class="fas fa-bell me-2"></i>{{ $hero['pending_items'] }} {{ db_trans('pending_items') }}
                        </span>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="hero-actions-grid">
                        @foreach($quickLinks as $link)
                            @if(!empty($link['can_open']))
                                <a href="{{ $link['route'] }}" class="hero-action-btn">
                                    <span class="hero-action-icon">
                                        <i class="{{ $link['icon'] }}"></i>
                                    </span>
                                    <span class="hero-action-text">{{ $link['label'] }}</span>
                                </a>
                            @else
                                <button type="button"
                                        class="hero-action-btn hero-action-btn-disabled"
                                        disabled
                                        title="{{ $link['disabled_reason'] ?? db_trans('permission_required') }}">
                                    <span class="hero-action-icon">
                                        <i class="{{ $link['icon'] }}"></i>
                                    </span>
                                    <span class="hero-action-text">{{ $link['label'] }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Main summary KPIs --}}
        <div class="row g-4 mb-4">
            @foreach($summaryCards as $card)
                <div class="col-xxl-2 col-xl-4 col-md-6">
                    @if(!empty($card['can_open']) && !empty($card['url']))
                        <a href="{{ $card['url'] }}" class="text-decoration-none text-reset d-block h-100">
                            <div class="card stat-card stat-card-{{ $card['tone'] }} h-100 border-0 stat-card-link">
                                <div class="card-body">
                                    <div class="stat-top-row">
                                        <div class="stat-icon">
                                            <i class="{{ $card['icon'] }}"></i>
                                        </div>
                                        <span class="stat-chip">{{ db_trans('overview') }}</span>
                                    </div>

                                    <div class="stat-label">{{ $card['title'] }}</div>
                                    <div class="stat-number">{{ $card['value'] }}</div>

                                    <div class="mt-3">
                                        <span class="btn btn-sm btn-light btn-modern">
                                            {{ $card['link_label'] ?? db_trans('open') }}
                                            <i class="fas fa-arrow-right ms-1"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @else
                        <div class="card stat-card stat-card-{{ $card['tone'] }} h-100 border-0 stat-card-disabled"
                             title="{{ $card['disabled_reason'] ?? db_trans('permission_required') }}">
                            <div class="card-body">
                                <div class="stat-top-row">
                                    <div class="stat-icon">
                                        <i class="{{ $card['icon'] }}"></i>
                                    </div>
                                    <span class="stat-chip">{{ db_trans('overview') }}</span>
                                </div>

                                <div class="stat-label">{{ $card['title'] }}</div>
                                <div class="stat-number">{{ $card['value'] }}</div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-light btn-modern" disabled>
                                        {{ $card['link_label'] ?? db_trans('open') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Secondary KPIs --}}
        <div class="row g-4 mb-4">
            @foreach($genderCards as $card)
                <div class="col-xl-3 col-md-6">
                    <div class="card mini-kpi-card h-100 border-0">
                        <div class="card-body">
                            <div class="mini-kpi-icon tone-{{ $card['tone'] }}">
                                <i class="{{ $card['icon'] }}"></i>
                            </div>
                            <div class="mini-kpi-label">{{ $card['title'] }}</div>
                            <div class="mini-kpi-value">{{ $card['value'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-xl-2 col-md-4">
                <div class="card mini-kpi-card h-100 border-0">
                    <div class="card-body">
                        <div class="mini-kpi-label">{{ db_trans('visitors_today') }}</div>
                        <div class="mini-kpi-value">{{ number_format($visitorCards['today']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4">
                <div class="card mini-kpi-card h-100 border-0">
                    <div class="card-body">
                        <div class="mini-kpi-label">{{ db_trans('visitors_this_month') }}</div>
                        <div class="mini-kpi-value">{{ number_format($visitorCards['month']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4">
                <div class="card mini-kpi-card h-100 border-0">
                    <div class="card-body">
                        <div class="mini-kpi-label">{{ db_trans('visitors_this_year') }}</div>
                        <div class="mini-kpi-value">{{ number_format($visitorCards['year']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Finance KPI band --}}
        <div class="dashboard-section mb-4">
            <div class="section-heading mb-3">
                <div>
                    <h4 class="section-title">{{ db_trans('finance') }}</h4>
                </div>
            </div>

            <div class="row g-4">
                @foreach($financeCards as $card)
                    <div class="col-xl-4 col-md-6">
                        <div class="card finance-card finance-card-{{ $card['tone'] }} h-100 border-0 {{ empty($card['can_open']) ? 'finance-card-disabled' : '' }}"
                             @if(empty($card['can_open'])) title="{{ $card['disabled_reason'] ?? db_trans('permission_required') }}" @endif>
                            <div class="card-body d-flex flex-column">
                                <div class="finance-card-top">
                                    <div class="finance-card-icon">
                                        <i class="{{ $card['icon'] }}"></i>
                                    </div>
                                    <span class="finance-chip">{{ db_trans('finance') }}</span>
                                </div>

                                <div class="finance-card-title">{{ $card['title'] }}</div>
                                <div class="finance-card-value">{{ $card['value'] }}</div>

                                <div class="mt-4 pt-2">
                                    @if(!empty($card['can_open']) && !empty($card['url']))
                                        <a href="{{ $card['url'] }}" class="btn btn-sm btn-light btn-modern">
                                            {{ $card['link_label'] ?? db_trans('open') }}
                                            <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm btn-light btn-modern" disabled>
                                            {{ $card['link_label'] ?? db_trans('open') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Chart row 1 --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card analytics-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('member_growth') }}</h5>
                            </div>
                            <div class="panel-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>

                        <div class="chart-shell chart-shell-lg">
                            <canvas id="memberChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card quick-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head mb-4">
                            <div>
                                <h5 class="panel-title">{{ db_trans('quick_actions') }}</h5>
                                <p class="panel-subtitle">{{ db_trans('shortcuts') }}</p>
                            </div>
                            <span class="section-badge">{{ db_trans('shortcuts') }}</span>
                        </div>

                        @forelse($quickLinks as $link)
                            @if(!empty($link['can_open']))
                                <a href="{{ $link['route'] }}" class="quick-link-card">
                                    <div class="quick-link-left">
                                        <span class="quick-link-icon">
                                            <i class="{{ $link['icon'] }}"></i>
                                        </span>
                                        <span class="quick-link-label">{{ $link['label'] }}</span>
                                    </div>
                                    <i class="fas fa-arrow-right quick-link-arrow"></i>
                                </a>
                            @else
                                <div class="quick-link-card quick-link-card-disabled"
                                     title="{{ $link['disabled_reason'] ?? db_trans('permission_required') }}">
                                    <div class="quick-link-left">
                                        <span class="quick-link-icon">
                                            <i class="{{ $link['icon'] }}"></i>
                                        </span>
                                        <span class="quick-link-label">{{ $link['label'] }}</span>
                                    </div>
                                    <i class="fas fa-lock quick-link-arrow"></i>
                                </div>
                            @endif
                        @empty
                            <div class="empty-note">{{ db_trans('no_quick_actions_available') }}</div>
                        @endforelse

                        <div class="meta-grid mt-4">
                            <div class="meta-mini-card">
                                <div class="meta-mini-label">{{ db_trans('current_language') }}</div>
                                <div class="meta-mini-value">{{ strtoupper(app()->getLocale()) }}</div>
                            </div>
                            <div class="meta-mini-card">
                                <div class="meta-mini-label">{{ db_trans('scope') }}</div>
                                <div class="meta-mini-value meta-mini-value-sm">{{ $page['scope_label'] }}</div>
                            </div>
                        </div>

                        <div class="dashboard-note-box mt-4">
                            <div class="dashboard-note-title">{{ db_trans('current_language') }}</div>
                            <div class="dashboard-note-text">{{ db_trans('you_can_switch_language_from_the_top_bar') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart row 2 --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card analytics-panel border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('finance_trends') }}</h5>
                            </div>
                            <div class="panel-icon">
                                <i class="fas fa-chart-column"></i>
                            </div>
                        </div>
                        <div class="chart-shell">
                            <canvas id="financeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card analytics-panel border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('visitor_trend') }}</h5>
                            </div>
                            <div class="panel-icon">
                                <i class="fas fa-signal"></i>
                            </div>
                        </div>
                        <div class="chart-shell">
                            <canvas id="visitorChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart row 3 --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card analytics-panel border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('sacrament_distribution') }}</h5>
                            </div>
                            <div class="panel-icon">
                                <i class="fas fa-cross"></i>
                            </div>
                        </div>
                        <div class="chart-shell">
                            <canvas id="sacramentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card analytics-panel border-0">
                    <div class="card-body p-4">
                        <div class="panel-head">
                            <div>
                                <h5 class="panel-title">{{ db_trans('finance_mix') }}</h5>
                            </div>
                            <div class="panel-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                        </div>
                        <div class="chart-shell">
                            <canvas id="mixChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity / schedules / alerts --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-4">
                <div class="card dashboard-side-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head mb-4">
                            <div>
                                <h5 class="panel-title">{{ db_trans('live_activity_feed') }}</h5>
                            </div>
                        </div>

                        @forelse($liveFeed as $item)
                            <div class="feed-item">
                                <div class="feed-icon tone-{{ $item['tone'] }}">
                                    <i class="{{ $item['icon'] }}"></i>
                                </div>
                                <div class="feed-content">
                                    <div class="feed-title">{{ $item['title'] }}</div>
                                    <div class="feed-text">{{ $item['text'] }}</div>
                                    <div class="feed-time">{{ $item['time'] }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-note">{{ db_trans('no_recent_activity_found') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card dashboard-side-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head mb-4">
                            <div>
                                <h5 class="panel-title">{{ db_trans('upcoming_mass_schedules') }}</h5>
                            </div>
                        </div>

                        @forelse($upcomingSchedules as $schedule)
                            <div class="schedule-card">
                                <div class="schedule-card-top">
                                    <div>
                                        <div class="schedule-title">{{ $schedule->title }}</div>
                                        <div class="schedule-meta">
                                            {{ optional($schedule->massType)->name ?? db_trans('not_available_short') }}
                                            @if($schedule->location)
                                                · {{ $schedule->location }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="schedule-time">
                                        {{ optional($schedule->scheduled_at)->format('d M Y') }}<br>
                                        {{ optional($schedule->scheduled_at)->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-note">{{ db_trans('no_upcoming_mass_schedules') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card dashboard-side-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head mb-4">
                            <div>
                                <h5 class="panel-title">{{ db_trans('attention_needed') }}</h5>
                            </div>
                        </div>

                        @forelse($alerts as $alert)
                            @if(!empty($alert['can_open']) && !empty($alert['url']))
                                <a href="{{ $alert['url'] }}" class="alert-link-card">
                                    <span class="alert-link-label">{{ $alert['label'] }}</span>
                                    <span class="alert-link-badge">{{ $alert['value'] }}</span>
                                </a>
                            @else
                                <div class="alert-link-card alert-link-card-disabled"
                                     title="{{ $alert['disabled_reason'] ?? db_trans('permission_required') }}">
                                    <span class="alert-link-label">{{ $alert['label'] }}</span>
                                    <span class="alert-link-badge">{{ $alert['value'] }}</span>
                                </div>
                            @endif
                        @empty
                            <div class="empty-note">{{ db_trans('no_alerts_available') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent members --}}
        <div class="card table-panel border-0 mb-4">
            <div class="card-header table-panel-header border-0 p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h5 class="panel-title mb-1">{{ db_trans('recent_members') }}</h5>
                    </div>
                    @if(Route::has('members.index') && auth()->user()->can('members.view'))
                        <a href="{{ route('members.index') }}" class="btn btn-outline-primary btn-modern">
                            {{ db_trans('view_all') }} <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-primary btn-modern" disabled
                                title="{{ db_trans('you_do_not_have_permission_to_view_this_section') }}">
                            {{ db_trans('view_all') }} <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                    @endif
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive dashboard-table-wrap">
                    <table class="table align-middle table-hover mb-0 dashboard-table">
                        <thead>
                            <tr>
                                <th>{{ db_trans('member') }}</th>
                                <th>{{ db_trans('gender') }}</th>
                                <th>{{ db_trans('phone') }}</th>
                                <th>{{ db_trans('jumuiya') }}</th>
                                <th>{{ db_trans('joined') }}</th>
                                <th>{{ db_trans('status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentMembers as $member)
                                @php
                                    $fullName = trim(collect([$member->first_name, $member->middle_name, $member->last_name])->filter()->implode(' '));
                                    $jumuiyaName = optional(optional($member->familia)->jumuiya)->name;
                                    $na = db_trans('not_available_short');
                                    $genderRaw = strtolower((string) ($member->gender ?? ''));
                                    $genderLabel = match ($genderRaw) {
                                        'male' => db_trans('male'),
                                        'female' => db_trans('female'),
                                        default => $na,
                                    };
                                    $genderBadgeClass = $genderRaw === 'male' ? 'text-bg-info' : ($genderRaw === 'female' ? 'text-bg-success' : 'text-bg-light');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="member-avatar-sm me-3">
                                                {{ strtoupper(substr($member->first_name ?? 'X', 0, 1)) }}{{ strtoupper(substr($member->last_name ?? 'X', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $fullName }}</div>
                                                <div class="small text-muted">{{ db_trans('member_id') }}: {{ $member->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill px-3 py-2 {{ $genderBadgeClass }}">
                                            {{ $genderLabel }}
                                        </span>
                                    </td>
                                    <td>{{ $member->phone ?: $na }}</td>
                                    <td>{{ $jumuiyaName ?: $na }}</td>
                                    <td>{{ optional($member->created_at)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge rounded-pill text-bg-light">{{ db_trans('active') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        {{ db_trans('no_members_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Kanda / Jumuiya matrix --}}
        <div class="card table-panel border-0">
            <div class="card-header table-panel-header border-0 p-4">
                <div>
                    <h5 class="panel-title mb-1">{{ db_trans('kanda_and_jumuiya_performance') }}</h5>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="accordion accordion-flush dashboard-accordion" id="kandaPerformanceAccordion">
                    @forelse($kandaPerformance as $index => $kanda)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-{{ $kanda['id'] }}">
                                <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse-{{ $kanda['id'] }}"
                                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                        aria-controls="collapse-{{ $kanda['id'] }}">
                                    <div class="w-100 pe-3">
                                        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                                            <div>
                                                <div class="accordion-kanda-title">{{ $kanda['name'] }}</div>
                                                <div class="accordion-kanda-meta">
                                                    {{ $kanda['jumuiya_count'] }} {{ db_trans('jumuiyas') }} ·
                                                    {{ $kanda['members_count'] }} {{ db_trans('members') }}
                                                </div>
                                            </div>
                                            <div class="accordion-badges">
                                                <span class="soft-badge">{{ db_trans('baptized') }}: {{ $kanda['baptized_count'] }}</span>
                                                <span class="soft-badge">{{ db_trans('communion') }}: {{ $kanda['communion_count'] }}</span>
                                                <span class="soft-badge">{{ db_trans('confirmation') }}: {{ $kanda['confirmation_count'] }}</span>
                                                <span class="soft-badge">{{ db_trans('married') }}: {{ $kanda['married_count'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </h2>

                            <div id="collapse-{{ $kanda['id'] }}"
                                 class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                 aria-labelledby="heading-{{ $kanda['id'] }}"
                                 data-bs-parent="#kandaPerformanceAccordion">
                                <div class="accordion-body p-0">
                                    <div class="accordion-toolbar">
                                        <a href="{{ $kanda['details_url'] }}" class="btn btn-sm btn-outline-primary btn-modern">
                                            <i class="fas fa-eye me-1"></i>{{ db_trans('view_kanda') }}
                                        </a>
                                        <a href="{{ $kanda['report_url'] }}" class="btn btn-sm btn-outline-secondary btn-modern">
                                            <i class="fas fa-chart-column me-1"></i>{{ db_trans('view_report') }}
                                        </a>
                                    </div>

                                    <div class="table-responsive dashboard-table-wrap">
                                        <table class="table align-middle table-hover mb-0 dashboard-table">
                                            <thead>
                                                <tr>
                                                    <th>{{ db_trans('jumuiya') }}</th>
                                                    <th>{{ db_trans('familias') }}</th>
                                                    <th>{{ db_trans('members') }}</th>
                                                    <th>{{ db_trans('baptized') }}</th>
                                                    <th>{{ db_trans('communion') }}</th>
                                                    <th>{{ db_trans('confirmation') }}</th>
                                                    <th>{{ db_trans('married') }}</th>
                                                    <th>{{ db_trans('eucharist') }}</th>
                                                    <th>{{ db_trans('actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($kanda['jumuiyas'] as $jumuiya)
                                                    <tr>
                                                        <td class="fw-semibold">{{ $jumuiya['name'] }}</td>
                                                        <td>{{ $jumuiya['familias_count'] }}</td>
                                                        <td>{{ $jumuiya['members_count'] }}</td>
                                                        <td>{{ $jumuiya['baptized_count'] }}</td>
                                                        <td>{{ $jumuiya['communion_count'] }}</td>
                                                        <td>{{ $jumuiya['confirmation_count'] }}</td>
                                                        <td>{{ $jumuiya['married_count'] }}</td>
                                                        <td>{{ $jumuiya['eucharist_count'] }}</td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <a href="{{ $jumuiya['members_url'] }}" class="btn btn-sm btn-outline-primary btn-modern">
                                                                    {{ db_trans('open') }}
                                                                </a>
                                                                <a href="{{ $jumuiya['report_url'] }}" class="btn btn-sm btn-outline-secondary btn-modern">
                                                                    {{ db_trans('report') }}
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center py-4 text-muted">
                                                            {{ db_trans('no_jumuiyas_found') }}
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-muted">
                            {{ db_trans('no_kanda_data_found') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    window.dashboardChartsData = {
        memberChart: @json($memberChart),
        financeChart: @json($financeChart),
        visitorChart: @json($visitorChart),
        sacramentChart: @json($sacramentChart),
        mixChart: @json($mixChart),
        chartLinks: @json($chartLinks ?? []),
        labels: {
            members: @json(db_trans('members')),
            familias: @json(db_trans('familias')),
            offerings: @json(db_trans('offerings')),
            tithes: @json(db_trans('tithes')),
            contributions: @json(db_trans('contributions')),
            visitors: @json(db_trans('visitors')),
        }
    };
</script>
    <script src="{{ asset('admin/js/dashboard-v3.js') }}"></script>
@endpush