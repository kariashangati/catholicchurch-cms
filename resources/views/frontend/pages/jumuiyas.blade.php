@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/jumuiya.css') }}">
@endpush

@php
    $pageKicker = db_trans('jumuiya.page_kicker');
    $pageTitle = db_trans('jumuiya.page_title');
    $pageSubtitle = db_trans('jumuiya.page_subtitle');
    $pageTotal = db_trans('jumuiya.stats.jumuiyas');
    $pageFamilies = db_trans('jumuiya.stats.families');
    $pageMembers = db_trans('jumuiya.stats.members');
    $pageKandas = db_trans('jumuiya.stats.kandas');
    $pageSectionTitle = db_trans('jumuiya.section_title');
    $pageCtaTitle = db_trans('jumuiya.cta.title');
    $pageCtaText = db_trans('jumuiya.cta.text');
    $pageCtaPrimary = db_trans('jumuiya.cta.primary');
    $pageCtaSecondary = db_trans('jumuiya.cta.secondary');
@endphp

@section('content')

<div class="jumuiya-page">

    <section class="jumuiya-hero">
        <div class="home-shell">
            <div class="jumuiya-hero-wrap">
                <div class="jumuiya-hero-inner">
                    <span class="jumuiya-kicker">
                        <i class="bi bi-house-heart"></i>
                        {{ $pageKicker }}
                    </span>

                    <h1>{{ $pageTitle }}</h1>

                    <p>{{ $pageSubtitle }}</p>

                    <div class="jumuiya-stats">
                        <div class="stat-chip">
                            <strong>{{ $totalCount ?? 0 }}</strong>
                            <span>{{ $pageTotal }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $totalFamilies ?? 0 }}</strong>
                            <span>{{ $pageFamilies }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $totalMembers ?? 0 }}</strong>
                            <span>{{ $pageMembers }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $totalKandas ?? 0 }}</strong>
                            <span>{{ $pageKandas }}</span>
                        </div>
                    </div>
                </div>

                <div class="jumuiya-hero-aside">
                    <div class="hero-mini-card">
                        <span class="hero-mini-label">{{ db_trans('jumuiya.hero.card_label') }}</span>
                        <h4>{{ db_trans('jumuiya.hero.card_title') }}</h4>
                        <p>{{ db_trans('jumuiya.hero.card_text') }}</p>

                        <div class="hero-points">
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('jumuiya.hero.point_1') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('jumuiya.hero.point_2') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('jumuiya.hero.point_3') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(!empty($featured))
        <section class="jumuiya-featured">
            <div class="home-shell">
                <div class="section-block">
                    <div class="section-block-head">
                        <div>
                            <span class="section-mini-kicker">{{ db_trans('jumuiya.featured.kicker') }}</span>
                            <h3 class="section-block-title">{{ db_trans('jumuiya.featured.title') }}</h3>
                        </div>
                    </div>

                    <div class="featured-jumuiya-card premium-hover-lift">
                        <div class="featured-jumuiya-visual">
                            <div class="featured-jumuiya-visual-inner">
                                <div class="featured-icon-wrap">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <span class="featured-visual-label">
                                    {{ $featured->kanda_name ?: db_trans('jumuiya.parent.parish_community') }}
                                </span>
                            </div>
                        </div>

                        <div class="featured-jumuiya-content">
                            <div class="featured-jumuiya-meta">
                                <span class="badge badge-parent">
                                    {{ $featured->kanda_name ?: db_trans('jumuiya.parent.community') }}
                                </span>

                                @if(!empty($featured->code))
                                    <span class="badge badge-code">
                                        {{ $featured->code }}
                                    </span>
                                @endif
                            </div>

                            <h2>{{ $featured->name }}</h2>

                            <p>{{ $featured->short_description }}</p>

                            <div class="featured-jumuiya-stats">
                                <div class="info-pill">
                                    <strong>{{ $featured->familias_count ?? 0 }}</strong>
                                    <span>{{ db_trans('jumuiya.stats.families') }}</span>
                                </div>

                                <div class="info-pill">
                                    <strong>{{ $featured->members_count ?? 0 }}</strong>
                                    <span>{{ db_trans('jumuiya.stats.members') }}</span>
                                </div>

                                <div class="info-pill">
                                    <strong>{{ $featured->kanda_name ?: '—' }}</strong>
                                    <span>{{ db_trans('jumuiya.parent.parent_kanda') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="jumuiya-grid-section">
        <div class="home-shell">
            <div class="section-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('jumuiya.grid.kicker') }}</span>
                        <h3 class="section-block-title">{{ $pageSectionTitle }}</h3>
                    </div>
                </div>

                <div class="jumuiya-grid">
                    @forelse($gridItems ?? $items ?? [] as $item)
                        <article class="jumuiya-card premium-hover-lift">
                            <div class="jumuiya-card-top">
                                <div class="jumuiya-icon">
                                    <i class="bi bi-house-heart"></i>
                                </div>

                                <div class="jumuiya-card-meta">
                                    <span class="badge badge-parent">
                                        {{ $item->kanda_name ?: db_trans('jumuiya.parent.community') }}
                                    </span>

                                    @if(!empty($item->code))
                                        <span class="badge badge-code">
                                            {{ $item->code }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="jumuiya-card-body">
                                <h4>{{ $item->name }}</h4>

                                <p>{{ $item->short_description }}</p>

                                <div class="jumuiya-mini-stats">
                                    <div class="mini-stat">
                                        <strong>{{ $item->familias_count ?? 0 }}</strong>
                                        <span>{{ db_trans('jumuiya.stats.families') }}</span>
                                    </div>

                                    <div class="mini-stat">
                                        <strong>{{ $item->members_count ?? 0 }}</strong>
                                        <span>{{ db_trans('jumuiya.stats.members') }}</span>
                                    </div>
                                </div>

                                <div class="jumuiya-parent-line">
                                    <i class="bi bi-diagram-3"></i>
                                    <span>{{ $item->kanda_name ?: db_trans('jumuiya.parent.parish_community') }}</span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-house-heart"></i>
                            <span>{{ db_trans('jumuiya.empty_state') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="jumuiya-cta">
        <div class="home-shell">
            <div class="cta-box">
                <div class="cta-copy">
                    <span class="section-mini-kicker">{{ db_trans('jumuiya.cta.kicker') }}</span>
                    <h3>{{ $pageCtaTitle }}</h3>
                    <p>{{ $pageCtaText }}</p>
                </div>

                <div class="cta-actions">
                    <a href="{{ route('frontend.contact') }}" class="btn-primary">
                        {{ $pageCtaPrimary }}
                    </a>

                    <a href="{{ route('frontend.kandas') }}" class="btn-secondary-soft">
                        {{ $pageCtaSecondary }}
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection