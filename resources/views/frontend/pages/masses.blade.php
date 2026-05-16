@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/mass.css') }}">
@endpush

@php
    $pageKicker = db_trans('mass.page_kicker');
    $pageTitle = db_trans('mass.page_title');
    $pageSubtitle = db_trans('mass.page_subtitle');
    $pageTotal = db_trans('mass.stats.upcoming');
    $pageTypes = db_trans('mass.stats.mass_types');
    $pageWeek = db_trans('mass.stats.this_week');
    $pageNext = db_trans('mass.stats.next_mass');
    $pageThisWeekTitle = db_trans('mass.section.this_week_title');
    $pageUpcomingTitle = db_trans('mass.section.upcoming_title');
    $pageGuideTitle = db_trans('mass.section.guide_title');
    $pageCtaTitle = db_trans('mass.cta.title');
    $pageCtaText = db_trans('mass.cta.text');
    $pageCtaPrimary = db_trans('mass.cta.primary');
    $pageCtaSecondary = db_trans('mass.cta.secondary');

    $featuredType = optional(optional($featured)->massType)->name ?: db_trans('mass.common.mass');
    $featuredDate = optional(optional($featured)->scheduled_at)->format('M d, Y');
    $featuredTime = optional(optional($featured)->scheduled_at)->format('h:i A');

    $massGuideCopy = db_trans('mass.guide.copy');
@endphp

@section('content')

