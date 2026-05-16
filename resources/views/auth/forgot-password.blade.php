<x-guest-layout>
    <section class="auth-card-wrap">
        <div class="auth-page-intro">
            <span class="auth-page-chip auth-page-chip--indigo">{{ db_trans('password_reset') }}</span>
            <h2>{{ db_trans('forgot_password') }}</h2>
            <p>{{ db_trans('enter_phone_to_receive_reset_code') }}</p>
        </div>

        <x-auth-session-status class="auth-status auth-status--success" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="auth-form-card">
            @csrf

            <div class="auth-field">
                <x-input-label for="phone" :value="db_trans('phone_number')" class="auth-label" />
                <x-text-input
                    id="phone"
                    class="auth-input"
                    type="text"
                    name="phone"
                    :value="old('phone')"
                    required
                    autofocus
                    autocomplete="tel"
                    placeholder="07XXXXXXXX"
                />
                <x-input-error :messages="$errors->get('phone')" class="auth-error" />
            </div>

            <x-primary-button class="auth-btn auth-btn--primary">{{ db_trans('send_reset_code') }}</x-primary-button>

            <div class="auth-form-meta">
                <a class="auth-link" href="{{ route('login') }}">{{ db_trans('back_to_login') }}</a>
            </div>
        </form>
    </section>
</x-guest-layout>