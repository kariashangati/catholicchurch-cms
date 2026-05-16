@extends('frontend.layouts.app')

@section('title', $hall->name.' | '.db_trans('frontend_hall_booking_title'))
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) $hall->description), 160))

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/hall-booking.css') }}">
@endpush

@section('content')
@php
    $cover = $hall->images->first();
    $coverUrl = $cover ? asset('storage/'.$cover->image_path) : asset('uploads/frontend/projects/proj.jpeg');
@endphp

<div class="hall-public-page hall-detail-page">
    <section class="hall-detail-hero" style="--hall-cover:url('{{ $coverUrl }}')">
        <div class="home-shell">
            <div class="hall-detail-hero-panel">
                <a href="{{ route('frontend.hall-bookings.index') }}" class="hall-back-link"><i class="bi bi-arrow-left"></i>{{ db_trans('frontend_hall_back_to_halls') }}</a>
                <span class="hall-public-kicker"><i class="bi bi-building"></i>{{ db_trans('frontend_hall_booking_kicker') }}</span>
                <h1>{{ $hall->name }}</h1>
                <p>{{ $hall->description ?: db_trans('frontend_hall_default_description') }}</p>
                <div class="hall-detail-facts">
                    @if($hall->capacity)
                        <span><i class="bi bi-people"></i>{{ number_format($hall->capacity) }} {{ db_trans('frontend_hall_capacity_people') }}</span>
                    @endif
                    @if($hall->location)
                        <span><i class="bi bi-geo-alt"></i>{{ $hall->location }}</span>
                    @endif
                    <span><i class="bi bi-cash-stack"></i>{{ db_trans('frontend_hall_from_price') }} TZS {{ number_format((float) $hall->default_price, 2) }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="hall-public-section">
        <div class="home-shell">
            <div class="hall-detail-layout">
                <div class="hall-main-column">
                    @if($hall->images->count() > 1)
                        <div class="hall-gallery-strip">
                            @foreach($hall->images as $image)
                                <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $image->caption ?: $hall->name }}">
                            @endforeach
                        </div>
                    @endif

                    <div class="hall-calendar-card" data-calendar-root data-availability-url="{{ route('frontend.hall-bookings.availability', $hall) }}">
                        <div class="hall-calendar-head">
                            <div>
                                <span class="section-mini-kicker">{{ db_trans('frontend_hall_calendar') }}</span>
                                <h2>{{ db_trans('frontend_hall_choose_available_date') }}</h2>
                                <p>{{ db_trans('frontend_hall_calendar_help') }}</p>
                            </div>
                            <div class="hall-calendar-controls">
                                <button type="button" class="calendar-nav-btn" data-calendar-prev><i class="bi bi-chevron-left"></i></button>
                                <strong data-calendar-month>{{ $calendar['month_label'] }}</strong>
                                <button type="button" class="calendar-nav-btn" data-calendar-next><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>

                        <div class="hall-calendar-weekdays">
                            <span>{{ db_trans('weekday_sun') }}</span>
                            <span>{{ db_trans('weekday_mon') }}</span>
                            <span>{{ db_trans('weekday_tue') }}</span>
                            <span>{{ db_trans('weekday_wed') }}</span>
                            <span>{{ db_trans('weekday_thu') }}</span>
                            <span>{{ db_trans('weekday_fri') }}</span>
                            <span>{{ db_trans('weekday_sat') }}</span>
                        </div>

                        <div class="hall-calendar-grid" data-calendar-grid data-year="{{ $calendar['year'] }}" data-month="{{ $calendar['month'] }}">
                            @foreach($calendar['days'] as $day)
                                @if(is_null($day))
                                    <div class="hall-day is-empty"></div>
                                @else
                                    <button type="button"
                                            class="hall-day status-{{ $day['status'] }} {{ $day['available'] ? 'is-available' : 'is-disabled' }}"
                                            data-date="{{ $day['date'] }}"
                                            data-price="{{ $day['price'] }}"
                                            data-price-label="{{ $day['price_label'] }}"
                                            data-status="{{ $day['status'] }}"
                                            data-status-label="{{ $day['status_label'] }}"
                                            @disabled(! $day['available'])>
                                        <span class="day-number">{{ $day['day'] }}</span>
                                        <small>{{ $day['status_label'] }}</small>
                                        <strong>{{ $day['price_label'] }}</strong>
                                    </button>
                                @endif
                            @endforeach
                        </div>

                        <div class="hall-calendar-legend">
                            <span><i class="legend-dot available"></i>{{ db_trans('frontend_hall_status_available') }}</span>
                            <span><i class="legend-dot pending"></i>{{ db_trans('frontend_hall_status_pending') }}</span>
                            <span><i class="legend-dot booked"></i>{{ db_trans('frontend_hall_status_booked') }}</span>
                            <span><i class="legend-dot blocked"></i>{{ db_trans('frontend_hall_status_blocked') }}</span>
                        </div>
                    </div>

                    @if($hall->conditions)
                        <div class="hall-info-card">
                            <h3><i class="bi bi-shield-check"></i>{{ db_trans('frontend_hall_conditions') }}</h3>
                            <div class="hall-rich-text">{!! nl2br(e($hall->conditions)) !!}</div>
                        </div>
                    @endif
                </div>

                <aside class="hall-booking-panel">
                    <div class="hall-selected-card">
                        <span>{{ db_trans('frontend_hall_selected_date') }}</span>
                        <strong data-selected-date-text>{{ db_trans('frontend_hall_no_date_selected') }}</strong>
                        <small data-selected-price-text>{{ db_trans('frontend_hall_select_date_to_see_price') }}</small>
                    </div>

                    <form method="POST" action="{{ route('frontend.hall-bookings.store', $hall) }}" enctype="multipart/form-data" class="hall-booking-form">
                        @csrf
                        <input type="hidden" name="booking_date" value="{{ old('booking_date') }}" data-booking-date-input>

                        <div class="form-group">
                            <label>{{ db_trans('frontend_hall_customer_name') }} *</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" required>
                            @error('customer_name')<small class="form-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-group">
                            <label>{{ db_trans('frontend_hall_customer_phone') }} *</label>
                            <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required placeholder="07XXXXXXXX">
                            @error('customer_phone')<small class="form-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="form-group">
                            <label>{{ db_trans('frontend_hall_customer_email') }}</label>
                            <input type="email" name="customer_email" value="{{ old('customer_email') }}">
                            @error('customer_email')<small class="form-error">{{ $message }}</small>@enderror
                        </div>

                        <div class="payment-box">
                            <h4>{{ db_trans('frontend_hall_payment_details') }}</h4>
                            @if($hall->bank_name || $hall->bank_account_number || $hall->bank_account_name)
                                <dl>
                                    @if($hall->bank_name)<div><dt>{{ db_trans('bank_name') }}</dt><dd>{{ $hall->bank_name }}</dd></div>@endif
                                    @if($hall->bank_account_name)<div><dt>{{ db_trans('bank_account_name') }}</dt><dd>{{ $hall->bank_account_name }}</dd></div>@endif
                                    @if($hall->bank_account_number)<div><dt>{{ db_trans('bank_account_number') }}</dt><dd>{{ $hall->bank_account_number }}</dd></div>@endif
                                </dl>
                            @endif
                            @if($hall->payment_instructions)
                                <p>{{ $hall->payment_instructions }}</p>
                            @else
                                <p>{{ db_trans('frontend_hall_payment_manual_help') }}</p>
                            @endif
                        </div>

                        <details class="optional-payment-details">
                            <summary>{{ db_trans('frontend_hall_already_paid') }}</summary>
                            <div class="form-group">
                                <label>{{ db_trans('frontend_hall_payment_reference') }}</label>
                                <input type="text" name="payment_reference" value="{{ old('payment_reference') }}">
                            </div>
                            <div class="form-group">
                                <label>{{ db_trans('frontend_hall_payment_proof') }}</label>
                                <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <div class="form-group">
                                <label>{{ db_trans('frontend_hall_payment_note') }}</label>
                                <textarea name="payment_note" rows="3">{{ old('payment_note') }}</textarea>
                            </div>
                        </details>

                        <label class="check-row">
                            <input type="checkbox" name="accept_conditions" value="1" required>
                            <span>{{ db_trans('frontend_hall_accept_conditions') }}</span>
                        </label>
                        @error('accept_conditions')<small class="form-error">{{ $message }}</small>@enderror
                        @error('booking_date')<small class="form-error">{{ $message }}</small>@enderror

                        <button type="submit" class="hall-public-btn hall-public-btn-primary w-100" data-submit-booking disabled>
                            <i class="bi bi-send-check"></i>{{ db_trans('frontend_hall_submit_booking') }}
                        </button>
                    </form>
                </aside>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('frontend-assets/js/hall-booking.js') }}"></script>
@endpush
