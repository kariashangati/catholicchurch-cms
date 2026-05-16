@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/leadership.css') }}">
@endpush

@php
    $pageKicker = db_trans('leadership.page_kicker');
    $pageTitle = db_trans('leadership.page_title');
    $pageSubtitle = db_trans('leadership.page_subtitle');
    $pageTotal = db_trans('leadership.stats.leaders');
    $pagePositions = db_trans('leadership.stats.positions');
    $pageKandas = db_trans('leadership.stats.kanda_roles');
    $pageJumuiyas = db_trans('leadership.stats.jumuiya_roles');
    $pageGroups = db_trans('leadership.stats.group_roles');
    $pageSectionTitle = db_trans('leadership.section_title');
    $pageCtaTitle = db_trans('leadership.cta.title');
    $pageCtaText = db_trans('leadership.cta.text');
    $pageCtaPrimary = db_trans('leadership.cta.primary');
    $pageCtaSecondary = db_trans('leadership.cta.secondary');

    $featuredScope = $featured->scope_label ?? db_trans('leadership.common.parish');
    $featuredRole = $featured->position_name ?? db_trans('leadership.common.leader');
    $featuredName = $featured->member_name ?? db_trans('leadership.common.leader');
@endphp

@section('content')

<div class="leadership-page">

    <section class="leadership-hero">
        <div class="home-shell">
            <div class="leadership-hero-wrap">
                <div class="leadership-hero-inner">
                    <span class="leadership-kicker">
                        <i class="bi bi-stars"></i>
                        {{ $pageKicker }}
                    </span>

                    <h1>{{ $pageTitle }}</h1>

                    <p>{{ $pageSubtitle }}</p>

                    <div class="leadership-stats">
                        <div class="stat-chip">
                            <strong>{{ $totalCount ?? 0 }}</strong>
                            <span>{{ $pageTotal }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $positionCount ?? 0 }}</strong>
                            <span>{{ $pagePositions }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $kandaCount ?? 0 }}</strong>
                            <span>{{ $pageKandas }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $jumuiyaCount ?? 0 }}</strong>
                            <span>{{ $pageJumuiyas }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $groupCount ?? 0 }}</strong>
                            <span>{{ $pageGroups }}</span>
                        </div>
                    </div>
                </div>

                <div class="leadership-hero-aside">
                    <div class="hero-mini-card">
                        <span class="hero-mini-label">{{ db_trans('leadership.hero.label') }}</span>
                        <h4>{{ db_trans('leadership.hero.title') }}</h4>
                        <p>{{ db_trans('leadership.hero.text') }}</p>

                        <div class="hero-points">
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('leadership.hero.point_1') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('leadership.hero.point_2') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('leadership.hero.point_3') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(!empty($featured))
        <section class="leadership-featured">
            <div class="home-shell">
                <div class="section-block">
                    <div class="section-block-head">
                        <div>
                            <span class="section-mini-kicker">{{ db_trans('leadership.featured.kicker') }}</span>
                            <h3 class="section-block-title">{{ db_trans('leadership.featured.title') }}</h3>
                        </div>
                    </div>

                    <div class="featured-leader-card premium-hover-lift">
                        <div class="featured-leader-visual">
                            <div class="featured-leader-visual-inner">
                                <div class="featured-avatar">
                                    <i class="bi bi-person-badge"></i>
                                </div>

                                <span class="featured-visual-label">
                                    {{ $featuredScope }}
                                </span>
                            </div>
                        </div>

                        <div class="featured-leader-content">
                            <div class="featured-leader-meta">
                                <span class="badge badge-role">
                                    {{ $featuredRole }}
                                </span>

                                <span class="badge badge-scope scope-{{ $featured->scope ?? 'parish' }}">
                                    {{ ucfirst($featured->scope ?? db_trans('leadership.common.parish')) }}
                                </span>
                            </div>

                            <h2>{{ $featuredName }}</h2>

                            <p class="featured-leader-copy">
                                {{ db_trans('leadership.featured.copy') }}
                            </p>

                            <div class="featured-leader-stats">
                                <div class="info-pill">
                                    <strong>{{ $featuredRole }}</strong>
                                    <span>{{ db_trans('leadership.common.position') }}</span>
                                </div>

                                <div class="info-pill">
                                    <strong>{{ $featuredScope }}</strong>
                                    <span>{{ db_trans('leadership.common.scope') }}</span>
                                </div>

                                <div class="info-pill">
                                    <strong>
                                        {{ optional($featured->started_at)->format('M Y') ?: db_trans('leadership.common.active') }}
                                    </strong>
                                    <span>{{ db_trans('leadership.common.since') }}</span>
                                </div>
                            </div>

                            <div class="featured-leader-tags">
                                @if($featured->kanda)
                                    <span>{{ db_trans('leadership.common.kanda') }}: {{ $featured->kanda->name }}</span>
                                @endif

                                @if($featured->jumuiya)
                                    <span>{{ db_trans('leadership.common.jumuiya') }}: {{ $featured->jumuiya->name }}</span>
                                @endif

                                @if($featured->apostolicGroup)
                                    <span>{{ db_trans('leadership.common.group') }}: {{ $featured->apostolicGroup->name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="leadership-grid-section">
        <div class="home-shell">
            <div class="section-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('leadership.grid.kicker') }}</span>
                        <h3 class="section-block-title">{{ $pageSectionTitle }}</h3>
                    </div>
                </div>

                <div class="leadership-grid">
                    @forelse($gridItems ?? $items ?? [] as $item)
                        <article class="leader-card premium-hover-lift">
                            <div class="leader-card-top">
                                <div class="leader-avatar">
                                    <i class="bi bi-person-lines-fill"></i>
                                </div>

                                <div class="leader-card-meta">
                                    <span class="badge badge-role">
                                        {{ $item->position_name ?? db_trans('leadership.common.leader') }}
                                    </span>

                                    <span class="badge badge-scope scope-{{ $item->scope ?? 'parish' }}">
                                        {{ ucfirst($item->scope ?? db_trans('leadership.common.parish')) }}
                                    </span>
                                </div>
                            </div>

                            <div class="leader-card-body">
                                <h4>{{ $item->member_name ?? db_trans('leadership.common.leader') }}</h4>

                                <p class="leader-scope-line">
                                    <i class="bi bi-diagram-3"></i>
                                    <span>{{ $item->scope_label ?? db_trans('leadership.common.parish') }}</span>
                                </p>

                                <div class="leader-mini-stats">
                                    <div class="mini-stat">
                                        <strong>{{ $item->position_name ?? db_trans('leadership.common.leader') }}</strong>
                                        <span>{{ db_trans('leadership.common.role') }}</span>
                                    </div>

                                    <div class="mini-stat">
                                        <strong>{{ ucfirst($item->scope ?? db_trans('leadership.common.parish')) }}</strong>
                                        <span>{{ db_trans('leadership.common.scope') }}</span>
                                    </div>
                                </div>

                                <div class="leader-card-tags">
                                    @if($item->kanda)
                                        <span>{{ $item->kanda->name }}</span>
                                    @endif

                                    @if($item->jumuiya)
                                        <span>{{ $item->jumuiya->name }}</span>
                                    @endif

                                    @if($item->apostolicGroup)
                                        <span>{{ $item->apostolicGroup->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-person-badge"></i>
                            <span>{{ db_trans('leadership.empty_state') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="leadership-structure-section">
        <div class="home-shell">
            <div class="section-block structure-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('leadership.structure.kicker') }}</span>
                        <h3 class="section-block-title">{{ db_trans('leadership.structure.title') }}</h3>
                    </div>
                </div>

                <div class="structure-grid">
                    <div class="structure-card premium-hover-lift">
                        <span class="structure-step">01</span>
                        <h4>{{ db_trans('leadership.structure.card_1_title') }}</h4>
                        <p>{{ db_trans('leadership.structure.card_1_text') }}</p>
                    </div>

                    <div class="structure-card premium-hover-lift">
                        <span class="structure-step">02</span>
                        <h4>{{ db_trans('leadership.structure.card_2_title') }}</h4>
                        <p>{{ db_trans('leadership.structure.card_2_text') }}</p>
                    </div>

                    <div class="structure-card premium-hover-lift">
                        <span class="structure-step">03</span>
                        <h4>{{ db_trans('leadership.structure.card_3_title') }}</h4>
                        <p>{{ db_trans('leadership.structure.card_3_text') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="leadership-cta">
        <div class="home-shell">
            <div class="cta-box">
                <div class="cta-copy">
                    <span class="section-mini-kicker">{{ db_trans('leadership.cta.kicker') }}</span>
                    <h3>{{ $pageCtaTitle }}</h3>
                    <p>{{ $pageCtaText }}</p>
                </div>

                <div class="cta-actions">
                    <a href="{{ route('frontend.contact') }}" class="btn-primary">
                        {{ $pageCtaPrimary }}
                    </a>

                    <a href="{{ route('frontend.jumuiyas') }}" class="btn-secondary-soft">
                        {{ $pageCtaSecondary }}
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection