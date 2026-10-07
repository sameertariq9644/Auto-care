<x-guest-layout>
    <x-slot name="heading">{{ __('Confirm your password') }}</x-slot>
    <x-slot name="subheading">{{ __('This is a secure area. Please confirm your password before continuing.') }}</x-slot>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <x-auth-field name="password" type="password" :label="__('Password')" icon="lock" required autofocus autocomplete="current-password" />

        <x-auth-button icon="shield-check">{{ __('Confirm') }}</x-auth-button>
    </form>
</x-guest-layout>
