<x-guest-layout>
    <x-slot name="heading">{{ __('Welcome back') }}</x-slot>
    <x-slot name="subheading">{{ __('Log in to your AutoCare Pro account.') }}</x-slot>

    @if (session('status'))
        <div class="mb-5 rounded-xl border border-rx-accent/30 bg-rx-accent/10 px-4 py-3 text-sm font-medium text-blue-300">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-auth-field name="email" type="email" :label="__('Email')" icon="envelope" :value="old('email')" placeholder="you@example.com" required autofocus autocomplete="username" />

        <x-auth-field name="password" type="password" :label="__('Password')" icon="lock" required autocomplete="current-password" />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
                <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-white/20 bg-white/5 text-rx-accent focus:ring-rx-accent focus:ring-offset-0">
                <span class="text-sm text-zinc-400">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-400 hover:text-blue-300">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <x-auth-button>{{ __('Log in') }}</x-auth-button>
    </form>

    @if (Route::has('register'))
        <x-slot name="footer">
            {{ __('New to AutoCare Pro?') }}
            <a href="{{ route('register') }}" class="font-semibold text-blue-400 hover:text-blue-300">{{ __('Create an account') }}</a>
        </x-slot>
    @endif
</x-guest-layout>
