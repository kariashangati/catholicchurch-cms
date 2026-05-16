@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/announcement.css') }}">
@endpush

@php
    $pageKicker = db_trans('announcements.kicker');
    $pageTitle = db_trans('announcements.title');
    $pageSubtitle = db_trans('announcements.subtitle');
    $pageTotal = db_trans('announcements.total');
    $pageFeatured = db_trans('announcements.featured');
    $pageLatest = db_trans('announcements.latest');
    $pageEmpty = db_trans('announcements.empty');
    $pageCtaTitle = db_trans('announcements.cta_title');
    $pageCtaText = db_trans('announcements.cta_text');
    $pageCtaButton = db_trans('announcements.cta_button');
    $readMore = db_trans('common.read_more');

    $pageKicker = ($pageKicker && $pageKicker !== 'announcements.kicker') ? $pageKicker : 'Church Updates';
    $pageTitle = ($pageTitle && $pageTitle !== 'announcements.title') ? $pageTitle : 'Announcements';
    $pageSubtitle = ($pageSubtitle && $pageSubtitle !== 'announcements.subtitle')
        ? $pageSubtitle
        : 'Stay informed with the latest updates, events, and messages from our church community.';
    $pageTotal = ($pageTotal && $pageTotal !== 'announcements.total') ? $pageTotal : 'Updates';
    $pageFeatured = ($pageFeatured && $pageFeatured !== 'announcements.featured') ? $pageFeatured : 'Featured';
    $pageLatest = ($pageLatest && $pageLatest !== 'announcements.latest') ? $pageLatest : 'Latest Updates';
    $pageEmpty = ($pageEmpty && $pageEmpty !== 'announcements.empty') ? $pageEmpty : 'No announcements available at the moment.';
    $pageCtaTitle = ($pageCtaTitle && $pageCtaTitle !== 'announcements.cta_title')
        ? $pageCtaTitle
        : 'Need prayer or want to connect with us?';
    $pageCtaText = ($pageCtaText && $pageCtaText !== 'announcements.cta_text')
        ? $pageCtaText
        : 'We are here to support you. Reach out to us anytime.';
    $pageCtaButton = ($pageCtaButton && $pageCtaButton !== 'announcements.cta_button') ? $pageCtaButton : 'Contact Us';
    $readMore = ($readMore && $readMore !== 'common.read_more') ? $readMore : 'Read more';
@endphp

@section('content')

<div class="announcements-page">

    {{-- HERO --}}
    <section class="announcements-hero">
        <div class="home-shell">
            <div class="announcements-hero-wrap">
                <div class="announcements-hero-inner">
                    <span class="announcements-kicker">
                        <i class="bi bi-megaphone"></i>
                        {{ $pageKicker }}
                    </span>

                    <h1>{{ $pageTitle }}</h1>

                    <p>{{ $pageSubtitle }}</p>

                    <div class="announcements-stats">
                        <div class="stat-chip">
                            <strong>{{ $totalCount ?? 0 }}</strong>
                            <span>{{ $pageTotal }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $featuredCount ?? 0 }}</strong>
                            <span>{{ $pageFeatured }}</span>
                        </div>
                    </div>
                </div>

                <div class="announcements-hero-aside">
                    <div class="hero-mini-card">
                        <span class="hero-mini-label">{{ db_trans('announcements.hero.label') ?: 'Live' }}</span>
                        <h4>{{ db_trans('announcements.hero.title') ?: 'Fresh parish updates' }}</h4>
                        <p>{{ db_trans('announcements.hero.text') ?: 'Announcements, events, notices, and featured community moments in one place.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURED --}}
    @if($featured)
        <section class="announcements-featured">
            <div class="home-shell">
                <div class="section-block">
                    <div class="section-block-head">
                        <div>
                            <span class="section-mini-kicker">{{ db_trans('announcements.featured_kicker') ?: 'Featured Story' }}</span>
                            <h3 class="section-block-title">{{ db_trans('announcements.featured_title') ?: 'Spotlight Announcement' }}</h3>
                        </div>
                    </div>

                    <a href="{{ route('frontend.announcements.show', $featured->slug) }}" class="featured-card premium-hover-lift">
                        <div class="featured-image">
                            <img
                                src="{{ $featured->image_url ?: asset('uploads/frontend/announcements/ann6.jpeg') }}"
                                alt="{{ $featured->title }}"
                            >
                        </div>

                        <div class="featured-content">
                            <div class="featured-meta">
                                <span class="badge badge-featured">
                                    {{ $featured->card_badge }}
                                </span>

                                @if(!empty($featured->card_date_full))
                                    <span class="date">
                                        <i class="bi bi-calendar-event"></i>
                                        {{ $featured->card_date_full }}
                                    </span>
                                @endif
                            </div>

                            <h2>{{ $featured->title }}</h2>

                            <p>{{ $featured->card_summary }}</p>

                            <span class="read-more">
                                {{ $readMore }}
                                <i class="bi bi-arrow-right"></i>
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- GRID --}}
    <section class="announcements-grid">
        <div class="home-shell">
            <div class="section-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('announcements.browse_kicker') ?: 'Browse Updates' }}</span>
                        <h3 class="section-block-title">{{ $pageLatest }}</h3>
                    </div>
                </div>

                <div class="grid-wrap">
                    @forelse($gridItems as $item)
                        <a href="{{ route('frontend.announcements.show', $item->slug) }}" class="announcement-card premium-hover-lift">
                            <div class="card-image">
                                <img
                                    src="{{ $item->image_url ?: asset('uploads/frontend/announcements/ann6.jpeg') }}"
                                    alt="{{ $item->title }}"
                                >
                            </div>

                            <div class="card-body">
                                <div class="card-meta">
                                    <span class="badge">
                                        {{ $item->card_badge }}
                                    </span>

                                    <span class="date">
                                        {{ $item->card_date }}
                                    </span>
                                </div>

                                <h4>{{ $item->title }}</h4>

                                <p>{{ $item->card_summary }}</p>

                                <span class="card-link">
                                    {{ $readMore }}
                                    <i class="bi bi-arrow-right"></i>
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-info-circle"></i>
                            <span>{{ $pageEmpty }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="announcements-cta">
        <div class="home-shell">
            <div class="cta-box">
                <div class="cta-copy">
                    <span class="section-mini-kicker">{{ db_trans('announcements.cta_kicker') ?: 'Let’s Connect' }}</span>
                    <h3>{{ $pageCtaTitle }}</h3>
                    <p>{{ $pageCtaText }}</p>
                </div>

                <div class="cta-actions">
                    <a href="{{ route('frontend.contact') }}" class="btn-primary">
                        {{ $pageCtaButton }}
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection