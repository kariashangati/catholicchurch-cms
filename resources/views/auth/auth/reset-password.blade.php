<x-guest-layout>
    <section class="auth-card-wrap">
        <div class="auth-page-intro">
            <span class="auth-page-chip auth-page-chip--violet">{{ db_trans('set_new_password') }}</span>
            <h2>{{ db_trans('reset_password') }}</h2>
            <p>{{ db_trans('enter_your_email_and_new_password') }}</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="auth-form-card">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="auth-field">
                <x-input-label for="email" :value="db_trans('email')" class="auth-label" />
                <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email', $request->email)" required autofocus placeholder="name@example.com" />
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div class="auth-grid-2">
                <div class="auth-field">
                    <x-input-label for="password" :value="db_trans('password')" class="auth-label" />
                    <div class="auth-password-wrap">
                        <x-text-input id="password" class="auth-input auth-input--password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
                        <button type="button" class="auth-toggle-password" data-toggle-password="#password">Show</button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="auth-error" />
                </div>
                <div class="auth-field">
                    <x-input-label for="password_confirmation" :value="db_trans('confirm_password')" class="auth-label" />
                    <div class="auth-password-wrap">
                        <x-text-input id="password_confirmation" class="auth-input auth-input--password" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                        <button type="button" class="auth-toggle-password" data-toggle-password="#password_confirmation">Show</button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
                </div>
            </div>

            <div class="auth-info-box">{{ db_trans('choose_a_password_that_is_easy_for_you_to_remember_but_hard_to_guess') }}</div>

            <x-primary-button class="auth-btn auth-btn--primary">{{ db_trans('reset_password') }}</x-primary-button>
        </form>
    </section>
</x-guest-layout>
