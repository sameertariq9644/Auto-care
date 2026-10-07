<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('mechanic.bookings.index') }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25" title="{{ __('Back to My Jobs') }}">
                        <x-icon name="chevron-right" class="h-5 w-5 rotate-180" />
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Job Details') }} · #{{ $booking->id }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">
                            {{ $booking->vehicle->display_name }}
                        </h1>
                        <p class="mt-0.5 text-sm text-white/80">{{ $booking->requested_service }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-xl bg-black/30 p-1">
                        <x-dark-status :status="$booking->status" class="!text-sm" />
                    </span>
                    @if ($booking->status === \App\Models\ServiceBooking::STATUS_CONFIRMED)
                        <form method="POST" action="{{ route('mechanic.bookings.start', $booking) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rx-accent shadow-lg transition hover:bg-blue-50">
                                <x-icon name="bolt" class="h-4 w-4" /> {{ __('Start Job') }}
                            </button>
                        </form>
                    @elseif ($booking->status === \App\Models\ServiceBooking::STATUS_IN_PROGRESS)
                        <a href="{{ route('mechanic.bookings.complete.create', $booking) }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rx-accent shadow-lg transition hover:bg-blue-50">
                            <x-icon name="check-circle" class="h-4 w-4" /> {{ __('Log Service & Complete') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_360px]">

            <!-- Details -->
            <div class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['icon' => 'user-circle', 'label' => __('Customer'), 'value' => $booking->customer->name],
                        ['icon' => 'calendar', 'label' => __('Preferred Date'), 'value' => $booking->preferred_date->format('M j, Y g:i A')],
                        ['icon' => 'cog', 'label' => __('Service'), 'value' => $booking->requested_service],
                        ['icon' => 'qr', 'label' => __('Plate Number'), 'value' => $booking->vehicle->plate_number],
                    ] as $item)
                        <div class="flex items-center gap-3 rounded-2xl bg-white/[0.03] p-4 ring-1 ring-rx-line">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                                <x-icon :name="$item['icon']" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] uppercase tracking-wide text-zinc-500">{{ $item['label'] }}</p>
                                <p class="truncate text-sm font-semibold text-white">{{ $item['value'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="relative mt-6 flex h-44 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-b from-white/[0.04] to-transparent">
                    @if ($booking->vehicle->image_url)
                        <img src="{{ $booking->vehicle->image_url }}" alt="" class="max-h-40 object-contain drop-shadow-2xl" />
                    @else
                        <x-car-illustration uid="mechanic-job" class="w-72" />
                    @endif
                </div>
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
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-dashboard-layout>
