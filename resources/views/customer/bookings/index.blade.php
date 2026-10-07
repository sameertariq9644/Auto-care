@php
    use App\Models\ServiceBooking;

    $accents = [
        'pending' => ['bar' => 'bg-zinc-400', 'chip' => 'bg-white/5 text-zinc-300', 'date' => 'text-zinc-300'],
        'confirmed' => ['bar' => 'bg-rx-accent', 'chip' => 'bg-rx-accent/10 text-blue-400', 'date' => 'text-blue-400'],
        'in_progress' => ['bar' => 'bg-blue-300', 'chip' => 'bg-rx-accent/20 text-blue-300', 'date' => 'text-blue-300'],
        'completed' => ['bar' => 'bg-rx-accent', 'chip' => 'bg-rx-accent text-white', 'date' => 'text-blue-400'],
        'cancelled' => ['bar' => 'bg-zinc-600', 'chip' => 'bg-white/[0.03] text-zinc-500', 'date' => 'text-zinc-500'],
    ];

    // The journey every booking moves through, shown as a progress track on each card.
    $steps = [
        ServiceBooking::STATUS_PENDING => __('Requested'),
        ServiceBooking::STATUS_CONFIRMED => __('Confirmed'),
        ServiceBooking::STATUS_IN_PROGRESS => __('In Service'),
        ServiceBooking::STATUS_COMPLETED => __('Completed'),
    ];
    $stepKeys = array_keys($steps);

    $statusCounts = Auth::user()->bookings()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
    $stats = [
        ['icon' => 'calendar', 'label' => __('Upcoming'), 'value' => $statusCounts->get('pending', 0) + $statusCounts->get('confirmed', 0), 'hint' => __('Requested or confirmed')],
        ['icon' => 'wrench', 'label' => __('In Service'), 'value' => $statusCounts->get('in_progress', 0), 'hint' => __('Being worked on now')],
        ['icon' => 'check-circle', 'label' => __('Completed'), 'value' => $statusCounts->get('completed', 0), 'hint' => __('Finished jobs')],
        ['icon' => 'clipboard-check', 'label' => __('All Bookings'), 'value' => $statusCounts->sum(), 'hint' => __('Since you joined')],
    ];

    $next = Auth::user()->bookings()
        ->with(['vehicle', 'mechanic.user'])
        ->whereIn('status', [ServiceBooking::STATUS_PENDING, ServiceBooking::STATUS_CONFIRMED])
        ->where('preferred_date', '>=', now())
        ->oldest('preferred_date')
        ->first();
@endphp

