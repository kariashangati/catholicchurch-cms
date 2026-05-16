@extends('frontend.layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/announcement.css') }}">
@endpush
@php
    $backLabel = db_trans('announcements.back');
    $quickInfoLabel = db_trans('announcements.quick_info');
    $typeLabel = db_trans('announcements.type');
    $publishedLabel = db_trans('announcements.published');
    $needHelpTitle = db_trans('announcements.need_help');
    $needHelpText = db_trans('announcements.need_help_text');
    $contactButton = db_trans('announcements.contact_button');
    $relatedLabel = db_trans('announcements.related');
    $readMore = db_trans('common.read_more');

    $backLabel = ($backLabel && $backLabel !== 'announcements.back') ? $backLabel : 'Back to Announcements';
    $quickInfoLabel = ($quickInfoLabel && $quickInfoLabel !== 'announcements.quick_info') ? $quickInfoLabel : 'Quick Info';
    $typeLabel = ($typeLabel && $typeLabel !== 'announcements.type') ? $typeLabel : 'Type';
    $publishedLabel = ($publishedLabel && $publishedLabel !== 'announcements.published') ? $publishedLabel : 'Published';
    $needHelpTitle = ($needHelpTitle && $needHelpTitle !== 'announcements.need_help') ? $needHelpTitle : 'Need prayer or support?';
    $needHelpText = ($needHelpText && $needHelpText !== 'announcements.need_help_text')
        ? $needHelpText
        : 'Our church family is here for you. Reach out and connect with us.';
    $contactButton = ($contactButton && $contactButton !== 'announcements.contact_button') ? $contactButton : 'Contact Us';
    $relatedLabel = ($relatedLabel && $relatedLabel !== 'announcements.related') ? $relatedLabel : 'Related Announcements';
    $readMore = ($readMore && $readMore !== 'common.read_more') ? $readMore : 'Read more';
@endphp
@section('content')

<div class="announcement-single-page">

    <!-- HERO / ARTICLE HEAD -->
    <section class="announcement-article-hero">
        <div class="home-shell">

            <div class="article-breadcrumb">
                <a href="{{ route('frontend.announcements') }}">
                    <i class="bi bi-arrow-left"></i>
                    <span>{{ $backLabel }}</span>
                </a>
            </div>

            <div class="article-hero-card">

                <div class="article-hero-media">
                    <img
                        src="{{ $announcement->hero_image_url }}"
                        alt="{{ $announcement->title }}"
                    >
                </div>

                <div class="article-hero-content">
                    <div class="article-meta-top">
                        <span class="badge badge-featured">
                            {{ $announcement->card_badge }}
                        </span>

                        @if(!empty($announcement->card_date_full))
                            <span class="article-date">
                                <i class="bi bi-calendar-event"></i>
                                {{ $announcement->card_date_full }}
                            </span>
                        @endif
                    </div>

                    <h1>{{ $announcement->title }}</h1>

                    @if(!empty($announcement->summary))
                        <p class="article-summary">
                            {{ $announcement->summary }}
                        </p>
                    @elseif(!empty($announcement->card_summary))
                        <p class="article-summary">
                            {{ $announcement->card_summary }}
                        </p>
                    @endif
                </div>

            </div>

        </div>
    </section>

    <!-- ARTICLE BODY -->
    <section class="announcement-article-body">
        <div class="home-shell">
            <div class="article-layout">

                <article class="article-main-card">
                    <div class="article-content prose-announcement">
                        {!! $announcement->content !!}
                    </div>
                </article>

                <aside class="article-sidebar">
                    <div class="article-side-card">
                        <h3>{{ $quickInfoLabel }}</h3>

                        <div class="side-info-list">
                            <div class="side-info-item">
                                <span class="side-label">{{ $typeLabel }}</span>
                                <strong>{{ $announcement->card_badge }}</strong>
                            </div>

                            @if(!empty($announcement->card_date_full))
                                <div class="side-info-item">
                                    <span class="side-label">{{ $publishedLabel }}</span>
                                    <strong>{{ $announcement->card_date_full }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="article-side-card side-cta-card">
                        <h3>{{ $needHelpTitle }}</h3>
                        <p>{{ $needHelpText }}</p>

                        <a href="{{ route('frontend.contact') }}" class="btn-primary">
                            {{ $contactButton }}
                        </a>
                    </div>
                </aside>

            </div>
        </div>
    </section>

    <!-- RELATED -->
    @if(isset($relatedAnnouncements) && $relatedAnnouncements->count())
    <section class="announcement-related-section">
        <div class="home-shell">

            <div class="grid-header">
                <h3>{{ $relatedLabel }}</h3>
            </div>

            <div class="grid-wrap">
                @foreach($relatedAnnouncements as $item)
                    <a href="{{ route('frontend.announcements.show', $item->slug) }}"
                       class="announcement-card">

                        <div class="card-image">
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
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
                @endforeach
            </div>

        </div>
    </section>
    @endif

</div>

@endsection