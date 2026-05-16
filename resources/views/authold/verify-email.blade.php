<x-guest-layout>
    <div class="mx-auto w-full max-w-md">
        <div class="mb-6">
            <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ db_trans('verify_email') }}</h1>
            <p class="mt-2 text-sm text-slate-500">
                {{ db_trans('thanks_for_signing_up_before_getting_started_verify_your_email_address') }}
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ db_trans('a_new_verification_link_has_been_sent_to_the_email_address_you_provided_during_registration') }}
            </div>
        @endif

        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-primary-button class="rounded-2xl px-5 py-3">
                    {{ db_trans('resend_verification_email') }}
                </x-primary-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-medium text-slate-600 underline hover:text-slate-900">
                    {{ db_trans('logout') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>