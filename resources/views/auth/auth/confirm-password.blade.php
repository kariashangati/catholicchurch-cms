<x-guest-layout>
    <section class="auth-card-wrap">
        <div class="auth-page-intro">
            <span class="auth-page-chip auth-page-chip--rose">{{ db_trans('secure_area') }}</span>
            <h2>{{ db_trans('confirm_password') }}</h2>
            <p>{{ db_trans('this_is_a_secure_area_of_the_application_please_confirm_your_password_before_continuing') }}</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="auth-form-card">
            @csrf
            <div class="auth-field">
                <x-input-label for="password" :value="db_trans('password')" class="auth-label" />
                <div class="auth-password-wrap">
                    <x-text-input id="password" class="auth-input auth-input--password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                    <button type="button" class="auth-toggle-password" data-toggle-password="#password">Show</button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="auth-error" />
            </div>

            <div class="auth-info-box">{{ db_trans('this_step_helps_keep_sensitive_information_safe') }}</div>
            <x-primary-button class="auth-btn auth-btn--primary">{{ db_trans('confirm') }}</x-primary-button>
        </form>
    </section>
</x-guest-layout>
