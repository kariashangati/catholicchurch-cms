@extends('frontend.layouts.app')

@php
    $siteName = db_trans('site.name');
    if ($siteName === 'Site.Name') {
        $siteName = $settings['site.name'] ?? 'ECCLESIA';
    }

    $siteTagline = db_trans('site.tagline');
    if ($siteTagline === 'Site.Tagline') {
        $siteTagline = $settings['site.tagline'] ?? 'Contemporary Worship Community';
    }

    $heroBadge = db_trans('home.hero_badge');
    if ($heroBadge === 'Home.Hero Badge') {
        $heroBadge = $settings['hero_badge'] ?? 'PAROKIA YA EPIPHANIA';
    }
@endphp

@section('title', $siteName . ' | ' . $siteTagline)

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/home.css') }}">
@endpush

@section('content')

<!-- HERO -->
<section class="hero-slider swiper heroSwiper premium-hero">
    <div class="swiper-wrapper">
        @forelse($banners as $banner)
            @php
                $overlayOpacity = $banner->overlay_opacity ?? 0.55;
                $hasPrimary = !empty($banner->primary_button_text) && !empty($banner->primary_button_link);
                $hasSecondary = !empty($banner->secondary_button_text) && !empty($banner->secondary_button_link);
            @endphp

            <div class="swiper-slide">
                @if($banner->is_video && $banner->background_url)
                    <video autoplay muted loop playsinline poster="{{ $banner->poster_url ?? '' }}">
                        <source src="{{ $banner->background_url }}" type="video/mp4">
                    </video>
                @elseif($banner->background_url)
                    <img
                        src="{{ $banner->background_url }}"
                        alt="{{ $banner->title }}"
                        class="hero-image"
                    >
                @else
                    <img
                        src="{{ asset('uploads/frontend/hero/hero-main.jpg') }}"
                        alt="{{ $banner->title }}"
                        class="hero-image"
                    >
                @endif

                <div class="hero-overlay" style="background:
                    linear-gradient(180deg,
                        rgba(255,255,255,0.10) 0%,
                        rgba(255,255,255,0.02) 30%,
                        rgba(15,23,42,{{ $overlayOpacity * 0.65 }}) 100%
                    ),
                    radial-gradient(circle at top left,
                        rgba(255,255,255,0.50) 0%,
                        rgba(255,255,255,0.06) 45%,
                        transparent 70%
                    );">
                </div>

                <div class="hero-content hero-content-premium">
                    <div class="hero-shell">
                        @if($hasPrimary || $hasSecondary)
                            <div class="hero-actions">
                                @if($hasPrimary)
                                    <a href="{{ $banner->primary_button_link }}" class="btn-primary">
                                        {{ $banner->primary_button_text }}
                                    </a>
                                @endif

                                @if($hasSecondary)
                                    <a href="{{ $banner->secondary_button_link }}" class="btn-secondary-hero">
                                        {{ $banner->secondary_button_text }}
                                    </a>
                                @endif
                            </div>
                        @endif

                        <div class="hero-mini-stats">
                            <div class="hero-mini-stat">
                                <strong>{{ $announcements->count() }}</strong>
                                <span>{{ db_trans('home.stats.updates') }}</span>
                            </div>
                            <div class="hero-mini-stat">
                                <strong>{{ $projects->count() }}</strong>
                                <span>{{ db_trans('home.stats.projects') }}</span>
                            </div>
                            <div class="hero-mini-stat">
                                <strong>{{ isset($jumuiyas) ? $jumuiyas->count() : 0 }}</strong>
                                <span>{{ db_trans('home.stats.communities') }}</span>
                            </div>
                            <div class="hero-mini-stat">
                                <strong>{{ $galleries->count() }}</strong>
                                <span>{{ db_trans('home.stats.moments') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="swiper-slide">
                <video autoplay muted loop playsinline poster="{{ asset('uploads/frontend/hero/hero-poster.jpg') }}">
                    <source src="{{ asset('uploads/frontend/hero/hero-video.mp4') }}" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>

                <div class="hero-content hero-content-premium">
                    <div class="hero-shell">
                        <div class="hero-actions">
                            <a href="{{ route('frontend.contact') }}" class="btn-primary">{{ db_trans('home.join_us') }}</a>
                            <a href="{{ route('frontend.masses') }}" class="btn-secondary-hero">{{ db_trans('home.mass_schedule') }}</a>
                        </div>

                        <div class="hero-mini-stats">
                            <div class="hero-mini-stat">
                                <strong>{{ $announcements->count() }}</strong>
                                <span>{{ db_trans('home.stats.updates') }}</span>
                            </div>
                            <div class="hero-mini-stat">
                                <strong>{{ $projects->count() }}</strong>
                                <span>{{ db_trans('home.stats.projects') }}</span>
                            </div>
                            <div class="hero-mini-stat">
                                <strong>{{ isset($jumuiyas) ? $jumuiyas->count() : 0 }}</strong>
                                <span>{{ db_trans('home.stats.communities') }}</span>
                            </div>
                            <div class="hero-mini-stat">
                                <strong>{{ $galleries->count() }}</strong>
                                <span>{{ db_trans('home.stats.moments') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-pagination"></div>
</section>

<!-- INTRO FEATURE STRIP -->
<section class="section-pad home-intro-section">
    <div class="home-shell">
        <div class="premium-intro-card">
            <div class="premium-intro-copy">
                <span class="section-kicker">{{ db_trans('home.intro.kicker') }}</span>
                <h2 class="section-title left-title">
                    {{ db_trans('home.intro.title_before') }}
                    <span class="gradient-text">{{ db_trans('home.intro.title_highlight') }}</span>
                </h2>
                <p class="premium-intro-text">
                    {{ db_trans('home.intro.description') }}
                </p>
            </div>

            <div class="premium-feature-grid">
                <div class="premium-feature-card">
                    <i class="bi bi-music-note-beamed"></i>
                    <h3>{{ db_trans('home.features.worship.title') }}</h3>
                    <p>{{ db_trans('home.features.worship.description') }}</p>
                </div>
                <div class="premium-feature-card">
                    <i class="bi bi-people"></i>
                    <h3>{{ db_trans('home.features.community.title') }}</h3>
                    <p>{{ db_trans('home.features.community.description') }}</p>
                </div>
                <div class="premium-feature-card">
                    <i class="bi bi-stars"></i>
                    <h3>{{ db_trans('home.features.formation.title') }}</h3>
                    <p>{{ db_trans('home.features.formation.description') }}</p>
                </div>
                <div class="premium-feature-card">
                    <i class="bi bi-heart"></i>
                    <h3>{{ db_trans('home.features.service.title') }}</h3>
                    <p>{{ db_trans('home.features.service.description') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ANNOUNCEMENTS -->
<section class="section-pad announcements-section">
    <div class="home-shell">
        <div class="section-panel">
            <div class="section-heading-wrap premium-heading-wrap">
                <div>
                    <span class="section-kicker">{{ db_trans('home.announcements.kicker') }}</span>
                    <h2 class="section-title left-title">
                        {{ db_trans('home.announcements.title_before') }}
                        <span class="gradient-text">{{ db_trans('home.announcements.title_highlight') }}</span>
                    </h2>
                </div>
                <a href="{{ route('frontend.announcements') }}" class="section-link">
                    {{ db_trans('home.announcements.view_all') }} <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="swiper announcementSwiper modern-announcement-swiper premium-swiper">
                <div class="swiper-wrapper">
                    @forelse($announcements as $announcement)
                        @php
                            $announcementUrl = \Illuminate\Support\Facades\Route::has('frontend.announcement')
                                ? route('frontend.announcement', $announcement->slug)
                                : route('frontend.announcements');
                        @endphp

                        <div class="swiper-slide">
                            <article class="announcement-card-modern compact-card">
                                <img
                                    src="{{ $announcement->image_url ?: asset('uploads/frontend/announcements/ann6.jpeg') }}"
                                    alt="{{ $announcement->title }}"
                                >
                                <div class="announcement-overlay"></div>

                                <div class="announcement-content compact-overlay-content">
                                    <span class="announcement-badge {{ $announcement->is_featured ? 'badge-popular' : '' }}">
                                        {{ $announcement->card_badge }}
                                    </span>

                                    <div class="announcement-meta">
                                        <i class="bi bi-calendar-event"></i>
                                        {{ $announcement->card_date ?: db_trans('home.announcements.default_meta') }}
                                    </div>

                                    <h3>{{ $announcement->title }}</h3>
                                    <p>{{ $announcement->card_summary }}</p>

                                    <a href="{{ $announcementUrl }}" class="announcement-link">
                                        {{ db_trans('home.announcements.learn_more') }} <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="swiper-slide">
                            <article class="announcement-card-modern compact-card">
                                <img
                                    src="{{ asset('uploads/frontend/announcements/ann6.jpeg') }}"
                                    alt="announcement placeholder"
                                >
                                <div class="announcement-overlay"></div>

                                <div class="announcement-content compact-overlay-content">
                                    <span class="announcement-badge">{{ db_trans('home.announcements.empty_badge') }}</span>
                                    <div class="announcement-meta">
                                        <i class="bi bi-calendar-event"></i>
                                        {{ db_trans('home.announcements.empty_meta') }}
                                    </div>
                                    <h3>{{ db_trans('home.announcements.empty_title') }}</h3>
                                    <p>{{ db_trans('home.announcements.empty_description') }}</p>
                                    <a href="{{ route('frontend.contact') }}" class="announcement-link">
                                        {{ db_trans('home.announcements.contact_us') }} <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforelse
                </div>

                <div class="announcement-controls">
                    <div class="swiper-pagination"></div>
                    <div class="announcement-navs">
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PROJECTS -->
<section class="section-pad projects-section-modern">
    <div class="home-shell">
        <div class="section-panel section-panel-soft">

            <div class="section-heading-wrap premium-heading-wrap">
                <div>
                    <span class="section-kicker">{{ db_trans('home.projects.kicker') }}</span>
                    <h2 class="section-title left-title">
                        {{ db_trans('home.projects.title_before') }}
                        <span class="gradient-text">{{ db_trans('home.projects.title_highlight') }}</span>
                    </h2>
                </div>
                <a href="{{ route('frontend.projects') }}" class="section-link">
                    {{ db_trans('home.projects.view_all') }} <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="swiper projectSwiper modern-project-swiper premium-swiper">
                <div class="swiper-wrapper">
                    @forelse($projects as $project)
                        <div class="swiper-slide">
                            <article class="project-card-modern compact-card">
                                <img
                                    src="{{ $project->cover_image_url ?: asset('uploads/frontend/projects/proj.jpeg') }}"
                                    alt="{{ $project->name }}"
                                >
                                <div class="project-overlay"></div>

                                <div class="project-content compact-overlay-content">
                                    <span class="project-badge">{{ $project->category_name }}</span>

                                    <div class="project-meta">
                                        <i class="bi bi-folder2-open"></i> {{ $project->status_badge }}
                                    </div>

                                    <h3>{{ $project->name }}</h3>
                                    <p>{{ $project->short_description }}</p>

                                    <div class="project-progress-wrap">

                                        <div class="project-progress-top">
                                            <span>{{ db_trans('home.projects.progress') }}</span>
                                            <strong>{{ $project->progress_percent }}%</strong>
                                        </div>

                                        <div class="project-progress-bar">
                                            <div
                                                class="project-progress-fill"
                                                style="width: {{ $project->progress_percent }}%;"
                                            ></div>
                                        </div>


                                        <div class="project-stats">
                                            <span>TZS {{ number_format($project->progress_amount, 0) }} {{ db_trans('home.projects.raised') }}</span>
                                            <span>
                                                TZS {{ number_format($project->goal_amount > 0 ? $project->goal_amount : ($project->budget_amount ?? 0), 0) }} {{ db_trans('home.projects.goal') }}
                                            </span>
                                        </div>
                                    </div>

                                    
                                    <a href="{{ route('frontend.projects') }}" class="project-link">
                                        {{ db_trans('home.projects.view_project') }} <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="swiper-slide">
                            <article class="project-card-modern compact-card">
                                <img
                                    src="{{ asset('uploads/frontend/projects/proj.jpeg') }}"
                                    alt="project placeholder"
                                >
                                <div class="project-overlay"></div>

                
                                <div class="project-content compact-overlay-content">
                                    <span class="project-badge">{{ db_trans('home.projects.empty_badge') }}</span>
                                    <div class="project-meta">
                                        <i class="bi bi-folder2-open"></i> {{ db_trans('home.projects.empty_meta') }}
                                    </div>
                                    <h3>{{ db_trans('home.projects.empty_title') }}</h3>
                                    <p>{{ db_trans('home.projects.empty_description') }}</p>

                                    <div class="project-progress-wrap">
                                        <div class="project-progress-top">
                                            <span>{{ db_trans('home.projects.progress') }}</span>
                                            <strong>0%</strong>
                                        </div>
                                        <div class="project-progress-bar">
                                            <div class="project-progress-fill" style="width:0%"></div>
                                        </div>
                                        <div class="project-stats">
                                            <span>TZS 0 {{ db_trans('home.projects.raised') }}</span>
                                            <span>TZS 0 {{ db_trans('home.projects.goal') }}</span>
                                        </div>
                                    </div>

                                    <a href="{{ route('frontend.contact') }}" class="project-link">
                                        {{ db_trans('home.projects.contact_us') }} <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforelse
                </div>

                <div class="project-controls">
                    <div class="swiper-pagination"></div>
                    <div class="project-navs">
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- COMMUNITIES -->
<section class="section-pad communities-section">
    <div class="home-shell">
        <div class="section-panel">
            <div class="section-heading-wrap premium-heading-wrap">
                <div>
                    <span class="section-kicker">{{ db_trans('home.communities.kicker') }}</span>
                    <h2 class="section-title left-title">
                        {{ db_trans('home.communities.title_before') }}
                        <span class="gradient-text">{{ db_trans('home.communities.title_highlight') }}</span>
                    </h2>
                </div>
                <a href="{{ route('frontend.kandas') }}" class="section-link">
                    {{ db_trans('home.communities.view_all') }} <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="swiper zoneSwiper modern-zone-swiper premium-swiper">
                <div class="swiper-wrapper">
                    @forelse($kandas as $kanda)
                        <div class="swiper-slide">
                            <article class="zone-card-modern compact-card">
                                <img
                                    src="{{ $kanda->image_url ?: asset('uploads/frontend/kandas/kanda.jpeg') }}"
                                    alt="{{ $kanda->name }}"
                                >
                                <div class="zone-overlay"></div>

                                <div class="zone-content compact-overlay-content">
                                    <span class="zone-badge">{{ $kanda->highlight_label }}</span>

                                    <div class="zone-meta">
                                        <i class="bi bi-diagram-3"></i>
                                        {{ $kanda->jumuiyas_count }} {{ db_trans('home.communities.jumuiyas_count_label') }}
                                    </div>

                                    <h3>{{ $kanda->name }}</h3>
                                    <p>{{ $kanda->short_comment }}</p>

                                    <div class="zone-stats">
                                        <span class="zone-stat-pill">
                                            <i class="bi bi-house-door"></i>
                                            {{ $kanda->familias_count }} {{ db_trans('home.communities.families') }}
                                        </span>

                                        <span class="zone-stat-pill">
                                            <i class="bi bi-people"></i>
                                            {{ $kanda->members_count }} {{ db_trans('home.communities.members') }}
                                        </span>
                                    </div>

                                    <a href="{{ route('frontend.kandas') }}" class="zone-link">
                                        {{ db_trans('home.communities.view_community') }} <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="swiper-slide">
                            <article class="zone-card-modern compact-card">
                                <img
                                    src="{{ asset('uploads/frontend/kandas/kanda.jpeg') }}"
                                    alt="community placeholder"
                                >
                                <div class="zone-overlay"></div>

                                <div class="zone-content compact-overlay-content">
                                    <span class="zone-badge">{{ db_trans('home.communities.empty_badge') }}</span>

                                    <div class="zone-meta">
                                        <i class="bi bi-diagram-3"></i>
                                        {{ db_trans('home.communities.empty_meta') }}
                                    </div>

                                    <h3>{{ db_trans('home.communities.empty_title') }}</h3>
                                    <p>{{ db_trans('home.communities.empty_description') }}</p>

                                    <div class="zone-stats">
                                        <span class="zone-stat-pill">
                                            <i class="bi bi-house-door"></i>
                                            0 {{ db_trans('home.communities.families') }}
                                        </span>

                                        <span class="zone-stat-pill">
                                            <i class="bi bi-people"></i>
                                            0 {{ db_trans('home.communities.members') }}
                                        </span>
                                    </div>

                                    <a href="{{ route('frontend.contact') }}" class="zone-link">
                                        {{ db_trans('home.communities.contact_us') }} <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforelse
                </div>

                <div class="zone-controls">
                    <div class="swiper-pagination"></div>
                    <div class="zone-navs">
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- GALLERY -->
<section class="section-pad gallery-section-modern">
    <div class="home-shell">
        <div class="section-panel section-panel-soft">
            <!-- 1. REPLACED gallery heading block -->
            <div class="section-heading-wrap premium-heading-wrap">
                <div>
                    <span class="section-kicker">{{ db_trans('home.gallery.kicker') }}</span>
                    <h2 class="section-title left-title">
                        {{ db_trans('home.gallery.title_before') }}
                        <span class="gradient-text">{{ db_trans('home.gallery.title_highlight') }}</span>
                    </h2>
                </div>
                <a href="{{ route('frontend.gallery') }}" class="section-link">
                    {{ db_trans('home.gallery.view_all') }} <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="swiper gallerySwiper modern-gallery-swiper premium-swiper">
                <div class="swiper-wrapper">
                    @forelse($galleries as $gallery)
                        <div class="swiper-slide">
                            <article class="gallery-card-modern compact-card">
                                <img
                                    src="{{ $gallery->cover_image_url ?: asset('uploads/frontend/gallery/gal.jpeg') }}"
                                    alt="{{ $gallery->image_alt }}"
                                >
                                <div class="gallery-overlay-modern"></div>

                                <div class="gallery-content-modern compact-overlay-content">
                                    <span class="gallery-chip">{{ $gallery->gallery_chip }}</span>

                                    <div class="gallery-meta-modern">
                                        <span>
                                            <i class="bi bi-calendar-event"></i>
                                            {{ $gallery->event_label }}
                                        </span>
                                        <!-- 2. REPLACED photo count label -->
                                        <span>
                                            <i class="bi bi-images"></i>
                                            {{ $gallery->images_count }} {{ db_trans('home.gallery.photos_count_label') }}
                                        </span>
                                    </div>

                                    <h3>{{ $gallery->title }}</h3>
                                    <p>{{ $gallery->short_description }}</p>

                                    <!-- 3. REPLACED "View gallery" link -->
                                    <a href="{{ route('frontend.gallery') }}" class="gallery-link-modern">
                                        {{ db_trans('home.gallery.view_gallery') }} <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="swiper-slide">
                            <article class="gallery-card-modern compact-card">
                                <img
                                    src="{{ asset('uploads/frontend/gallery/gal.jpeg') }}"
                                    alt="gallery placeholder"
                                >
                                <div class="gallery-overlay-modern"></div>

                                <!-- 4. REPLACED empty-state gallery card content -->
                                <div class="gallery-content-modern compact-overlay-content">
                                    <span class="gallery-chip">{{ db_trans('home.gallery.empty_badge') }}</span>

                                    <div class="gallery-meta-modern">
                                        <span>
                                            <i class="bi bi-camera"></i>
                                            {{ db_trans('home.gallery.empty_meta') }}
                                        </span>
                                    </div>

                                    <h3>{{ db_trans('home.gallery.empty_title') }}</h3>
                                    <p>{{ db_trans('home.gallery.empty_description') }}</p>

                                    <a href="{{ route('frontend.contact') }}" class="gallery-link-modern">
                                        {{ db_trans('home.gallery.contact_us') }} <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforelse
                </div>

                <div class="gallery-controls-modern">
                    <div class="swiper-pagination"></div>
                    <div class="gallery-navs-modern">
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section-pad home-cta-wrap">
    <div class="home-shell">
        <div class="home-cta-card">
            <div class="home-cta-copy">
                <span class="section-kicker">{{ db_trans('home.cta.kicker') }}</span>
                <h2>{{ db_trans('home.cta.title') }}</h2>
                <p>{{ db_trans('home.cta.description') }}</p>
            </div>

            <div class="home-cta-actions">
                <a href="{{ route('frontend.contact') }}" class="btn-primary">{{ db_trans('home.cta.primary') }}</a>
                <a href="{{ route('frontend.masses') }}" class="btn-secondary-hero btn-secondary-light">{{ db_trans('home.cta.secondary') }}</a>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script src="{{ asset('frontend-assets/js/home.js') }}"></script>
@endpush