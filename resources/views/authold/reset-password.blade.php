<x-guest-layout>
    <div class="mx-auto w-full max-w-md">
        <div class="mb-6">
            <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ db_trans('reset_password') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ db_trans('enter_your_email_and_new_password') }}</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <x-input-label for="email" :value="db_trans('email')" />
                <x-text-input id="email" class="mt-1 block w-full rounded-2xl" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
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

            <x-primary-button class="w-full justify-center rounded-2xl py-3">
                {{ db_trans('reset_password') }}
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>