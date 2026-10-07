<x-guest-layout>
    <x-slot name="heading">{{ __('Forgot your password?') }}</x-slot>
    <x-slot name="subheading">{{ __('No problem. Enter your email and we\'ll send you a reset link.') }}</x-slot>

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-rx-accent/30 bg-rx-accent/10 px-4 py-3 text-sm font-medium text-blue-300">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <x-auth-field name="email" type="email" :label="__('Email')" icon="envelope" :value="old('email')" placeholder="you@example.com" required autofocus />

        <x-auth-button icon="envelope">{{ __('Email Password Reset Link') }}</x-auth-button>
    </form>

    <x-slot name="footer">
        {{ __('Remembered it?') }}
        <a href="{{ route('login') }}" class="font-semibold text-blue-400 hover:text-blue-300">{{ __('Back to log in') }}</a>
    </x-slot>
</x-guest-layout>
