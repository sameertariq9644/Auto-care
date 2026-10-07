@php
    $input = 'mt-1.5 block w-full rounded-xl border-0 bg-white/5 px-4 py-3 text-sm text-white placeholder-zinc-500 ring-1 ring-inset ring-rx-line transition focus:bg-white/[0.07] focus:ring-2 focus:ring-rx-accent';
    $label = 'block text-sm font-medium text-zinc-200';
    $button = 'inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover focus:outline-none focus:ring-2 focus:ring-rx-accent focus:ring-offset-2 focus:ring-offset-rx-surface';
    $saved = 'flex items-center gap-1.5 text-sm font-medium text-blue-400';
@endphp

<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center gap-5">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-2xl font-bold text-white ring-2 ring-white/30">
                    {{ str($user->name)->substr(0, 1)->upper() }}
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Profile') }}</p>
                    <h1 class="truncate text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">{{ $user->name }}</h1>
                    <p class="truncate text-sm text-white/80">{{ $user->email }} · {{ __('Member since :date', ['date' => $user->created_at->format('M Y')]) }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

            <!-- Profile information -->
            <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">
                <header class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                        <x-icon name="user-circle" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ __('Profile Information') }}</h2>
                        <p class="text-sm text-zinc-500">{{ __("Update your account's profile information and email address.") }}</p>
                    </div>
                </header>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
                    @csrf
                    @method('patch')

                    <div>
                        <label for="name" class="{{ $label }}">{{ __('Name') }}</label>
                        <input id="name" name="name" type="text" class="{{ $input }}" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
                        @error('name') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="{{ $label }}">{{ __('Email') }}</label>
                        <input id="email" name="email" type="email" class="{{ $input }}" value="{{ old('email', $user->email) }}" required autocomplete="username" />
                        @error('email') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-3 rounded-xl bg-white/5 p-3 text-sm text-zinc-300 ring-1 ring-white/15">
                                {{ __('Your email address is unverified.') }}
                                <button form="send-verification" class="font-semibold underline hover:text-white">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>

                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-2 font-medium text-blue-400">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit" class="{{ $button }}">{{ __('Save') }}</button>

                        @if (session('status') === 'profile-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="{{ $saved }}">
                                <x-icon name="check-circle" class="h-4 w-4" /> {{ __('Saved.') }}
                            </p>
                        @endif
                    </div>
                </form>
            </section>

            <!-- Password -->
            <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">
                <header class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                        <x-icon name="shield-check" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ __('Update Password') }}</h2>
                        <p class="text-sm text-zinc-500">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
                    </div>
                </header>

                <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
                    @csrf
                    @method('put')

                    <div>
                        <label for="update_password_current_password" class="{{ $label }}">{{ __('Current Password') }}</label>
                        <input id="update_password_current_password" name="current_password" type="password" class="{{ $input }}" autocomplete="current-password" />
                        @foreach ($errors->updatePassword->get('current_password') as $message)
                            <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div>
                        <label for="update_password_password" class="{{ $label }}">{{ __('New Password') }}</label>
                        <input id="update_password_password" name="password" type="password" class="{{ $input }}" autocomplete="new-password" />
                        @foreach ($errors->updatePassword->get('password') as $message)
                            <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div>
                        <label for="update_password_password_confirmation" class="{{ $label }}">{{ __('Confirm Password') }}</label>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="{{ $input }}" autocomplete="new-password" />
                        @foreach ($errors->updatePassword->get('password_confirmation') as $message)
                            <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit" class="{{ $button }}">{{ __('Save') }}</button>

                        @if (session('status') === 'password-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="{{ $saved }}">
                                <x-icon name="check-circle" class="h-4 w-4" /> {{ __('Saved.') }}
                            </p>
                        @endif
                    </div>
                </form>
            </section>
        </div>

        <!-- Danger zone -->
        <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-red-500/20 sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <header class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-500/15 text-red-400">
                        <x-icon name="exclamation-triangle" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ __('Delete Account') }}</h2>
                        <p class="max-w-2xl text-sm text-zinc-500">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>
                    </div>
                </header>

                <button
                    type="button"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-500/10 px-5 py-2.5 text-sm font-semibold text-red-300 ring-1 ring-red-500/30 transition hover:bg-red-500 hover:text-white"
                >
                    <x-icon name="trash" class="h-4 w-4" /> {{ __('Delete Account') }}
                </button>
            </div>

            <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                    @csrf
                    @method('delete')

                    <h2 class="text-lg font-medium text-slate-900">
                        {{ __('Are you sure you want to delete your account?') }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-600">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>

                    <div class="mt-6">
                        <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-3/4" placeholder="{{ __('Password') }}" />
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-danger-button class="ms-3">
                            {{ __('Delete Account') }}
                        </x-danger-button>
                    </div>
                </form>
            </x-modal>
        </section>
    </div>
</x-dashboard-layout>
