<x-guest-layout>
    <div class="auth-card-wrap">
        <div class="auth-page-intro">
            <span class="auth-page-chip auth-page-chip--rose">{{ db_trans('account_recovery') }}</span>
            <h2>{{ db_trans('reset_password') }}</h2>
            <p>{{ db_trans('enter_your_email_and_new_password') }}</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="auth-form-card">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="auth-field">
                <label for="email" class="auth-label">{{ db_trans('email') }}</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="name@example.com">
                @error('email')<div class="auth-error">{{ $message }}</div>@enderror
            </div>

            <div class="auth-grid-2">
                <div class="auth-field">
                    <label for="password" class="auth-label">{{ db_trans('password') }}</label>
                    <div class="auth-password-wrap">
                        <input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" placeholder="••••••••">
                        <button type="button" class="auth-toggle-password" data-toggle-password="#password">Show</button>
                    </div>
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

            <div class="auth-info-box">{{ db_trans('choose_a_strong_password_that_you_do_not_use_elsewhere') }}</div>

            <button type="submit" class="auth-btn auth-btn--primary">{{ db_trans('reset_password') }}</button>
        </form>
    </div>
</x-guest-layout>
