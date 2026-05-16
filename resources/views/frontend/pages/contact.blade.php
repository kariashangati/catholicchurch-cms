@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend-assets/css/contact.css') }}">
<link rel="stylesheet" href="{{ asset('frontend-assets/css/contact-sermon-request.css') }}">
@endpush

@php
    $pageKicker = db_trans('contact.page_kicker');
    $pageTitle = db_trans('contact.page_title');
    $pageSubtitle = db_trans('contact.page_subtitle');

    $cardCall = db_trans('contact.cards.call');
    $cardEmail = db_trans('contact.cards.email');
    $cardLocation = db_trans('contact.cards.location');
    $cardHours = db_trans('contact.cards.hours');

    $formTitle = db_trans('contact.form.title');
    $labelFullName = db_trans('contact.form.full_name');
    $labelPhone = db_trans('contact.form.phone');
    $labelEmail = db_trans('contact.form.email');
    $labelReason = db_trans('contact.form.reason');
    $labelKanda = db_trans('contact.form.kanda');
    $labelJumuiya = db_trans('contact.form.jumuiya');
    $labelMessage = db_trans('contact.form.message');

    $placeholderSelectReason = db_trans('contact.form.select_reason');
    $placeholderSelectKanda = db_trans('contact.form.select_kanda');
    $placeholderSelectJumuiya = db_trans('contact.form.select_jumuiya');

    $submitButton = db_trans('contact.form.submit');
    $mapUnavailable = db_trans('contact.map.unavailable');

    $alertSuccessTitle = db_trans('contact.alert.success_title');
    $alertErrorTitle = db_trans('contact.alert.error_title');
    $alertErrorText = db_trans('contact.alert.error_text');

    $pageKicker = ($pageKicker && $pageKicker !== 'contact.page_kicker') ? $pageKicker : 'Get in Touch';
    $pageTitle = ($pageTitle && $pageTitle !== 'contact.page_title') ? $pageTitle : 'Contact Us';
    $pageSubtitle = ($pageSubtitle && $pageSubtitle !== 'contact.page_subtitle')
        ? $pageSubtitle
        : 'We are here to listen, support, and connect with you. Reach out anytime.';

    $cardCall = ($cardCall && $cardCall !== 'contact.cards.call') ? $cardCall : 'Call Us';
    $cardEmail = ($cardEmail && $cardEmail !== 'contact.cards.email') ? $cardEmail : 'Email';
    $cardLocation = ($cardLocation && $cardLocation !== 'contact.cards.location') ? $cardLocation : 'Location';
    $cardHours = ($cardHours && $cardHours !== 'contact.cards.hours') ? $cardHours : 'Working Hours';

    $formTitle = ($formTitle && $formTitle !== 'contact.form.title') ? $formTitle : 'Send us a Message';
    $labelFullName = ($labelFullName && $labelFullName !== 'contact.form.full_name') ? $labelFullName : 'Full Name *';
    $labelPhone = ($labelPhone && $labelPhone !== 'contact.form.phone') ? $labelPhone : 'Phone';
    $labelEmail = ($labelEmail && $labelEmail !== 'contact.form.email') ? $labelEmail : 'Email';
    $labelReason = ($labelReason && $labelReason !== 'contact.form.reason') ? $labelReason : 'Reason *';
    $labelKanda = ($labelKanda && $labelKanda !== 'contact.form.kanda') ? $labelKanda : 'Kanda (Zone)';
    $labelJumuiya = ($labelJumuiya && $labelJumuiya !== 'contact.form.jumuiya') ? $labelJumuiya : 'Jumuiya (Community)';
    $labelMessage = ($labelMessage && $labelMessage !== 'contact.form.message') ? $labelMessage : 'Message *';

    $placeholderSelectReason = ($placeholderSelectReason && $placeholderSelectReason !== 'contact.form.select_reason') ? $placeholderSelectReason : 'Select reason';
    $placeholderSelectKanda = ($placeholderSelectKanda && $placeholderSelectKanda !== 'contact.form.select_kanda') ? $placeholderSelectKanda : 'Select Kanda';
    $placeholderSelectJumuiya = ($placeholderSelectJumuiya && $placeholderSelectJumuiya !== 'contact.form.select_jumuiya') ? $placeholderSelectJumuiya : 'Select Jumuiya';

    $submitButton = ($submitButton && $submitButton !== 'contact.form.submit') ? $submitButton : 'Send Message';
    $mapUnavailable = ($mapUnavailable && $mapUnavailable !== 'contact.map.unavailable') ? $mapUnavailable : 'Map not available';

    $alertSuccessTitle = ($alertSuccessTitle && $alertSuccessTitle !== 'contact.alert.success_title') ? $alertSuccessTitle : 'Success';
    $alertErrorTitle = ($alertErrorTitle && $alertErrorTitle !== 'contact.alert.error_title') ? $alertErrorTitle : 'Error';
    $alertErrorText = ($alertErrorText && $alertErrorText !== 'contact.alert.error_text') ? $alertErrorText : 'Something went wrong. Please try again.';

    $sermonButton = db_trans('frontend_sermon_request_button');
    $sermonTitle = db_trans('frontend_sermon_request_title');
    $sermonHint = db_trans('frontend_sermon_request_hint');
    $sermonTopic = db_trans('frontend_sermon_request_topic');
    $sermonResponseType = db_trans('frontend_sermon_request_response_type');
    $sermonSubmit = db_trans('frontend_sermon_request_submit');
    $sermonCancel = db_trans('frontend_sermon_request_cancel');
    $normalMessageLabel = db_trans('contact_request_normal_message');

    $sermonButton = ($sermonButton && $sermonButton !== 'frontend_sermon_request_button') ? $sermonButton : 'Request Personal Sermon';
    $sermonTitle = ($sermonTitle && $sermonTitle !== 'frontend_sermon_request_title') ? $sermonTitle : 'Request a Personal Sermon';
    $sermonHint = ($sermonHint && $sermonHint !== 'frontend_sermon_request_hint') ? $sermonHint : 'Tell us the topic and your phone number. The parish team will prepare and respond.';
    $sermonTopic = ($sermonTopic && $sermonTopic !== 'frontend_sermon_request_topic') ? $sermonTopic : 'Sermon Topic *';
    $sermonResponseType = ($sermonResponseType && $sermonResponseType !== 'frontend_sermon_request_response_type') ? $sermonResponseType : 'Preferred response';
    $sermonSubmit = ($sermonSubmit && $sermonSubmit !== 'frontend_sermon_request_submit') ? $sermonSubmit : 'Submit Request';
    $sermonCancel = ($sermonCancel && $sermonCancel !== 'frontend_sermon_request_cancel') ? $sermonCancel : 'Back to Message';
    $normalMessageLabel = ($normalMessageLabel && $normalMessageLabel !== 'contact_request_normal_message') ? $normalMessageLabel : 'Normal Message';
