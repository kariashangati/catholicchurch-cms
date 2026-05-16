<x-guest-layout>
    <div class="auth-card-wrap">
        <div class="auth-page-intro">
            <span class="auth-page-chip auth-page-chip--sky">{{ db_trans('secure_area') }}</span>
            <h2>{{ db_trans('confirm_password') }}</h2>
            <p>{{ db_trans('this_is_a_secure_area_of_the_application_please_confirm_your_password_before_continuing') }}</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="auth-form-card">
            @csrf

            <div class="auth-field">
                <label for="password" class="auth-label">{{ db_trans('password') }}</label>
                <div class="auth-password-wrap">
                    <input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                    <button type="button" class="auth-toggle-password" data-toggle-password="#password">Show</button>
                </div>
                @error('password')<div class="auth-error">{{ $message }}</div>@enderror
            </div>

            <div class="auth-info-box">{{ db_trans('confirming_your_password_helps_protect_sensitive_actions') }}</div>

            <button type="submit" class="auth-btn auth-btn--primary">{{ db_trans('confirm') }}</button>
        </form>
    </div>
</x-guest-layout>
