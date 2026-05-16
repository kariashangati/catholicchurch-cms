@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/project.css') }}">
@endpush

@php
    $pageKicker = db_trans('projects.page_kicker');
    $pageTitle = db_trans('projects.page_title');
    $pageSubtitle = db_trans('projects.page_subtitle');
    $pageTotal = db_trans('projects.stats.total');
    $pageActive = db_trans('projects.stats.active');
    $pageCompleted = db_trans('projects.stats.completed');
    $pageLatest = db_trans('projects.section_title');
    $pageCtaTitle = db_trans('projects.cta.title');
    $pageCtaText = db_trans('projects.cta.text');
    $pageCtaPrimary = db_trans('projects.cta.primary');
    $pageCtaSecondary = db_trans('projects.cta.secondary');
@endphp

@section('content')

<div class="projects-page">

    {{-- HERO --}}
    <section class="projects-hero">
        <div class="home-shell">
            <div class="projects-hero-wrap">
                <div class="projects-hero-inner">
                    <span class="projects-kicker">
                        <i class="bi bi-kanban"></i>
                        {{ $pageKicker }}
                    </span>

                    <h1>{{ $pageTitle }}</h1>

                    <p>{{ $pageSubtitle }}</p>

                    <div class="projects-stats">
                        <div class="stat-chip">
                            <strong>{{ $totalCount ?? 0 }}</strong>
                            <span>{{ $pageTotal }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $activeCount ?? 0 }}</strong>
                            <span>{{ $pageActive }}</span>
                        </div>

                        <div class="stat-chip">
                            <strong>{{ $completedCount ?? 0 }}</strong>
                            <span>{{ $pageCompleted }}</span>
                        </div>
                    </div>
                </div>

                <div class="projects-hero-aside">
                    <div class="hero-mini-card">
                        <span class="hero-mini-label">{{ db_trans('projects.hero.label') }}</span>
                        <h4>{{ db_trans('projects.hero.title') }}</h4>
                        <p>{{ db_trans('projects.hero.text') }}</p>

                        <div class="hero-mini-metrics">
                            <div>
                                <strong>TZS {{ number_format($totalRaised ?? 0, 0) }}</strong>
                                <span>{{ db_trans('projects.hero.raised') }}</span>
                            </div>
                            <div>
                                <strong>TZS {{ number_format($totalGoal ?? 0, 0) }}</strong>
                                <span>{{ db_trans('projects.hero.goal') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURED --}}
    @if(!empty($featured))
        <section class="projects-featured">
            <div class="home-shell">
                <div class="section-block">
                    <div class="section-block-head">
                        <div>
                            <span class="section-mini-kicker">{{ db_trans('projects.featured.kicker') }}</span>
                            <h3 class="section-block-title">{{ db_trans('projects.featured.title') }}</h3>
                        </div>
                    </div>

                    <div class="featured-project-card premium-hover-lift">
                        <div class="featured-project-image">
                            <img
                                src="{{ $featured->cover_image_url ?: asset('uploads/frontend/projects/proj.jpeg') }}"
                                alt="{{ $featured->name }}"
                            >
                        </div>

                        <div class="featured-project-content">
                            <div class="featured-project-meta">
                                <span class="badge badge-category">
                                    {{ $featured->category_name }}
                                </span>

                                <span class="badge badge-status status-{{ \Illuminate\Support\Str::slug($featured->status ?? 'project') }}">
                                    {{ $featured->status_badge }}
                                </span>
                            </div>

                            <h2>{{ $featured->name }}</h2>

                            <p>{{ $featured->short_description }}</p>

                            <div class="project-progress-card">
                                <div class="project-progress-top">
                                    <span>{{ db_trans('projects.progress.label') }}</span>
                                    <strong>{{ $featured->progress_percent }}%</strong>
                                </div>

                                <div class="project-progress-bar">
                                    <div
                                        class="project-progress-fill"
                                        style="width: {{ $featured->progress_percent }}%;"
                                    ></div>
                                </div>

                                <div class="project-progress-stats">
                                    <span>TZS {{ number_format($featured->progress_amount ?? 0, 0) }} {{ db_trans('projects.progress.raised') }}</span>
                                    <span>TZS {{ number_format($featured->goal_amount ?? 0, 0) }} {{ db_trans('projects.progress.goal') }}</span>
                                </div>
                            </div>

                            <div class="featured-project-actions">
                                <a href="{{ route('frontend.giving') }}" class="btn-primary">
                                    {{ db_trans('projects.featured.support') }}
                                </a>

                                <a href="{{ route('frontend.contact') }}" class="btn-secondary-soft">
                                    {{ db_trans('projects.featured.learn_more') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- GRID --}}
    <section class="projects-grid-section">
        <div class="home-shell">
            <div class="section-block">
                <div class="section-block-head">
                    <div>
                        <span class="section-mini-kicker">{{ db_trans('projects.grid.kicker') }}</span>
                        <h3 class="section-block-title">{{ $pageLatest }}</h3>
                    </div>
                </div>

                <div class="projects-grid">
                    @forelse($gridItems as $item)
                        <article class="project-card premium-hover-lift">
                            <div class="project-card-image">
                                <img
                                    src="{{ $item->cover_image_url ?: asset('uploads/frontend/projects/proj.jpeg') }}"
                                    alt="{{ $item->name }}"
                                >
                            </div>

                            <div class="project-card-body">
                                <div class="project-card-meta">
                                    <span class="badge badge-category">
                                        {{ $item->category_name }}
                                    </span>

                                    <span class="badge badge-status status-{{ \Illuminate\Support\Str::slug($item->status ?? 'project') }}">
                                        {{ $item->status_badge }}
                                    </span>
                                </div>

                                <h4>{{ $item->name }}</h4>

                                <p>{{ $item->short_description }}</p>

                                <div class="project-progress-card compact-progress">
                                    <div class="project-progress-top">
                                        <span>{{ db_trans('projects.progress.label') }}</span>
                                        <strong>{{ $item->progress_percent }}%</strong>
                                    </div>

                                    <div class="project-progress-bar">
                                        <div
                                            class="project-progress-fill"
                                            style="width: {{ $item->progress_percent }}%;"
                                        ></div>
                                    </div>

                                    <div class="project-progress-stats">
                                        <span>TZS {{ number_format($item->progress_amount ?? 0, 0) }}</span>
                                        <span>TZS {{ number_format($item->goal_amount ?? 0, 0) }}</span>
                                    </div>
                                </div>

                                <div class="project-card-actions">
                                    <a href="{{ route('frontend.giving') }}" class="card-action-link">
                                        {{ db_trans('projects.grid.support') }}
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state">
                            <i class="bi bi-folder2-open"></i>
                            <span>{{ db_trans('projects.empty_state') }}</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="projects-cta">
        <div class="home-shell">
            <div class="cta-box">
                <div class="cta-copy">
                    <span class="section-mini-kicker">{{ db_trans('projects.cta.kicker') }}</span>
                    <h3>{{ $pageCtaTitle }}</h3>
                    <p>{{ $pageCtaText }}</p>
                </div>

                <div class="cta-actions">
                    <a href="{{ route('frontend.giving') }}" class="btn-primary">
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