@php
    $input = 'mt-1.5 block w-full rounded-xl border-0 bg-white/5 px-4 py-3 text-sm text-white placeholder-zinc-500 ring-1 ring-inset ring-rx-line transition [color-scheme:dark] focus:bg-white/[0.07] focus:ring-2 focus:ring-rx-accent';
    $label = 'block text-sm font-medium text-zinc-200';
    $hint = 'mt-0.5 text-xs text-zinc-500';
@endphp

<x-dashboard-layout>
    <div class="px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">
        <form method="POST" action="{{ route('admin.mechanics.store') }}" class="space-y-5">
            @csrf

            <!-- Banner -->
            <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
                <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative flex items-center gap-4">
                    <a href="{{ route('admin.mechanics.index') }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25" title="{{ __('Back to Mechanics') }}">
                        <x-icon name="chevron-right" class="h-5 w-5 rotate-180" />
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Mechanics') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">{{ __('Add a Mechanic') }}</h1>
                        <p class="mt-0.5 text-sm text-white/80">{{ __('Create a login for a new mechanic on your team.') }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <!-- Profile -->
                <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                            <x-icon name="user-circle" class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="{{ $label }}">{{ __('Profile') }}</p>
                            <p class="{{ $hint }}">{{ __('Who they are and what they work on.') }}</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="name" class="{{ $label }}">{{ __('Name') }}</label>
                        <input id="name" class="{{ $input }}" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
                        @error('name') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-4">
                        <label for="phone" class="{{ $label }}">{{ __('Phone') }}</label>
                        <input id="phone" class="{{ $input }}" type="text" name="phone" value="{{ old('phone') }}" autocomplete="tel" />
                        @error('phone') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-4">
                        <label for="specialty" class="{{ $label }}">{{ __('Specialty') }}</label>
                        <input id="specialty" class="{{ $input }}" type="text" name="specialty" value="{{ old('specialty') }}" placeholder="{{ __('e.g. Brakes, Electrical, Engine') }}" />
                        @error('specialty') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </section>

                <!-- Login -->
                <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                            <x-icon name="shield-check" class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="{{ $label }}">{{ __('Login Details') }}</p>
                            <p class="{{ $hint }}">{{ __('They\'ll use these to sign in.') }}</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="email" class="{{ $label }}">{{ __('Email') }}</label>
                        <input id="email" class="{{ $input }}" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
                        @error('email') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-4">
                        <label for="password" class="{{ $label }}">{{ __('Password') }}</label>
                        <input id="password" class="{{ $input }}" type="password" name="password" required autocomplete="new-password" />
                        @error('password') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-4">
                        <label for="password_confirmation" class="{{ $label }}">{{ __('Confirm Password') }}</label>
                        <input id="password_confirmation" class="{{ $input }}" type="password" name="password_confirmation" required autocomplete="new-password" />
                        @error('password_confirmation') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </section>
            </div>

            <div class="flex items-center justify-end gap-3 rounded-3xl bg-rx-surface px-6 py-4 ring-1 ring-rx-line">
                <a href="{{ route('admin.mechanics.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-zinc-400 transition hover:bg-white/5 hover:text-white">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                    <x-icon name="plus" class="h-4 w-4" /> {{ __('Create Mechanic') }}
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
