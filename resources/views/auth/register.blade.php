<x-guest-layout>
    <x-slot name="heading">{{ __('Get started') }}</x-slot>
    <x-slot name="subheading">{{ __('Create your account to book services and track your vehicles in one place.') }}</x-slot>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <x-auth-field name="name" :label="__('Full name')" icon="user-circle" :value="old('name')" required autofocus autocomplete="name" />

        <x-auth-field name="email" type="email" :label="__('Email')" icon="envelope" :value="old('email')" placeholder="you@example.com" required autocomplete="username" />

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-auth-field name="password" type="password" :label="__('Password')" icon="lock" required autocomplete="new-password" />
            <x-auth-field name="password_confirmation" type="password" :label="__('Confirm password')" icon="lock" required autocomplete="new-password" />
        </div>

        <ul class="grid grid-cols-1 gap-2 rounded-2xl bg-white/[0.03] p-4 text-xs text-zinc-400 ring-1 ring-rx-line sm:grid-cols-3">
            @foreach ([['icon' => 'car', 'text' => __('Add your vehicles')], ['icon' => 'calendar', 'text' => __('Book services')], ['icon' => 'qr', 'text' => __('Get a history QR')]] as $perk)
                <li class="flex items-center gap-2">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-rx-accent/15 text-blue-400">
                        <x-icon :name="$perk['icon']" class="h-4 w-4" />
                    </span>
                    {{ $perk['text'] }}
                </li>
            @endforeach
        </ul>

        <x-auth-button>{{ __('Create account') }}</x-auth-button>
    </form>

    <x-slot name="footer">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" class="font-semibold text-blue-400 hover:text-blue-300">{{ __('Log in') }}</a>
    </x-slot>
</x-guest-layout>
