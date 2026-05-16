<x-guest-layout>
    <div class="mx-auto w-full max-w-md">
        <div class="mb-8">
            <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ db_trans('login') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ db_trans('sign_in_to_continue_to_your_dashboard') }}</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="db_trans('email')" />
                <x-text-input id="email" class="mt-1 block w-full rounded-2xl" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="db_trans('password')" />
                <x-text-input id="password" class="mt-1 block w-full rounded-2xl" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <label for="remember_me" class="inline-flex items-center gap-2">
                    <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="text-sm text-slate-600">{{ db_trans('remember_me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-indigo-600 hover:text-indigo-700" href="{{ route('password.request') }}">
                        {{ db_trans('forgot_password') }}
                    </a>
                @endif
            </div>

            <x-primary-button class="w-full justify-center rounded-2xl py-3">
                {{ db_trans('log_in') }}
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>