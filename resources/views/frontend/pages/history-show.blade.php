@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/history.css') }}">
@endpush

@php
    $relatedTitle = db_trans('history.related.title');
    $relatedTitle = ($relatedTitle && $relatedTitle !== 'history.related.title') ? $relatedTitle : 'Related Stories';
@endphp

@section('content')

<div class="history-single">

    <!-- HERO -->
    <section class="history-single-hero">
        <div class="home-shell">

            <div class="history-single-inner">

                <div class="history-meta">
                    <span class="badge badge-date">
                        {{ optional($history->published_at)->format('M d, Y') }}
                    </span>
                </div>

                <h1>{{ $history->title }}</h1>

                @if($history->image_url)
                <div class="history-hero-image">
                    <img src="{{ $history->image_url }}" alt="{{ $history->title }}">
                </div>
                @endif

            </div>

        </div>
    </section>

    <!-- CONTENT -->
    <section class="history-content">
        <div class="home-shell">

            <div class="content-wrapper">
                {!! $history->content !!}
            </div>

        </div>
    </section>

    <!-- RELATED -->
    @if($relatedHistories->count())
    <section class="history-related">
        <div class="home-shell">

            <div class="grid-header">
                <h3>{{ $relatedTitle }}</h3>
            </div>

            <div class="grid-wrap">

                @foreach($relatedHistories as $item)
                    <a href="{{ route('frontend.history.show', $item->slug) }}"
                       class="history-card">

                        <div class="card-image">
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                        </div>

                        <div class="card-body">

                            <span class="date">
                                {{ optional($item->published_at)->format('M d, Y') }}
                            </span>

                            <h4>{{ $item->title }}</h4>

                            <p>{{ $item->card_excerpt }}</p>

                        </div>

                    </a>
                @endforeach

            </div>

        </div>
    </section>
    @endif

</div>

@endsection