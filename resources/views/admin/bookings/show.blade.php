<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <x-banner-photo src="images/bookings-bg.jpg" />
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.bookings.index') }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25" title="{{ __('Back to Bookings') }}">
                        <x-icon name="chevron-right" class="h-5 w-5 rotate-180" />
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Booking Details') }} · #{{ $booking->id }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">
                            {{ $booking->vehicle->display_name }}
                        </h1>
                        <p class="mt-0.5 text-sm text-white/80">{{ $booking->requested_service }}</p>
                    </div>
                </div>
                <span class="rounded-xl bg-black/30 p-1">
                    <x-dark-status :status="$booking->status" class="!text-sm" />
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_360px]">

            <!-- Details -->
            <div class="space-y-5">
                <div class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach ([
                            ['icon' => 'user-circle', 'label' => __('Customer'), 'value' => $booking->customer->name, 'sub' => $booking->customer->email],
                            ['icon' => 'calendar', 'label' => __('Preferred Date'), 'value' => $booking->preferred_date->format('M j, Y g:i A')],
                            ['icon' => 'wrench', 'label' => __('Mechanic'), 'value' => $booking->mechanic?->user->name ?? __('Not yet assigned')],
                            ['icon' => 'qr', 'label' => __('Plate Number'), 'value' => $booking->vehicle->plate_number],
                        ] as $item)
                            <div class="flex items-center gap-3 rounded-2xl bg-white/[0.03] p-4 ring-1 ring-rx-line">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                                    <x-icon :name="$item['icon']" class="h-5 w-5" />
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[11px] uppercase tracking-wide text-zinc-500">{{ $item['label'] }}</p>
                                    <p class="truncate text-sm font-semibold text-white">{{ $item['value'] }}</p>
                                    @isset($item['sub'])
                                        <p class="truncate text-xs text-zinc-500">{{ $item['sub'] }}</p>
                                    @endisset
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="relative mt-6 flex h-44 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-b from-white/[0.04] to-transparent">
                        @if ($booking->vehicle->image_url)
                            <img src="{{ $booking->vehicle->image_url }}" alt="" class="max-h-40 object-contain drop-shadow-2xl" />
                        @else
                            <x-car-illustration uid="admin-booking" class="w-72" />
                        @endif
                    </div>
                </div>

                @if ($booking->status === \App\Models\ServiceBooking::STATUS_PENDING)
                    <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}" x-data="{ submitting: false }" @submit="submitting = true" class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">
                        @csrf
                        @method('PATCH')

                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                                <x-icon name="users" class="h-4 w-4" />
                            </span>
                            <div>
                                <label for="mechanic_id" class="block text-sm font-medium text-zinc-200">{{ __('Assign Mechanic') }}</label>
                                <p class="mt-0.5 text-xs text-zinc-500">{{ __('Confirming the booking notifies the customer.') }}</p>
                            </div>
                        </div>

                        <select id="mechanic_id" name="mechanic_id" class="mt-4 block w-full rounded-xl border-0 bg-white/5 px-4 py-3 text-sm text-white ring-1 ring-inset ring-rx-line [color-scheme:dark] focus:ring-2 focus:ring-rx-accent" required>
                            <option value="">{{ __('Select a mechanic') }}</option>
                            @foreach ($mechanics as $mechanic)
                                <option value="{{ $mechanic->id }}">{{ $mechanic->user->name }}@if ($mechanic->specialty) ({{ $mechanic->specialty }})@endif</option>
                            @endforeach
                        </select>
                        @error('mechanic_id') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror

                        <div class="mt-5 flex flex-wrap items-center justify-end gap-3">
                            <button type="submit" :disabled="submitting" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover disabled:cursor-not-allowed disabled:opacity-60">
                                <x-icon name="check-circle" class="h-4 w-4" /> {{ __('Confirm & Assign') }}
                            </button>
                        </div>
                    </form>
                @endif

                @unless (in_array($booking->status, [\App\Models\ServiceBooking::STATUS_COMPLETED, \App\Models\ServiceBooking::STATUS_CANCELLED]))
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-3xl bg-rx-surface px-6 py-4 ring-1 ring-rx-line">
                        <p class="text-sm text-zinc-400">{{ __('Need to call this booking off?') }}</p>
                        <x-confirm-modal
                            name="cancel-booking"
                            :action="route('admin.bookings.cancel', $booking)"
                            method="PATCH"
                            title="{{ __('Cancel this booking?') }}"
                            body="{{ __('The customer will be notified. This action cannot be undone.') }}"
                            confirm-label="{{ __('Cancel Booking') }}"
                        />
                    </div>
                @endunless
            </div>

            <!-- Timeline -->
            <div class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">
                <h2 class="text-xl font-bold italic text-white">{{ __('Timeline') }}</h2>

                <ul class="mt-6">
                    @foreach ($booking->statusHistories as $history)
                        <li class="relative pb-6 pl-8 last:pb-0">
                            @unless ($loop->last)
                                <span class="absolute left-[11px] top-7 h-full w-px bg-white/10"></span>
                            @endunless
                            <span class="absolute left-0 top-0.5 flex h-6 w-6 items-center justify-center rounded-full bg-rx-accent/15 ring-1 ring-rx-accent/30">
                                <span class="h-2 w-2 rounded-full bg-rx-accent"></span>
                            </span>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <x-dark-status :status="$history->status" />
                                <span class="text-xs text-zinc-500">{{ $history->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                            @if ($history->changedBy)
                                <p class="mt-2 text-xs text-zinc-500">{{ __('by') }} <span class="text-zinc-300">{{ $history->changedBy->name }}</span></p>
                            @endif
                            @if ($history->note)
                                <p class="mt-1 text-sm text-zinc-400">{{ $history->note }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-dashboard-layout>
