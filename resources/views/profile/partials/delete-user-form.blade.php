<section>
    <header class="mb-4">
        <h2 class="h5 font-weight-bold text-danger">
            {{ db_trans('delete_account') }}
        </h2>

        <p class="mb-0 text-muted">
            {{ db_trans('once_your_account_is_deleted_all_of_its_resources_and_data_will_be_permanently_deleted') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="btn btn-danger"
    >
        {{ db_trans('delete_account') }}
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-4">
            @csrf
            @method('delete')

            <h2 class="h5 font-weight-bold text-gray-800">
                {{ db_trans('are_you_sure_you_want_to_delete_your_account') }}
            </h2>

            <p class="mt-2 text-muted">
                {{ db_trans('please_enter_your_password_to_confirm_you_would_like_to_permanently_delete_your_account') }}
            </p>

            <div class="form-group mt-4">
                <label for="password" class="sr-only">{{ db_trans('password') }}</label>

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="form-control"
                    :placeholder="db_trans('password')"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="d-flex justify-content-end" style="gap: 10px;">
                <x-secondary-button x-on:click="$dispatch('close')" class="btn btn-secondary">
                    {{ db_trans('cancel') }}
                </x-secondary-button>

                <x-danger-button class="btn btn-danger">
                    {{ db_trans('delete_account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>