<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <x-banner-photo src="images/bookings-bg.jpg" />
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="hidden h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-white sm:flex">
                        <x-icon name="calendar" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('My Bookings') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-4xl">{{ __('Appointments') }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full bg-black/20 px-3 py-1.5 text-xs font-medium text-white">
                        {{ __(':count booking(s) total.', ['count' => $bookings->total()]) }}
                    </span>
                    <a href="{{ route('bookings.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rx-accent shadow-lg transition hover:bg-blue-50">
                        <x-icon name="plus" class="h-4 w-4" /> {{ __('Book a Service') }}
                    </a>
                </div>
            </div>
        </div>

        @if ($bookings->isEmpty())
            <div class="flex flex-col items-center rounded-3xl bg-rx-surface py-16 text-center ring-1 ring-rx-line">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rx-accent/15 text-blue-400">
                    <x-icon name="calendar" class="h-7 w-7" />
                </span>
                <p class="mt-4 text-base font-medium text-white">{{ __("You haven't booked any services yet.") }}</p>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Schedule your first appointment in a couple of clicks.') }}</p>
                <a href="{{ route('bookings.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                    <x-icon name="plus" class="h-4 w-4" /> {{ __('Book a Service') }}
                </a>
            </div>
        @else
            <!-- Overview -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($stats as $stat)
                    <div class="rounded-3xl bg-rx-surface p-5 ring-1 ring-rx-line">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-zinc-400">{{ $stat['label'] }}</p>
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                                <x-icon :name="$stat['icon']" class="h-4 w-4" />
                            </span>
                        </div>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-white">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-xs text-zinc-500">{{ $stat['hint'] }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Next appointment -->
            @if ($next)
                <a href="{{ route('bookings.show', $next) }}" class="group relative block overflow-hidden rounded-3xl bg-rx-surface ring-1 ring-rx-line transition hover:ring-rx-accent/40">
                    <div class="pointer-events-none absolute -left-16 -top-24 h-64 w-64 rounded-full bg-rx-accent/15 blur-3xl"></div>
                    <div class="relative grid grid-cols-1 items-center gap-6 p-6 sm:p-8 lg:grid-cols-[1fr_auto]">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex items-center gap-2 rounded-full bg-rx-accent/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rx-accent"></span> {{ __('Next Appointment') }}
                                </span>
                                <span class="text-xs text-zinc-500">{{ $next->preferred_date->diffForHumans() }}</span>
                            </div>

                            <p class="mt-4 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                {{ $next->preferred_date->format('l, F j') }}
                                <span class="text-blue-400">· {{ $next->preferred_date->format('g:i A') }}</span>
                            </p>
                            <p class="mt-1 text-sm text-zinc-400">
                                {{ $next->requested_service }} {{ __('for your') }} {{ $next->vehicle->display_name }}
                            </p>

                            <div class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-zinc-300">
                                <span class="flex items-center gap-2">
                                    <x-icon name="wrench" class="h-4 w-4 text-zinc-500" />
                                    {{ $next->mechanic?->user->name ?? __('Mechanic not yet assigned') }}
                                </span>
                                <span class="flex items-center gap-2 font-mono text-xs">
                                    <x-icon name="qr" class="h-4 w-4 text-zinc-500" />
                                    {{ $next->vehicle->plate_number }}
                                </span>
                                <x-dark-status :status="$next->status" />
                            </div>
                        </div>

                        <div class="flex items-center justify-center lg:w-72">
                            @if ($next->vehicle->image_url)
                                <img src="{{ $next->vehicle->image_url }}" alt="" class="h-36 w-full rounded-2xl object-cover ring-1 ring-white/10" />
                            @else
                                <x-car-illustration uid="next-booking" class="w-64" />
                            @endif
                        </div>
                    </div>
                </a>
            @endif

            <!-- All bookings -->
            <div class="flex items-center justify-between px-1">
                <h2 class="text-lg font-semibold text-white">{{ __('All Appointments') }}</h2>
                <p class="text-xs text-zinc-500">{{ __('Showing :from–:to of :total', ['from' => $bookings->firstItem(), 'to' => $bookings->lastItem(), 'total' => $bookings->total()]) }}</p>
            </div>

            <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                @foreach ($bookings as $booking)
                    @php
                        $accent = $accents[$booking->status] ?? ['bar' => 'bg-zinc-500', 'chip' => 'bg-white/5 text-zinc-300', 'date' => 'text-zinc-300'];
                        $reached = array_search($booking->status, $stepKeys);
                    @endphp
                    <a
                        href="{{ route('bookings.show', $booking) }}"
                        class="group relative flex flex-col overflow-hidden rounded-3xl bg-rx-surface ring-1 ring-rx-line transition hover:-translate-y-0.5 hover:ring-rx-accent/40"
                    >
                        <span class="absolute inset-y-0 left-0 w-1 {{ $accent['bar'] }}"></span>

                        <div class="flex items-start gap-4 p-5 pl-6">
                            <!-- Date block -->
                            <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl bg-black/40 ring-1 ring-white/10">
                                <span class="text-[11px] font-semibold uppercase tracking-wide {{ $accent['date'] }}">{{ $booking->preferred_date->format('M') }}</span>
                                <span class="text-2xl font-bold leading-none text-white">{{ $booking->preferred_date->format('d') }}</span>
                            </div>

                            <!-- Details -->
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="truncate font-semibold text-white">
                                        {{ $booking->vehicle->display_name }}
                                    </p>
                                    <x-dark-status :status="$booking->status" />
                                </div>
                                <span class="mt-1.5 inline-flex items-center rounded-full {{ $accent['chip'] }} px-2.5 py-0.5 text-xs font-medium">
                                    {{ $booking->requested_service }}
                                </span>
                                <div class="mt-2.5 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-zinc-400">
                                    <span class="flex items-center gap-1.5">
                                        <x-icon name="clock" class="h-4 w-4 text-zinc-500" />
                                        {{ $booking->preferred_date->format('D, g:i A') }}
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <x-icon name="wrench" class="h-4 w-4 text-zinc-500" />
                                        {{ $booking->mechanic?->user->name ?? __('Not yet assigned') }}
                                    </span>
                                    <span class="flex items-center gap-1.5 font-mono text-xs">
                                        <x-icon name="qr" class="h-4 w-4 text-zinc-500" />
                                        {{ $booking->vehicle->plate_number }}
                                    </span>
                                </div>
                            </div>

                            <!-- Vehicle thumbnail -->
                            <span class="hidden h-16 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-black/40 ring-1 ring-white/10 sm:flex">
                                @if ($booking->vehicle->image_url)
                                    <img src="{{ $booking->vehicle->image_url }}" alt="" class="h-full w-full object-cover" />
                                @else
                                    <x-car-illustration uid="bk{{ $booking->id }}" class="w-20" />
                                @endif
                            </span>
                        </div>

                        <!-- Progress track -->
                        <div class="mt-auto border-t border-white/5 bg-white/[0.02] px-5 py-4 pl-6">
                            @if ($reached === false)
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs text-zinc-500">{{ __('This booking was cancelled.') }}</p>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-400">
                                        {{ __('View Details') }} <x-icon name="chevron-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />
                                    </span>
                                </div>
                            @else
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach ($steps as $key => $label)
                                        @php $index = array_search($key, $stepKeys); @endphp
                                        <div>
                                            <span class="block h-1.5 rounded-full {{ $index <= $reached ? 'bg-rx-accent' : 'bg-white/10' }}"></span>
                                            <span class="mt-2 block truncate text-[11px] {{ $index === $reached ? 'font-semibold text-white' : ($index < $reached ? 'text-zinc-400' : 'text-zinc-600') }}">{{ $label }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 flex justify-end">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-400">
                                        {{ __('View Details') }} <x-icon name="chevron-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />
                                    </span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($bookings->hasPages())
                <div class="rounded-2xl bg-rx-surface px-4 py-3 ring-1 ring-rx-line">
                    {{ $bookings->links() }}
                </div>
            @endif
        @endif
    </div>
</x-dashboard-layout>
