@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/kanda.css') }}">
@endpush

@php
    $pageKicker = db_trans('kanda.page_kicker');
    $pageTitle = db_trans('kanda.page_title');
    $pageSubtitle = db_trans('kanda.page_subtitle');
    $pageTotal = db_trans('kanda.stats.kandas');
    $pageJumuiyas = db_trans('kanda.stats.jumuiyas');
    $pageFamilies = db_trans('kanda.stats.families');
    $pageMembers = db_trans('kanda.stats.members');
    $pageSectionTitle = db_trans('kanda.section_title');
    $pageCtaTitle = db_trans('kanda.cta.title');
    $pageCtaText = db_trans('kanda.cta.text');
    $pageCtaPrimary = db_trans('kanda.cta.primary');
    $pageCtaSecondary = db_trans('kanda.cta.secondary');
@endphp

@section('content')

<div class="kanda-page">

    <section class="kanda-hero">
        <div class="home-shell">
            <div class="kanda-hero-wrap">
                <div class="kanda-hero-inner">
                    <span class="kanda-kicker">
                        <i class="bi bi-people"></i>
                        {{ $pageKicker }}
                    </span>

                    <h1>{{ $pageTitle }}</h1>

                    <p>{{ $pageSubtitle }}</p>

                    <div class="kanda-stats">
                        <div class="stat-chip">
                            <strong>{{ $totalCount ?? 0 }}</strong>
                            <span>{{ $pageTotal }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $totalJumuiyas ?? 0 }}</strong>
                            <span>{{ $pageJumuiyas }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $totalFamilies ?? 0 }}</strong>
                            <span>{{ $pageFamilies }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $totalMembers ?? 0 }}</strong>
                            <span>{{ $pageMembers }}</span>
                        </div>
                    </div>
                </div>

                <div class="kanda-hero-aside">
                    <div class="hero-mini-card">
                        <span class="hero-mini-label">{{ db_trans('kanda.hero.card_label') }}</span>
                        <h4>{{ db_trans('kanda.hero.card_title') }}</h4>
                        <p>{{ db_trans('kanda.hero.card_text') }}</p>

                        <div class="hero-points">
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('kanda.hero.point_1') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('kanda.hero.point_2') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('kanda.hero.point_3') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(!empty($featured))
        <section class="kanda-featured">
            <div class="home-shell">
                <div class="section-block">
                    <div class="section-block-head">
                        <div>
                            <span class="section-mini-kicker">{{ db_trans('kanda.featured.kicker') }}</span>
                            <h3 class="section-block-title">{{ db_trans('kanda.featured.title') }}</h3>
                        </div>
                    </div>

                    <div class="featured-kanda-card premium-hover-lift">
                        <div class="featured-kanda-image">
                            <img
                                src="{{ $featured->image_url ?: asset('uploads/frontend/community/kanda-default.jpeg') }}"
                                alt="{{ $featured->name }}"
                            >
                        </div>

                        <div class="featured-kanda-content">
                            <div class="featured-kanda-meta">
                                <span class="badge badge-code">{{ $featured->code }}</span>
                                <span class="badge badge-highlight">{{ $featured->highlight_label }}</span>
                            </div>

                            <h2>{{ $featured->name }}</h2>

                            <p>{{ $featured->short_comment }}</p>

                            <div class="featured-kanda-stats">
                                <div class="info-pill">
                                    <strong>{{ $featured->jumuiyas_count ?? 0 }}</strong>
                                    <span>{{ db_trans('kanda.stats.jumuiyas') }}</span>
                                </div>

                                <div class="info-pill">
                                    <strong>{{ $featured->familias_count ?? 0 }}</strong>
                                    <span>{{ db_trans('kanda.stats.families') }}</span>
                                </div>

                                <div class="info-pill">
                                    <strong>{{ $featured->members_count ?? 0 }}</strong>
                                    <span>{{ db_trans('kanda.stats.members') }}</span>
                                </div>
                            </div>

                            @if(!empty($featured->jumuiyas) && $featured->jumuiyas->count())
                                <div class="featured-jumuiya-strip">
                                    <span class="strip-label">{{ db_trans('kanda.featured.jumuiyas_in_kanda') }}</span>

                                    <div class="strip-tags">
                                        @foreach($featured->jumuiyas->take(4) as $jumuiya)
                                            <span class="strip-tag">{{ $jumuiya->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="kanda-grid-section">
        <div class="home-shell">
            <div class="section-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('kanda.grid.kicker') }}</span>
                        <h3 class="section-block-title">{{ $pageSectionTitle }}</h3>
                    </div>
                </div>

                <div class="kanda-grid">
                    @forelse($gridItems ?? $items ?? [] as $item)
                        <article class="kanda-card premium-hover-lift">
                            <div class="kanda-card-image">
                                <img
                                    src="{{ $item->image_url ?: asset('uploads/frontend/community/kanda-default.jpeg') }}"
                                    alt="{{ $item->name }}"
                                >
                            </div>

                            <div class="kanda-card-body">
                                <div class="kanda-card-meta">
                                    <span class="badge badge-code">{{ $item->code }}</span>
                                    <span class="badge badge-highlight">{{ $item->highlight_label }}</span>
                                </div>

                                <h4>{{ $item->name }}</h4>

                                <p>{{ $item->short_comment }}</p>

                                <div class="kanda-mini-stats">
                                    <div class="mini-stat">
                                        <strong>{{ $item->jumuiyas_count ?? 0 }}</strong>
                                        <span>{{ db_trans('kanda.stats.jumuiyas') }}</span>
                                    </div>

                                    <div class="mini-stat">
                                        <strong>{{ $item->familias_count ?? 0 }}</strong>
                                        <span>{{ db_trans('kanda.stats.families') }}</span>
                                    </div>

                                    <div class="mini-stat">
                                        <strong>{{ $item->members_count ?? 0 }}</strong>
                                        <span>{{ db_trans('kanda.stats.members') }}</span>
                                    </div>
                                </div>

                                @if(!empty($item->jumuiyas) && $item->jumuiyas->count())
                                    <div class="kanda-card-tags">
                                        @foreach($item->jumuiyas->take(3) as $jumuiya)
                                            <span>{{ $jumuiya->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-people"></i>
                            <span>{{ db_trans('kanda.empty_state') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="kanda-cta">
        <div class="home-shell">
            <div class="cta-box">
                <div class="cta-copy">
                    <span class="section-mini-kicker">{{ db_trans('kanda.cta.kicker') }}</span>
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