<x-guest-layout>
    <section class="auth-card-wrap">
        <div class="auth-page-intro">
            <span class="auth-page-chip auth-page-chip--indigo">{{ db_trans('password_reset') }}</span>
            <h2>{{ db_trans('enter_reset_code') }}</h2>
            <p>{{ db_trans('enter_the_code_sent_to_your_phone') }}</p>
        </div>

        <x-auth-session-status class="auth-status auth-status--success" :status="session('status')" />

        <form method="POST" action="{{ route('password.store.code') }}" class="auth-form-card">
            @csrf

            <div class="auth-field">
                <x-input-label for="phone" :value="db_trans('phone_number')" class="auth-label" />
                <x-text-input
                    id="phone"
                    class="auth-input"
                    type="text"
                    name="phone"
                    :value="old('phone', $phone ?? session('phone'))"
                    required
                    autofocus
                    autocomplete="tel"
                    placeholder="07XXXXXXXX"
                />
                <x-input-error :messages="$errors->get('phone')" class="auth-error" />
            </div>

            <div class="auth-field">
                <x-input-label for="code" :value="db_trans('reset_code')" class="auth-label" />
                <x-text-input
                    id="code"
                    class="auth-input"
                    type="text"
                    name="code"
                    :value="old('code')"
                    required
                    inputmode="numeric"
                    maxlength="6"
                    placeholder="123456"
                />
                <x-input-error :messages="$errors->get('code')" class="auth-error" />
            </div>

            <div class="auth-field">
                <x-input-label for="password" :value="db_trans('new_password')" class="auth-label" />
                <div class="auth-password-wrap">
                    <x-text-input id="password" class="auth-input auth-input--password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
                    <button type="button" class="auth-toggle-password" data-toggle-password="#password">{{ db_trans('show') }}</button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="auth-error" />
            </div>

            <div class="auth-field">
                <x-input-label for="password_confirmation" :value="db_trans('confirm_password')" class="auth-label" />
                <div class="auth-password-wrap">
                    <x-text-input id="password_confirmation" class="auth-input auth-input--password" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                    <button type="button" class="auth-toggle-password" data-toggle-password="#password_confirmation">{{ db_trans('show') }}</button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
            </div>

            <x-primary-button class="auth-btn auth-btn--primary">{{ db_trans('reset_password') }}</x-primary-button>

            <div class="auth-form-meta">
                <a class="auth-link" href="{{ route('login') }}">{{ db_trans('back_to_login') }}</a>
            </div>
        </form>
    </section>
</x-guest-layout>