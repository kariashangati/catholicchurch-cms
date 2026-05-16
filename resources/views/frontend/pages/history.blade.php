@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/history.css') }}">
@endpush

@php
    $pageKicker = db_trans('history.page_kicker');
    $pageTitle = db_trans('history.page_title');
    $pageSubtitle = db_trans('history.page_subtitle');
    $pageStories = db_trans('history.stats.stories');
    $pageHighlights = db_trans('history.stats.highlights');
    $featuredBadge = db_trans('history.featured.badge');
    $featuredReadMore = db_trans('history.featured.read_more');
    $gridTitle = db_trans('history.grid.title');
    $gridReadMore = db_trans('history.grid.read_more');
    $emptyState = db_trans('history.grid.empty');

    $pageKicker = ($pageKicker && $pageKicker !== 'history.page_kicker') ? $pageKicker : 'Our Journey';
    $pageTitle = ($pageTitle && $pageTitle !== 'history.page_title') ? $pageTitle : 'Church History';
    $pageSubtitle = ($pageSubtitle && $pageSubtitle !== 'history.page_subtitle')
        ? $pageSubtitle
        : 'Discover the story of our parish — moments of faith, growth, and transformation across generations.';
    $pageStories = ($pageStories && $pageStories !== 'history.stats.stories') ? $pageStories : 'Stories';
    $pageHighlights = ($pageHighlights && $pageHighlights !== 'history.stats.highlights') ? $pageHighlights : 'Highlights';
    $featuredBadge = ($featuredBadge && $featuredBadge !== 'history.featured.badge') ? $featuredBadge : 'Featured Story';
    $featuredReadMore = ($featuredReadMore && $featuredReadMore !== 'history.featured.read_more') ? $featuredReadMore : 'Read full story';
    $gridTitle = ($gridTitle && $gridTitle !== 'history.grid.title') ? $gridTitle : 'All Stories';
    $gridReadMore = ($gridReadMore && $gridReadMore !== 'history.grid.read_more') ? $gridReadMore : 'Read story';
    $emptyState = ($emptyState && $emptyState !== 'history.grid.empty') ? $emptyState : 'No history stories available yet.';
@endphp

@section('content')

<div class="history-page">

    <!-- HERO -->
    <section class="history-hero">
        <div class="home-shell">
            <div class="history-hero-inner">

                <span class="history-kicker">
                    <i class="bi bi-clock-history"></i>
                    {{ $pageKicker }}
                </span>

                <h1>{{ $pageTitle }}</h1>

                <p>{{ $pageSubtitle }}</p>

                <div class="history-stats">
                    <div class="stat-chip">
                        <strong>{{ $totalCount }}</strong>
                        <span>{{ $pageStories }}</span>
                    </div>

                    <div class="stat-chip">
                        <strong>{{ $featuredCount }}</strong>
                        <span>{{ $pageHighlights }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- FEATURED -->
    @if($featured)
    <section class="history-featured">
        <div class="home-shell">

            <a href="{{ route('frontend.history.show', $featured->slug) }}"
               class="featured-history-card">

                <div class="featured-image">
                    <img src="{{ $featured->image_url }}" alt="{{ $featured->title }}">
                </div>

                <div class="featured-content">

                    <span class="badge badge-featured">
                        {{ $featuredBadge }}
                    </span>

                    <h2>{{ $featured->title }}</h2>

                    <p>{{ $featured->card_excerpt }}</p>

                    <span class="read-more">
                        {{ $featuredReadMore }}
                        <i class="bi bi-arrow-right"></i>
                    </span>

                </div>

            </a>

        </div>
    </section>
    @endif

    <!-- GRID -->
    <section class="history-grid">
        <div class="home-shell">

            <div class="grid-header">
                <h3>{{ $gridTitle }}</h3>
            </div>

            <div class="grid-wrap">

                @forelse($gridItems as $item)
                    <a href="{{ route('frontend.history.show', $item->slug) }}"
                       class="history-card">

                        <div class="card-image">
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                        </div>

                        <div class="card-body">

                            <span class="date">
                                {{ $item->card_date_full }}
                            </span>

                            <h4>{{ $item->title }}</h4>

                            <p>{{ $item->card_excerpt }}</p>

                            <span class="card-link">
                                {{ $gridReadMore }}
                                <i class="bi bi-arrow-right"></i>
                            </span>

                        </div>

                    </a>
                @empty
                    <div class="empty-state">
                        {{ $emptyState }}
                    </div>
                @endforelse

            </div>

        </div>
    </section>

</div>

@endsection