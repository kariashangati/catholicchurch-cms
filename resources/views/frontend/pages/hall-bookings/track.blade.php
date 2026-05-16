@extends('frontend.layouts.app')

@section('title', db_trans('frontend_hall_track_booking'))

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/hall-booking.css') }}">
@endpush

@section('content')
<div class="hall-public-page">
    <section class="hall-public-hero hall-track-hero">
        <div class="home-shell">
            <div class="hall-public-hero-grid compact">
                <div class="hall-public-hero-copy">
                    <span class="hall-public-kicker"><i class="bi bi-search-heart"></i>{{ db_trans('frontend_hall_track_kicker') }}</span>
                    <h1>{{ db_trans('frontend_hall_track_booking') }}</h1>
                    <p>{{ db_trans('frontend_hall_track_help') }}</p>
                </div>
                <form method="POST" action="{{ route('frontend.hall-bookings.track.submit') }}" class="track-form-card">
                    @csrf
                    <div class="form-group">
                        <label>{{ db_trans('frontend_hall_booking_reference') }}</label>
                        <input type="text" name="reference" value="{{ request('reference') }}" required placeholder="HB-260510-XXXXX">
                    </div>
                    <div class="form-group">
                        <label>{{ db_trans('frontend_hall_customer_phone') }}</label>
                        <input type="tel" name="phone" value="{{ request('phone') }}" required placeholder="07XXXXXXXX">
                    </div>
                    <button class="hall-public-btn hall-public-btn-primary w-100" type="submit"><i class="bi bi-search"></i>{{ db_trans('search') }}</button>
                </form>
            </div>
        </div>
    </section>

    <section class="hall-public-section">
        <div class="home-shell">
            @if($notFound)
                <div class="hall-public-empty">
                    <i class="bi bi-exclamation-circle"></i>
                    <h3>{{ db_trans('frontend_hall_booking_not_found') }}</h3>
                    <p>{{ db_trans('frontend_hall_booking_not_found_help') }}</p>
                </div>
            @endif

            @if($booking)
                <div class="track-result-layout">
                    <div class="track-status-card">
                        <span class="hall-public-kicker">{{ db_trans('frontend_hall_booking_reference') }}</span>
                        <h2>{{ $booking->booking_reference }}</h2>
                        <div class="track-status-badges">
                            <span class="status-badge booking-{{ $booking->booking_status }}">{{ $statusLabels[$booking->booking_status] ?? $booking->booking_status }}</span>
                            <span class="status-badge payment-{{ $booking->payment_status }}">{{ $paymentLabels[$booking->payment_status] ?? $booking->payment_status }}</span>
                        </div>

                        <div class="confirmation-grid compact-grid">
                            <div><span>{{ db_trans('halls') }}</span><strong>{{ $booking->hall->name }}</strong></div>
                            <div><span>{{ db_trans('frontend_hall_booking_date') }}</span><strong>{{ optional($booking->booking_date)->translatedFormat('d M Y') }}</strong></div>
                            <div><span>{{ db_trans('amount') }}</span><strong>TZS {{ number_format((float) $booking->price, 2) }}</strong></div>
                            <div><span>{{ db_trans('frontend_hall_customer_name') }}</span><strong>{{ $booking->customer_name }}</strong></div>
                        </div>

                        @if($booking->admin_note)
                            <div class="admin-note-box">
                                <strong>{{ db_trans('admin_note') }}</strong>
                                <p>{{ $booking->admin_note }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="payment-submit-card">
                        <h3>{{ db_trans('frontend_hall_submit_payment_info') }}</h3>
                        <p>{{ db_trans('frontend_hall_submit_payment_info_help') }}</p>

                        @if($booking->payment_status === \App\Models\HallBooking::PAYMENT_VERIFIED)
                            <div class="success-note"><i class="bi bi-check2-circle"></i>{{ db_trans('frontend_hall_payment_already_verified') }}</div>
                        @else
                            <form method="POST" action="{{ route('frontend.hall-bookings.payment.submit') }}" enctype="multipart/form-data" class="hall-booking-form compact-form">
                                @csrf
                                <input type="hidden" name="reference" value="{{ $booking->booking_reference }}">
                                <input type="hidden" name="phone" value="{{ request('phone', $booking->customer_phone) }}">
                                <div class="form-group">
                                    <label>{{ db_trans('frontend_hall_payment_reference') }} *</label>
                                    <input type="text" name="payment_reference" value="{{ old('payment_reference', $booking->payment_reference) }}" required>
                                </div>
                                <div class="form-group">
                                    <label>{{ db_trans('frontend_hall_payment_proof') }}</label>
                                    <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf">
                                </div>
                                <div class="form-group">
                                    <label>{{ db_trans('frontend_hall_payment_note') }}</label>
                                    <textarea name="payment_note" rows="3">{{ old('payment_note', $booking->payment_note) }}</textarea>
                                </div>
                                <button class="hall-public-btn hall-public-btn-primary w-100" type="submit"><i class="bi bi-upload"></i>{{ db_trans('frontend_hall_submit_payment') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
