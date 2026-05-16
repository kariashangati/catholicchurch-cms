@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/gallery.css') }}">
@endpush

@php
    $pageKicker = db_trans('gallery.page_kicker');
    $pageTitle = db_trans('gallery.page_title');
    $pageSubtitle = db_trans('gallery.page_subtitle');
    $pageTotal = db_trans('gallery.stats.galleries');
    $pageImages = db_trans('gallery.stats.images');
    $pageFeatured = db_trans('gallery.stats.featured');
    $pageSectionTitle = db_trans('gallery.section_title');
    $pageCtaTitle = db_trans('gallery.cta.title');
    $pageCtaText = db_trans('gallery.cta.text');
    $pageCtaPrimary = db_trans('gallery.cta.primary');
    $pageCtaSecondary = db_trans('gallery.cta.secondary');

    $featuredTitle = optional($featured)->title ?: db_trans('gallery.featured.default_title');
    $featuredDate = optional(optional($featured)->event_date)->format('M d, Y') ?: null;
    $featuredImagesCount = optional($featured)->images_count ?? 0;
    $featuredDescription = optional($featured)->short_description ?: db_trans('gallery.featured.default_description');
@endphp

@section('content')

<div class="gallery-page">

    <section class="gallery-hero">
        <div class="home-shell">
            <div class="gallery-hero-wrap">
                <div class="gallery-hero-inner">
                    <span class="gallery-kicker">
                        <i class="bi bi-images"></i>
                        {{ $pageKicker }}
                    </span>

                    <h1>{{ $pageTitle }}</h1>

                    <p>{{ $pageSubtitle }}</p>

                    <div class="gallery-stats">
                        <div class="stat-chip">
                            <strong>{{ $totalCount ?? 0 }}</strong>
                            <span>{{ $pageTotal }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $totalImages ?? 0 }}</strong>
                            <span>{{ $pageImages }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $featuredCount ?? 0 }}</strong>
                            <span>{{ $pageFeatured }}</span>
                        </div>
                    </div>
                </div>

                <div class="gallery-hero-aside">
                    <div class="hero-mini-card">
                        <span class="hero-mini-label">{{ db_trans('gallery.hero.label') }}</span>
                        <h4>{{ db_trans('gallery.hero.title') }}</h4>
                        <p>{{ db_trans('gallery.hero.text') }}</p>

                        <div class="hero-points">
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('gallery.hero.point_1') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('gallery.hero.point_2') }}</span>
                            </div>
                            <div class="hero-point">
                                <i class="bi bi-check-circle"></i>
                                <span>{{ db_trans('gallery.hero.point_3') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(!empty($featured))
        <section class="gallery-featured">
            <div class="home-shell">
                <div class="section-block">
                    <div class="section-block-head">
                        <div>
                            <span class="section-mini-kicker">{{ db_trans('gallery.featured.kicker') }}</span>
                            <h3 class="section-block-title">{{ db_trans('gallery.featured.title') }}</h3>
                        </div>
                    </div>

                    <div class="featured-gallery-card premium-hover-lift">
                        <div class="featured-gallery-image">
                            <img
                                src="{{ $featured->cover_image_url ?: asset('uploads/frontend/gallery/gallery-default.jpeg') }}"
                                alt="{{ $featured->image_alt ?: $featuredTitle }}"
                            >
                        </div>

                        <div class="featured-gallery-content">
                            <div class="featured-gallery-meta">
                                <span class="badge badge-gallery">
                                    {{ $featured->gallery_chip ?? db_trans('gallery.common.gallery') }}
                                </span>

                                @if($featuredDate)
                                    <span class="badge badge-date">
                                        {{ $featuredDate }}
                                    </span>
                                @endif
                            </div>

                            <h2>{{ $featuredTitle }}</h2>

                            <p>{{ $featuredDescription }}</p>

                            <div class="featured-gallery-stats">
                                <div class="info-pill">
                                    <strong>{{ $featuredImagesCount }}</strong>
                                    <span>{{ db_trans('gallery.stats.images') }}</span>
                                </div>

                                <div class="info-pill">
                                    <strong>{{ $featured->event_label ?? db_trans('gallery.common.moments_of_faith') }}</strong>
                                    <span>{{ db_trans('gallery.common.event') }}</span>
                                </div>
                            </div>

                            @if(!empty($featured->images) && $featured->images->count())
                                <div class="featured-gallery-preview">
                                    @foreach($featured->images->take(3) as $image)
                                        <div class="preview-thumb">
                                            <img
                                                src="{{ asset(ltrim($image->image_path, '/')) }}"
                                                alt="{{ $image->alt_text ?: $featuredTitle }}"
                                            >
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="gallery-grid-section">
        <div class="home-shell">
            <div class="section-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('gallery.grid.kicker') }}</span>
                        <h3 class="section-block-title">{{ $pageSectionTitle }}</h3>
                    </div>
                </div>

                <div class="gallery-grid">
                    @forelse($gridItems ?? $items ?? [] as $item)
                        <article class="gallery-card premium-hover-lift">
                            <div class="gallery-card-image">
                                <img
                                    src="{{ $item->cover_image_url ?: asset('uploads/frontend/gallery/gallery-default.jpeg') }}"
                                    alt="{{ $item->image_alt ?: $item->title }}"
                                >

                                <div class="gallery-card-overlay">
                                    <span class="overlay-chip">
                                        <i class="bi bi-camera"></i>
                                        {{ $item->images_count ?? 0 }}
                                    </span>
                                </div>
                            </div>

                            <div class="gallery-card-body">
                                <div class="gallery-card-meta">
                                    <span class="badge badge-gallery">
                                        {{ $item->gallery_chip ?? db_trans('gallery.common.gallery') }}
                                    </span>

                                    <span class="badge badge-date">
                                        {{ $item->event_label ?? db_trans('gallery.common.moments_of_faith') }}
                                    </span>
                                </div>

                                <h4>{{ $item->title }}</h4>

                                <p>{{ $item->short_description }}</p>

                                @if(!empty($item->images) && $item->images->count())
                                    <div class="gallery-card-preview">
                                        @foreach($item->images->take(3) as $image)
                                            <div class="mini-preview-thumb">
                                                <img
                                                    src="{{ asset(ltrim($image->image_path, '/')) }}"
                                                    alt="{{ $image->alt_text ?: $item->title }}"
                                                >
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-images"></i>
                            <span>{{ db_trans('gallery.empty_state') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="gallery-cta">
        <div class="home-shell">
            <div class="cta-box">
                <div class="cta-copy">
                    <span class="section-mini-kicker">{{ db_trans('gallery.cta.kicker') }}</span>
                    <h3>{{ $pageCtaTitle }}</h3>
                    <p>{{ $pageCtaText }}</p>
                </div>

                <div class="cta-actions">
                    <a href="{{ route('frontend.announcements') }}" class="btn-primary">
                        {{ $pageCtaPrimary }}
                    </a>

                    <a href="{{ route('frontend.contact') }}" class="btn-secondary-soft">
                        {{ $pageCtaSecondary }}
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection