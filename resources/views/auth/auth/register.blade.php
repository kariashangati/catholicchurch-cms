<x-guest-layout>
    <section class="auth-card-wrap">
        <div class="auth-page-intro">
            <span class="auth-page-chip auth-page-chip--green">{{ db_trans('create_account') }}</span>
            <h2>{{ db_trans('register') }}</h2>
            <p>{{ db_trans('create_a_new_account_to_get_started') }}</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form-card">
            @csrf

            <div class="auth-field">
                <x-input-label for="name" :value="db_trans('name')" class="auth-label" />
                <x-text-input id="name" class="auth-input" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="{{ db_trans('full_name') }}" />
                <x-input-error :messages="$errors->get('name')" class="auth-error" />
            </div>

            <div class="auth-field">
                <x-input-label for="email" :value="db_trans('email')" class="auth-label" />
                <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="name@example.com" />
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div class="auth-grid-2">
                <div class="auth-field">
                    <x-input-label for="password" :value="db_trans('password')" class="auth-label" />
                    <div class="auth-password-wrap">
                        <x-text-input id="password" class="auth-input auth-input--password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
                        <button type="button" class="auth-toggle-password" data-toggle-password="#password">Show</button>
                    </div>
                    <div class="auth-help">{{ db_trans('use_a_strong_password_for_secure_access') }}</div>
                    <x-input-error :messages="$errors->get('password')" class="auth-error" />
                </div>

                <div class="auth-field">
                    <x-input-label for="password_confirmation" :value="db_trans('confirm_password')" class="auth-label" />
                    <div class="auth-password-wrap">
                        <x-text-input id="password_confirmation" class="auth-input auth-input--password" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                        <button type="button" class="auth-toggle-password" data-toggle-password="#password_confirmation">Show</button>
                    </div>
                    <div class="auth-help">{{ db_trans('re_enter_password_to_confirm') }}</div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
                </div>
            </div>

            <div class="auth-form-actions">
                <a class="auth-link" href="{{ route('login') }}">{{ db_trans('already_registered') }}</a>
                <x-primary-button class="auth-btn auth-btn--primary auth-btn--auto">{{ db_trans('register') }}</x-primary-button>
            </div>
        </form>
    </section>
</x-guest-layout>
