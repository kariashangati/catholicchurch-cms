<section>
    <header class="mb-4">
        <h2 class="h5 font-weight-bold text-gray-800">
            {{ db_trans('update_password') }}
        </h2>

        <p class="mb-0 text-muted">
            {{ db_trans('ensure_your_account_uses_a_secure_password') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="form-group">
            <label for="update_password_current_password" class="font-weight-semibold">{{ db_trans('current_password') }}</label>
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="form-group">
            <label for="update_password_password" class="font-weight-semibold">{{ db_trans('new_password') }}</label>
            <x-text-input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div class="form-group">
            <label for="update_password_password_confirmation" class="font-weight-semibold">{{ db_trans('confirm_password') }}</label>
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="d-flex align-items-center" style="gap: 12px;">
            <x-primary-button class="btn btn-primary">
                {{ db_trans('save') }}
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p class="small text-muted mb-0">{{ db_trans('saved') }}</p>
            @endif
        </div>
    </form>
</section>