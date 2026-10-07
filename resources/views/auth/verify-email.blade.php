<x-guest-layout>
    <x-slot name="heading">{{ __('Verify your email') }}</x-slot>
    <x-slot name="subheading">{{ __('Thanks for signing up! Click the link we emailed you to get started. Didn\'t get it? We can send another.') }}</x-slot>

    <div class="flex justify-center">
        <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-rx-accent/15 text-blue-400">
            <x-icon name="envelope" class="h-8 w-8" />
        </span>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-5 rounded-xl border border-rx-accent/30 bg-rx-accent/10 px-4 py-3 text-sm font-medium text-blue-300">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
        @csrf
        <x-auth-button icon="envelope">{{ __('Resend Verification Email') }}</x-auth-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-medium text-zinc-400 ring-1 ring-rx-line transition hover:bg-white/5 hover:text-white">
            <x-icon name="logout" class="h-4 w-4" /> {{ __('Log Out') }}
        </button>
    </form>
</x-guest-layout>
