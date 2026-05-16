@extends('frontend.layouts.app')

@section('title', db_trans('frontend_hall_booking_confirmation'))

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/hall-booking.css') }}">
@endpush

@section('content')
<div class="hall-public-page">
    <section class="hall-public-section hall-confirm-section">
        <div class="home-shell">
            <div class="hall-confirm-card">
                <div class="confirm-icon"><i class="bi bi-check2-circle"></i></div>
                <span class="hall-public-kicker">{{ db_trans('frontend_hall_booking_received') }}</span>
                <h1>{{ db_trans('frontend_hall_booking_confirmation') }}</h1>
                <p>{{ db_trans('frontend_hall_booking_confirmation_help') }}</p>

                <div class="reference-box">
                    <span>{{ db_trans('frontend_hall_booking_reference') }}</span>
                    <strong>{{ $booking->booking_reference }}</strong>
                    <button type="button" class="copy-btn" data-copy-text="{{ $booking->booking_reference }}">
                        <i class="bi bi-copy"></i>{{ db_trans('copy') }}
                    </button>
                </div>

                <div class="confirmation-grid">
                    <div><span>{{ db_trans('halls') }}</span><strong>{{ $booking->hall->name }}</strong></div>
                    <div><span>{{ db_trans('frontend_hall_booking_date') }}</span><strong>{{ optional($booking->booking_date)->translatedFormat('d M Y') }}</strong></div>
                    <div><span>{{ db_trans('amount') }}</span><strong>TZS {{ number_format((float) $booking->price, 2) }}</strong></div>
                    <div><span>{{ db_trans('status') }}</span><strong>{{ $statusLabels[$booking->booking_status] ?? $booking->booking_status }}</strong></div>
                    <div><span>{{ db_trans('payment_status') }}</span><strong>{{ $paymentLabels[$booking->payment_status] ?? $booking->payment_status }}</strong></div>
                    <div><span>{{ db_trans('frontend_hall_customer_phone') }}</span><strong>{{ $booking->customer_phone }}</strong></div>
                </div>

                <div class="payment-box confirmation-payment">
                    <h4>{{ db_trans('frontend_hall_payment_details') }}</h4>
                    @if($booking->hall->bank_name || $booking->hall->bank_account_number || $booking->hall->bank_account_name)
                        <dl>
                            @if($booking->hall->bank_name)<div><dt>{{ db_trans('bank_name') }}</dt><dd>{{ $booking->hall->bank_name }}</dd></div>@endif
                            @if($booking->hall->bank_account_name)<div><dt>{{ db_trans('bank_account_name') }}</dt><dd>{{ $booking->hall->bank_account_name }}</dd></div>@endif
                            @if($booking->hall->bank_account_number)<div><dt>{{ db_trans('bank_account_number') }}</dt><dd>{{ $booking->hall->bank_account_number }}</dd></div>@endif
                        </dl>
                    @endif
                    <p>{{ $booking->hall->payment_instructions ?: db_trans('frontend_hall_payment_manual_help') }}</p>
                </div>

                <div class="hall-confirm-actions">
                    <a href="{{ route('frontend.hall-bookings.track', ['reference' => $booking->booking_reference, 'phone' => $booking->customer_phone]) }}" class="hall-public-btn hall-public-btn-primary">
                        <i class="bi bi-search"></i>{{ db_trans('frontend_hall_track_booking') }}
                    </a>
                    <a href="{{ route('frontend.hall-bookings.index') }}" class="hall-public-btn hall-public-btn-soft">
                        <i class="bi bi-building"></i>{{ db_trans('frontend_hall_back_to_halls') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('frontend-assets/js/hall-booking.js') }}"></script>
@endpush
