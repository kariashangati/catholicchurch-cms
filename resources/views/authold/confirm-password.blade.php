<x-guest-layout>
    <div class="mx-auto w-full max-w-md">
        <div class="mb-6">
            <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ db_trans('confirm_password') }}</h1>
            <p class="mt-2 text-sm text-slate-500">
                {{ db_trans('this_is_a_secure_area_of_the_application_please_confirm_your_password_before_continuing') }}
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="password" :value="db_trans('password')" />
                <x-text-input id="password"
                              class="mt-1 block w-full rounded-2xl"
                              type="password"
                              name="password"
                              required
                              autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center rounded-2xl py-3">
                {{ db_trans('confirm') }}
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>