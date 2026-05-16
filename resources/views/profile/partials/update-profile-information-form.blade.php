<section>
    <header>
        <h2 class="text-lg font-bold text-slate-900">
            {{ db_trans('profile_information') }}
        </h2>

        <p class="mt-1 text-sm text-slate-600">
            {{ db_trans('update_your_profile_information_and_email_address') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="db_trans('name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full rounded-2xl" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="db_trans('email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-2xl" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-slate-700">
                        {{ db_trans('your_email_address_is_unverified') }}

                        <button form="send-verification"
                                class="rounded-md text-sm font-medium text-indigo-600 underline hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ db_trans('click_here_to_resend_verification_email') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-600">
                            {{ db_trans('a_new_verification_link_has_been_sent_to_your_email_address') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ db_trans('save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-slate-600"
                >{{ db_trans('saved') }}</p>
            @endif
        </div>
    </form>
</section>