<div class="mass-page">

    <section class="mass-hero">
        <div class="home-shell">
            <div class="mass-hero-wrap">
                <div class="mass-hero-inner">
                    <span class="mass-kicker">
                        <i class="bi bi-calendar2-heart"></i>
                        {{ $pageKicker }}
                    </span>

                    <h1>{{ $pageTitle }}</h1>

                    <p>{{ $pageSubtitle }}</p>

                    <div class="mass-stats">
                        <div class="stat-chip">
                            <strong>{{ $totalCount ?? 0 }}</strong>
                            <span>{{ $pageTotal }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $typeCount ?? 0 }}</strong>
                            <span>{{ $pageTypes }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $thisWeekCount ?? 0 }}</strong>
                            <span>{{ $pageWeek }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $nextMassDateLabel ?: '—' }}</strong>
                            <span>{{ $pageNext }}</span>
                        </div>
                    </div>
                </div>

                <div class="mass-hero-aside">
                    <div class="hero-mini-card">
                        <span class="hero-mini-label">{{ db_trans('mass.hero.card_label') }}</span>
                        <h4>{{ db_trans('mass.hero.card_title') }}</h4>
                        <p>{{ db_trans('mass.hero.card_text') }}</p>

                        <div class="hero-points">
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('mass.hero.point_1') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('mass.hero.point_2') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('mass.hero.point_3') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($featured)
        <section class="mass-featured">
            <div class="home-shell">
                <div class="section-block">
                    <div class="section-block-head">
                        <div>
                            <span class="section-mini-kicker">{{ db_trans('mass.featured.kicker') }}</span>
                            <h3 class="section-block-title">{{ db_trans('mass.featured.title') }}</h3>
                        </div>
                    </div>

                    <div class="featured-mass-card premium-hover-lift">
                        <div class="featured-mass-visual">
                            <div class="featured-mass-visual-inner">
                                <div class="featured-icon-wrap">
                                    <i class="bi bi-building"></i>
                                </div>

                                <span class="featured-visual-label">
                                    {{ $featuredType }}
                                </span>
                            </div>
                        </div>

                        <div class="featured-mass-content">
                            <div class="featured-mass-meta">
                                <span class="badge badge-type">
                                    {{ $featuredType }}
                                </span>

                                <span class="badge badge-status">
                                    {{ db_trans('mass.featured.status_scheduled') }}
                                </span>
                            </div>

                            <h2>{{ $featuredDate ?: db_trans('mass.featured.upcoming_mass') }}</h2>

                            <p class="featured-mass-copy">
                                {{ db_trans('mass.featured.copy') }}
                            </p>

                            <div class="featured-mass-stats">
                                <div class="info-pill">
                                    <strong>{{ $featuredDate ?: '—' }}</strong>
                                    <span>{{ db_trans('mass.common.date') }}</span>
                                </div>

                                <div class="info-pill">
                                    <strong>{{ $featuredTime ?: '—' }}</strong>
                                    <span>{{ db_trans('mass.common.time') }}</span>
                                </div>

                                <div class="info-pill">
                                    <strong>{{ $featuredType }}</strong>
                                    <span>{{ db_trans('mass.common.mass_type') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="mass-week-section">
        <div class="home-shell">
            <div class="section-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('mass.weekly.kicker') }}</span>
                        <h3 class="section-block-title">{{ $pageThisWeekTitle }}</h3>
                    </div>
                </div>

                <div class="mass-list">
                    @forelse($weekItems as $item)
                        <article class="mass-row premium-hover-lift">
                            <div class="mass-row-date">
                                <strong>{{ optional($item->scheduled_at)->format('d') }}</strong>
                                <span>{{ optional($item->scheduled_at)->format('M') }}</span>
                            </div>

                            <div class="mass-row-main">
                                <div class="mass-row-meta">
                                    <span class="badge badge-type">
                                        {{ optional($item->massType)->name ?: db_trans('mass.common.mass') }}
                                    </span>
                                </div>

                                <h4>{{ optional($item->massType)->name ?: db_trans('mass.common.mass_celebration') }}</h4>

                                <p>
                                    {{ optional($item->scheduled_at)->format('l, M d, Y') }} •
                                    {{ optional($item->scheduled_at)->format('h:i A') }}
                                </p>
                            </div>

                            <div class="mass-row-time">
                                {{ optional($item->scheduled_at)->format('h:i A') }}
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <span>{{ db_trans('mass.weekly.empty_state') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="mass-upcoming-section">
        <div class="home-shell">
            <div class="section-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('mass.upcoming.kicker') }}</span>
                        <h3 class="section-block-title">{{ $pageUpcomingTitle }}</h3>
                    </div>
                </div>

                <div class="mass-grid">
                    @forelse($upcomingItems as $item)
                        <article class="mass-card premium-hover-lift">
                            <div class="mass-card-top">
                                <div class="mass-card-icon">
                                    <i class="bi bi-calendar2-week"></i>
                                </div>

                                <div class="mass-card-meta">
                                    <span class="badge badge-type">
                                        {{ optional($item->massType)->name ?: db_trans('mass.common.mass') }}
                                    </span>
                                </div>
                            </div>

                            <div class="mass-card-body">
                                <h4>{{ optional($item->massType)->name ?: db_trans('mass.common.mass_celebration') }}</h4>

                                <p>
                                    {{ optional($item->scheduled_at)->format('l, M d, Y') }}
                                </p>

                                <div class="mass-mini-stats">
                                    <div class="mini-stat">
                                        <strong>{{ optional($item->scheduled_at)->format('h:i') ?: '—' }}</strong>
                                        <span>{{ db_trans('mass.common.time') }}</span>
                                    </div>

                                    <div class="mini-stat">
                                        <strong>{{ optional($item->scheduled_at)->format('A') ?: '—' }}</strong>
                                        <span>{{ db_trans('mass.common.session') }}</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <span>{{ db_trans('mass.upcoming.empty_state') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="mass-guide-section">
        <div class="home-shell">
            <div class="section-block guide-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('mass.guide.kicker') }}</span>
                        <h3 class="section-block-title">{{ $pageGuideTitle }}</h3>
                    </div>
                </div>

                <div class="guide-grid">
                    <div class="guide-card premium-hover-lift">
                        <span class="guide-step">01</span>
                        <h4>{{ db_trans('mass.guide.card_1_title') }}</h4>
                        <p>{{ db_trans('mass.guide.card_1_text') }}</p>
                    </div>

                    <div class="guide-card premium-hover-lift">
                        <span class="guide-step">02</span>
                        <h4>{{ db_trans('mass.guide.card_2_title') }}</h4>
                        <p>{{ db_trans('mass.guide.card_2_text') }}</p>
                    </div>

                    <div class="guide-card premium-hover-lift">
                        <span class="guide-step">03</span>
                        <h4>{{ db_trans('mass.guide.card_3_title') }}</h4>
                        <p>{{ $massGuideCopy }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mass-cta">
        <div class="home-shell">
            <div class="cta-box">
                <div class="cta-copy">
                    <span class="section-mini-kicker">{{ db_trans('mass.cta.kicker') }}</span>
                    <h3>{{ $pageCtaTitle }}</h3>
                    <p>{{ $pageCtaText }}</p>
                </div>

                <div class="cta-actions">
                    <a href="{{ route('frontend.contact') }}" class="btn-primary">
                        {{ $pageCtaPrimary }}
                    </a>

                    <a href="{{ route('frontend.announcements') }}" class="btn-secondary-soft">
                        {{ $pageCtaSecondary }}
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection