<x-guest-layout>
    <div class="mx-auto w-full max-w-md">
        <div class="mb-6">
            <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ db_trans('forgot_password') }}</h1>
            <p class="mt-2 text-sm text-slate-500">
                {{ db_trans('forgot_your_password_no_problem_just_let_us_know_your_email_address') }}
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="db_trans('email')" />
                <x-text-input id="email" class="mt-1 block w-full rounded-2xl" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center rounded-2xl py-3">
                {{ db_trans('email_password_reset_link') }}
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>