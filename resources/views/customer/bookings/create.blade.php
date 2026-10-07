@php
    $serviceIcons = [
        'Oil Change' => 'beaker',
        'Brake Inspection' => 'shield-check',
        'Tire Rotation' => 'arrow-path',
        'Battery Check' => 'bolt',
        'General Inspection' => 'clipboard-check',
        'Diagnostic' => 'cog',
    ];
    $selectedVehicle = old('vehicle_id', request('vehicle_id'));
    $selectedService = old('requested_service');
@endphp

<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <x-banner-photo src="images/bookings-bg.jpg" />
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex items-center gap-4">
                <a href="{{ route('bookings.index') }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25" title="{{ __('Back to My Bookings') }}">
                    <x-icon name="chevron-right" class="h-5 w-5 rotate-180" />
                </a>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('My Bookings') }}</p>
                    <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">{{ __('Book a Service') }}</h1>
                    <p class="mt-0.5 text-sm text-white/80">{{ __('Pick a vehicle, service type and your preferred time.') }}</p>
                </div>
            </div>
        </div>

        @if ($vehicles->isEmpty())
            <div class="flex flex-col items-center rounded-3xl bg-rx-surface py-16 text-center ring-1 ring-rx-line">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/5 text-zinc-500">
                    <x-icon name="truck" class="h-7 w-7" />
                </span>
                <p class="mt-4 text-sm text-zinc-400">{{ __('You need to add a vehicle before booking a service.') }}</p>
                <a href="{{ route('vehicles.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                    <x-icon name="plus" class="h-4 w-4" /> {{ __('Add Vehicle') }}
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('bookings.store') }}" class="space-y-5">
                @csrf

                <!-- Vehicle -->
                <fieldset class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rx-accent text-sm font-bold text-white">1</span>
                        <legend class="text-base font-semibold text-white">{{ __('Vehicle') }}</legend>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($vehicles as $vehicle)
                            <label class="group relative flex cursor-pointer items-center gap-4 rounded-2xl bg-white/[0.03] p-4 ring-1 ring-white/10 transition hover:bg-white/[0.06] has-[:checked]:bg-rx-accent/15 has-[:checked]:ring-2 has-[:checked]:ring-rx-accent">
                                <input type="radio" name="vehicle_id" value="{{ $vehicle->id }}" class="peer sr-only" @checked($selectedVehicle == $vehicle->id) required />
                                <span class="flex h-14 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-black/40">
                                    @if ($vehicle->image_url)
                                        <img src="{{ $vehicle->image_url }}" alt="" class="h-full w-full object-contain" />
                                    @else
                                        <x-car-illustration :uid="'bk'.$vehicle->id" class="w-16" />
                                    @endif
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-white">{{ $vehicle->display_name }}</span>
                                    <span class="block font-mono text-xs text-zinc-500">{{ $vehicle->plate_number }}</span>
                                </span>
                                <x-icon name="check-circle" class="absolute right-3 top-3 hidden h-5 w-5 text-blue-400 peer-checked:block" />
                            </label>
                        @endforeach
                    </div>
                    @error('vehicle_id') <p class="mt-3 text-sm text-rose-400">{{ $message }}</p> @enderror
                </fieldset>

                <!-- Service Type -->
                <fieldset class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rx-accent text-sm font-bold text-white">2</span>
                        <legend class="text-base font-semibold text-white">{{ __('Service Type') }}</legend>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-7">
                        @foreach ($serviceTypes as $type)
                            <label class="flex cursor-pointer flex-col items-center gap-3 rounded-2xl bg-white/[0.03] px-3 py-5 text-center text-sm font-medium text-zinc-300 ring-1 ring-white/10 transition hover:bg-white/[0.06] hover:text-white has-[:checked]:-translate-y-0.5 has-[:checked]:bg-rx-accent has-[:checked]:text-white has-[:checked]:shadow-lg has-[:checked]:shadow-rx-accent/30 has-[:checked]:ring-0">
                                <input type="radio" name="requested_service" value="{{ $type }}" class="sr-only" @checked($selectedService === $type) required />
                                <x-icon :name="$serviceIcons[$type] ?? 'ellipsis'" class="h-7 w-7" />
                                {{ $type }}
                            </label>
                        @endforeach
                    </div>
                    @error('requested_service') <p class="mt-3 text-sm text-rose-400">{{ $message }}</p> @enderror
                </fieldset>

                <!-- Preferred Date -->
                <fieldset class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rx-accent text-sm font-bold text-white">3</span>
                        <legend class="text-base font-semibold text-white">
                            <label for="preferred_date">{{ __('Preferred Date & Time') }}</label>
                        </legend>
                    </div>

                    <div class="relative mt-4 max-w-sm">
                        <x-icon name="calendar" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-blue-400" />
                        <input id="preferred_date" type="datetime-local" name="preferred_date" value="{{ old('preferred_date') }}" required
                            class="block w-full rounded-xl border-0 bg-white/5 py-3 pl-12 pr-4 text-sm text-white ring-1 ring-inset ring-rx-line [color-scheme:dark] focus:ring-2 focus:ring-rx-accent" />
                    </div>
                    @error('preferred_date') <p class="mt-3 text-sm text-rose-400">{{ $message }}</p> @enderror
                </fieldset>

                <div class="flex items-center justify-end gap-3 rounded-3xl bg-rx-surface px-6 py-4 ring-1 ring-rx-line">
                    <a href="{{ route('bookings.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-zinc-400 transition hover:bg-white/5 hover:text-white">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover focus:outline-none focus:ring-2 focus:ring-rx-accent focus:ring-offset-2 focus:ring-offset-rx-surface">
                        <x-icon name="check-circle" class="h-4 w-4" /> {{ __('Submit Booking') }}
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-dashboard-layout>
