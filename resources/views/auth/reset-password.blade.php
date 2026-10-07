<x-guest-layout>
    <x-slot name="heading">{{ __('Reset your password') }}</x-slot>
    <x-slot name="subheading">{{ __('Choose a new password for your account.') }}</x-slot>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-auth-field name="email" type="email" :label="__('Email')" icon="envelope" :value="old('email', $request->email)" required autofocus autocomplete="username" />

        <x-auth-field name="password" type="password" :label="__('New password')" icon="lock" required autocomplete="new-password" />

        <x-auth-field name="password_confirmation" type="password" :label="__('Confirm password')" icon="lock" required autocomplete="new-password" />

        <x-auth-button>{{ __('Reset Password') }}</x-auth-button>
    </form>
</x-guest-layout>
