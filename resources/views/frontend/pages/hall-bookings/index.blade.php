@extends('frontend.layouts.app')

@section('title', db_trans('frontend_hall_booking_title'))
@section('meta_description', db_trans('frontend_hall_booking_meta'))

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/hall-booking.css') }}">
@endpush

@section('content')
<div class="hall-public-page">
    <section class="hall-public-hero">
        <div class="home-shell">
            <div class="hall-public-hero-grid">
                <div class="hall-public-hero-copy">
                    <span class="hall-public-kicker"><i class="bi bi-building-check"></i>{{ db_trans('frontend_hall_booking_kicker') }}</span>
                    <h1>{{ db_trans('frontend_hall_booking_heading') }}</h1>
                    <p>{{ db_trans('frontend_hall_booking_subtitle') }}</p>
                    <div class="hall-public-hero-actions">
                        @if($featuredHall)
                            <a href="{{ route('frontend.hall-bookings.show', $featuredHall) }}" class="hall-public-btn hall-public-btn-primary">
                                <i class="bi bi-calendar2-check"></i>{{ db_trans('frontend_hall_choose_date') }}
                            </a>
                        @endif
                        <a href="{{ route('frontend.hall-bookings.track') }}" class="hall-public-btn hall-public-btn-soft">
                            <i class="bi bi-search"></i>{{ db_trans('frontend_hall_track_booking') }}
                        </a>
                    </div>
                </div>

                <div class="hall-public-hero-card">
                    <div class="hero-card-icon"><i class="bi bi-calendar-week"></i></div>
                    <span>{{ db_trans('frontend_hall_next_available') }}</span>
                    @if($featuredHall && $nextAvailable)
                        <strong>{{ $nextAvailable['date']->translatedFormat('d M Y') }}</strong>
                        <small>{{ $featuredHall->name }} · TZS {{ number_format($nextAvailable['price'], 2) }}</small>
                    @else
                        <strong>{{ db_trans('frontend_hall_no_available_dates') }}</strong>
                        <small>{{ db_trans('frontend_hall_contact_admin') }}</small>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="hall-public-section">
        <div class="home-shell">
            <div class="hall-public-section-head">
                <div>
                    <span class="section-mini-kicker">{{ db_trans('frontend_hall_available_halls') }}</span>
                    <h2>{{ db_trans('frontend_hall_select_hall') }}</h2>
                    <p>{{ db_trans('frontend_hall_select_hall_help') }}</p>
                </div>
                <a href="{{ route('frontend.hall-bookings.track') }}" class="hall-public-link">{{ db_trans('frontend_hall_already_booked') }}</a>
            </div>

            @if($halls->isEmpty())
                <div class="hall-public-empty">
                    <i class="bi bi-building-slash"></i>
                    <h3>{{ db_trans('frontend_hall_no_halls') }}</h3>
                    <p>{{ db_trans('frontend_hall_no_halls_help') }}</p>
                </div>
            @else
                <div class="hall-card-grid">
                    @foreach($halls as $hall)
                        @php
                            $cover = $hall->images->first();
                            $coverUrl = $cover ? asset('storage/'.$cover->image_path) : asset('uploads/frontend/projects/proj.jpeg');
                        @endphp
                        <article class="hall-card">
                            <a href="{{ route('frontend.hall-bookings.show', $hall) }}" class="hall-card-image">
                                <img src="{{ $coverUrl }}" alt="{{ $hall->name }}">
                                <span class="hall-card-price">TZS {{ number_format((float) $hall->default_price, 2) }}</span>
                            </a>
                            <div class="hall-card-body">
                                <div class="hall-card-meta">
                                    @if($hall->capacity)
                                        <span><i class="bi bi-people"></i>{{ number_format($hall->capacity) }} {{ db_trans('frontend_hall_capacity_people') }}</span>
                                    @endif
                                    @if($hall->location)
                                        <span><i class="bi bi-geo-alt"></i>{{ $hall->location }}</span>
                                    @endif
                                </div>
                                <h3><a href="{{ route('frontend.hall-bookings.show', $hall) }}">{{ $hall->name }}</a></h3>
                                <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) $hall->description), 130) }}</p>
                                <div class="hall-card-footer">
                                    <span>{{ number_format($hall->approved_bookings_count) }} {{ db_trans('frontend_hall_confirmed_bookings') }}</span>
                                    <a href="{{ route('frontend.hall-bookings.show', $hall) }}">{{ db_trans('frontend_hall_view_calendar') }} <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
