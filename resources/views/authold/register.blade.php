<x-guest-layout>
    <div class="mx-auto w-full max-w-md">
        <div class="mb-8">
            <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ db_trans('register') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ db_trans('create_a_new_account_to_get_started') }}</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="name" :value="db_trans('name')" />
                <x-text-input id="name" class="mt-1 block w-full rounded-2xl" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="db_trans('email')" />
                <x-text-input id="email" class="mt-1 block w-full rounded-2xl" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="db_trans('password')" />
                <x-text-input id="password" class="mt-1 block w-full rounded-2xl" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="db_trans('confirm_password')" />
                <x-text-input id="password_confirmation" class="mt-1 block w-full rounded-2xl" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4 pt-2">
                <a class="text-sm font-medium text-indigo-600 hover:text-indigo-700" href="{{ route('login') }}">
                    {{ db_trans('already_registered') }}
                </a>

                <x-primary-button class="rounded-2xl px-6 py-3">
                    {{ db_trans('register') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>