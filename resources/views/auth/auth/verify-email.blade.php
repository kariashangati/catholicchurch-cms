<x-guest-layout>
    <section class="auth-card-wrap">
        <div class="auth-page-intro">
            <span class="auth-page-chip auth-page-chip--sky">{{ db_trans('email_verification') }}</span>
            <h2>{{ db_trans('verify_email') }}</h2>
            <p>{{ db_trans('thanks_for_signing_up_before_getting_started_verify_your_email_address') }}</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="auth-status auth-status--success">{{ db_trans('a_new_verification_link_has_been_sent_to_the_email_address_you_provided_during_registration') }}</div>
        @endif

        <div class="auth-form-card">
            <div class="auth-info-box">{{ db_trans('check_your_inbox_and_click_the_verification_link_to_continue') }}</div>
            <div class="auth-form-actions auth-form-actions--stack">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-primary-button class="auth-btn auth-btn--primary auth-btn--auto">{{ db_trans('resend_verification_email') }}</x-primary-button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="auth-link auth-link--button">{{ db_trans('logout') }}</button>
                </form>
            </div>
        </div>
    </section>
</x-guest-layout>
