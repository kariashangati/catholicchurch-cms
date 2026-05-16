<x-guest-layout>
    <div class="auth-card-wrap">
        <div class="auth-page-intro">
            <span class="auth-page-chip auth-page-chip--violet">{{ db_trans('create_account') }}</span>
            <h2>{{ db_trans('register') }}</h2>
            <p>{{ db_trans('create_a_new_account_to_get_started') }}</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form-card">
            @csrf

            <div class="auth-field">
                <label for="name" class="auth-label">{{ db_trans('name') }}</label>
                <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="{{ db_trans('full_name') }}">
                @error('name')<div class="auth-error">{{ $message }}</div>@enderror
            </div>

            <div class="auth-field">
                <label for="email" class="auth-label">{{ db_trans('email') }}</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="name@example.com">
                @error('email')<div class="auth-error">{{ $message }}</div>@enderror
            </div>

            <div class="auth-grid-2">
                <div class="auth-field">
                    <label for="password" class="auth-label">{{ db_trans('password') }}</label>
                    <div class="auth-password-wrap">
                        <input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" placeholder="••••••••">
                        <button type="button" class="auth-toggle-password" data-toggle-password="#password">Show</button>
                    </div>
                    <div class="auth-help">{{ db_trans('use_at_least_8_characters_for_better_security') }}</div>
                    @error('password')<div class="auth-error">{{ $message }}</div>@enderror
                </div>

                <div class="auth-field">
                    <label for="password_confirmation" class="auth-label">{{ db_trans('confirm_password') }}</label>
                    <div class="auth-password-wrap">
                        <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
                        <button type="button" class="auth-toggle-password" data-toggle-password="#password_confirmation">Show</button>
                    </div>
                    @error('password_confirmation')<div class="auth-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="auth-info-box">{{ db_trans('your_account_will_use_role_based_access_after_registration') }}</div>

            <div class="auth-form-actions">
                <a class="auth-link" href="{{ route('login') }}">{{ db_trans('already_registered') }}</a>
                <button type="submit" class="auth-btn auth-btn--primary auth-btn--auto">{{ db_trans('register') }}</button>
            </div>
        </form>
    </div>
</x-guest-layout>
