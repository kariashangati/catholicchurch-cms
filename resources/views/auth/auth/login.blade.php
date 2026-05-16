<x-guest-layout>
    <section class="auth-card-wrap">
        <div class="auth-page-intro">
            <span class="auth-page-chip auth-page-chip--indigo">{{ db_trans('secure_login') }}</span>
            <h2>{{ db_trans('login') }}</h2>
            <p>{{ db_trans('sign_in_to_continue_to_your_dashboard') }}</p>
        </div>

        <x-auth-session-status class="auth-status auth-status--success" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="auth-form-card">
            @csrf

            <div class="auth-field">
                <x-input-label for="email" :value="db_trans('email')" class="auth-label" />
                <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@example.com" />
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div class="auth-field">
                <div class="auth-field-head">
                    <x-input-label for="password" :value="db_trans('password')" class="auth-label" />
                    @if (Route::has('password.request'))
                        <a class="auth-link" href="{{ route('password.request') }}">{{ db_trans('forgot_password') }}</a>
                    @endif
                </div>
                <div class="auth-password-wrap">
                    <x-text-input id="password" class="auth-input auth-input--password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                    <button type="button" class="auth-toggle-password" data-toggle-password="#password">Show</button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="auth-error" />
            </div>

            <div class="auth-form-meta">
                <label for="remember_me" class="auth-checkbox">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span>{{ db_trans('remember_me') }}</span>
                </label>
                <span class="auth-form-note">{{ db_trans('your_session_is_protected') }}</span>
            </div>

            <x-primary-button class="auth-btn auth-btn--primary">{{ db_trans('log_in') }}</x-primary-button>
        </form>
    </section>
</x-guest-layout>