@endphp

@section('content')

<div class="contact-page">

    <section class="contact-hero">
        <div class="home-shell">
            <div class="contact-hero-inner">
                <span class="contact-kicker">
                    <i class="bi bi-envelope-paper"></i>
                    {{ $pageKicker }}
                </span>
                <h1>{{ $pageTitle }}</h1>
                <p>{{ $pageSubtitle }}</p>
            </div>
        </div>
    </section>

    <section class="contact-cards">
        <div class="home-shell">
            <div class="cards-grid">
                @if($contactPhone)
                    <div class="contact-card"><i class="bi bi-telephone"></i><h4>{{ $cardCall }}</h4><p>{{ $contactPhone }}</p></div>
                @endif
                @if($contactEmail)
                    <div class="contact-card"><i class="bi bi-envelope"></i><h4>{{ $cardEmail }}</h4><p>{{ $contactEmail }}</p></div>
                @endif
                @if($contactAddress)
                    <div class="contact-card"><i class="bi bi-geo-alt"></i><h4>{{ $cardLocation }}</h4><p>{{ $contactAddress }}</p></div>
                @endif
                @if($workingHours)
                    <div class="contact-card"><i class="bi bi-clock"></i><h4>{{ $cardHours }}</h4><p>{!! nl2br(e($workingHours)) !!}</p></div>
                @endif
            </div>
        </div>
    </section>

    <section class="contact-main">
        <div class="home-shell">
            <div class="contact-grid">

                <div class="contact-form-box contact-form-box-enhanced">
                    <div class="contact-form-headline">
                        <div>
                            <h3 id="contactFormHeading">{{ $formTitle }}</h3>
                            <p id="contactFormHelp" class="contact-form-help">{{ $sermonHint }}</p>
                        </div>
                        <div class="contact-action-switch" role="group" aria-label="{{ $formTitle }}">
                            <button type="button" class="contact-switch-btn is-active" id="showContactMessageBtn">
                                <i class="bi bi-chat-left-text"></i>
                                <span>{{ $normalMessageLabel }}</span>
                            </button>
                            <button type="button" class="contact-switch-btn" id="showSermonRequestBtn">
                                <i class="bi bi-book-half"></i>
                                <span>{{ $sermonButton }}</span>
                            </button>
                        </div>
                    </div>

                    <form id="contactForm" class="contact-live-form">
                        @csrf
                        <div class="form-group"><label>{{ $labelFullName }}</label><input type="text" name="full_name" required></div>
                        <div class="form-group"><label>{{ $labelPhone }}</label><input type="text" name="phone"></div>
                        <div class="form-group"><label>{{ $labelEmail }}</label><input type="email" name="email"></div>
                        <div class="form-group">
                            <label>{{ $labelReason }}</label>
                            <select name="contact_reason_id" required>
                                <option value="">{{ $placeholderSelectReason }}</option>
                                @foreach($reasons as $reason)
                                    <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ $labelKanda }}</label>
                            <select name="kanda_id" id="kandaSelect">
                                <option value="">{{ $placeholderSelectKanda }}</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}">{{ $kanda->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ $labelJumuiya }}</label>
                            <select name="jumuiya_id" id="jumuiyaSelect">
                                <option value="">{{ $placeholderSelectJumuiya }}</option>
                                @foreach($jumuiyas as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" data-kanda="{{ $jumuiya->kanda_id }}">{{ $jumuiya->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>{{ $labelMessage }}</label><textarea name="message" rows="5" required></textarea></div>
                        <button type="submit" class="btn-primary">{{ $submitButton }}</button>
                    </form>

                    <form id="sermonRequestForm"
                          class="contact-live-form sermon-request-form d-none"
                          action="{{ Route::has('frontend.sermon-requests.submit') ? route('frontend.sermon-requests.submit') : url('/sermon-requests/submit') }}">
                        @csrf
                        <div class="sermon-request-banner">
                            <span><i class="bi bi-book-half"></i></span>
                            <div>
                                <strong>{{ $sermonTitle }}</strong>
                                <small>{{ $sermonHint }}</small>
                            </div>
                        </div>

                        <div class="form-group"><label>{{ $labelFullName }}</label><input type="text" name="name" required></div>
                        <div class="form-group"><label>{{ $labelPhone }} *</label><input type="text" name="phone" required></div>
                        <div class="form-group"><label>{{ $labelEmail }}</label><input type="email" name="email"></div>

                        <div class="form-group">
                            <label>{{ $labelKanda }}</label>
                            <select id="sermonKandaSelect">
                                <option value="">{{ $placeholderSelectKanda }}</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}">{{ $kanda->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>{{ $labelJumuiya }}</label>
                            <select name="jumuiya_id" id="sermonJumuiyaSelect">
                                <option value="">{{ $placeholderSelectJumuiya }}</option>
                                @foreach($jumuiyas as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" data-kanda="{{ $jumuiya->kanda_id }}">{{ $jumuiya->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group"><label>{{ $sermonTopic }}</label><input type="text" name="topic" required></div>

                        <div class="form-group">
                            <label>{{ $sermonResponseType }}</label>
                            <select name="preferred_response_type">
                                <option value="any">{{ db_trans('frontend_sermon_request_any') }}</option>
                                <option value="text">{{ db_trans('frontend_sermon_request_text') }}</option>
                                <option value="video">{{ db_trans('frontend_sermon_request_video') }}</option>
                                <option value="call">{{ db_trans('frontend_sermon_request_call') }}</option>
                            </select>
                        </div>

                        <div class="form-group"><label>{{ $labelMessage }}</label><textarea name="message" rows="5"></textarea></div>

                        <div class="sermon-request-actions">
                            <button type="button" class="btn-sermon-secondary" id="cancelSermonRequestBtn">{{ $sermonCancel }}</button>
                            <button type="submit" class="btn-primary">{{ $sermonSubmit }}</button>
                        </div>
                    </form>
                </div>

                <div class="contact-map">
                    @if($mapEmbed)
                        {!! $mapEmbed !!}
                    @else
                        <div class="map-placeholder">{{ $mapUnavailable }}</div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function filterJumuiya(kandaSelect, jumuiyaSelect) {
        if (!kandaSelect || !jumuiyaSelect) return;
        const selected = kandaSelect.value;
        [...jumuiyaSelect.options].forEach(option => {
            if (!option.value) return;
            option.style.display = (!selected || option.dataset.kanda === selected) ? 'block' : 'none';
        });
        jumuiyaSelect.value = '';
    }

    const kandaSelect = document.getElementById('kandaSelect');
    const jumuiyaSelect = document.getElementById('jumuiyaSelect');
    const sermonKandaSelect = document.getElementById('sermonKandaSelect');
    const sermonJumuiyaSelect = document.getElementById('sermonJumuiyaSelect');

    if (kandaSelect) kandaSelect.addEventListener('change', function () { filterJumuiya(kandaSelect, jumuiyaSelect); });
    if (sermonKandaSelect) sermonKandaSelect.addEventListener('change', function () { filterJumuiya(sermonKandaSelect, sermonJumuiyaSelect); });

    const contactForm = document.getElementById('contactForm');
    const sermonRequestForm = document.getElementById('sermonRequestForm');
    const showContactMessageBtn = document.getElementById('showContactMessageBtn');
    const showSermonRequestBtn = document.getElementById('showSermonRequestBtn');
    const cancelSermonRequestBtn = document.getElementById('cancelSermonRequestBtn');
    const contactFormHeading = document.getElementById('contactFormHeading');

    function showContactForm() {
        contactForm?.classList.remove('d-none');
        sermonRequestForm?.classList.add('d-none');
        showContactMessageBtn?.classList.add('is-active');
        showSermonRequestBtn?.classList.remove('is-active');
        if (contactFormHeading) contactFormHeading.textContent = @json($formTitle);
    }

    function showSermonForm() {
        contactForm?.classList.add('d-none');
        sermonRequestForm?.classList.remove('d-none');
        showContactMessageBtn?.classList.remove('is-active');
        showSermonRequestBtn?.classList.add('is-active');
        if (contactFormHeading) contactFormHeading.textContent = @json($sermonTitle);
    }

    showContactMessageBtn?.addEventListener('click', showContactForm);
    showSermonRequestBtn?.addEventListener('click', showSermonForm);
    cancelSermonRequestBtn?.addEventListener('click', showContactForm);

    contactForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(contactForm);
        fetch("{{ route('frontend.contact.submit') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': contactForm.querySelector('input[name=_token]').value, 'Accept': 'application/json' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) throw new Error();
            Swal.fire({ icon: 'success', title: @json($alertSuccessTitle), text: data.message });
            contactForm.reset();
        })
        .catch(() => Swal.fire({ icon: 'error', title: @json($alertErrorTitle), text: @json($alertErrorText) }));
    });

    sermonRequestForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(sermonRequestForm);
        fetch(sermonRequestForm.getAttribute('action'), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': sermonRequestForm.querySelector('input[name=_token]').value, 'Accept': 'application/json' },
            body: formData
        })
        .then(async res => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.success) throw new Error(data.message || 'Failed');
            return data;
        })
        .then(data => {
            const reference = data.reference ? ('\n' + @json(db_trans('frontend_sermon_request_reference')) + ': ' + data.reference) : '';
            Swal.fire({ icon: 'success', title: @json($alertSuccessTitle), text: (data.message || @json(db_trans('frontend_sermon_request_received'))) + reference });
            sermonRequestForm.reset();
            showContactForm();
        })
        .catch(() => Swal.fire({ icon: 'error', title: @json($alertErrorTitle), text: @json($alertErrorText) }));
    });
});
</script>
@endpush
