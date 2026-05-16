<x-guest-layout>
    <section class="auth-card-wrap">
        <div class="auth-page-intro">
            <span class="auth-page-chip auth-page-chip--amber">{{ db_trans('password_help') }}</span>
            <h2>{{ db_trans('forgot_password') }}</h2>
            <p>{{ db_trans('forgot_your_password_no_problem_just_let_us_know_your_email_address') }}</p>
        </div>

        <x-auth-session-status class="auth-status auth-status--success" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="auth-form-card">
            @csrf
            <div class="auth-field">
                <x-input-label for="email" :value="db_trans('email')" class="auth-label" />
                <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email')" required autofocus placeholder="name@example.com" />
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div class="auth-info-box">{{ db_trans('we_will_send_a_secure_reset_link_to_your_email') }}</div>

            <x-primary-button class="auth-btn auth-btn--primary">{{ db_trans('email_password_reset_link') }}</x-primary-button>
        </form>
    </section>
</x-guest-layout>
