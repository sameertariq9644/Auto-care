@php
    use App\Models\ServiceBooking;

    $statusCounts = ServiceBooking::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
    $stats = [
        ['icon' => 'clock', 'label' => __('Awaiting Review'), 'value' => $statusCounts->get('pending', 0), 'hint' => __('Need a mechanic assigned')],
        ['icon' => 'calendar', 'label' => __('Confirmed'), 'value' => $statusCounts->get('confirmed', 0), 'hint' => __('Scheduled and assigned')],
        ['icon' => 'wrench', 'label' => __('In Service'), 'value' => $statusCounts->get('in_progress', 0), 'hint' => __('On the workshop floor')],
        ['icon' => 'check-circle', 'label' => __('Completed'), 'value' => $statusCounts->get('completed', 0), 'hint' => __('Finished jobs')],
    ];
    $bars = [
        'pending' => 'bg-zinc-400',
        'confirmed' => 'bg-rx-accent',
        'in_progress' => 'bg-blue-300',
        'completed' => 'bg-rx-accent',
        'cancelled' => 'bg-zinc-600',
    ];
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
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Workshop') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-4xl">{{ __('Bookings') }}</h1>
                    </div>
                </div>
                <span class="rounded-full bg-black/20 px-3 py-1.5 text-xs font-medium text-white">
                    {{ __(':count booking(s) total.', ['count' => $bookings->total()]) }}
                </span>
            </div>
        </div>

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

        @if ($bookings->isEmpty())
            <div class="flex flex-col items-center rounded-3xl bg-rx-surface py-16 text-center ring-1 ring-rx-line">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rx-accent/15 text-blue-400">
                    <x-icon name="calendar" class="h-7 w-7" />
                </span>
                <p class="mt-4 text-base font-medium text-white">{{ __('No bookings yet.') }}</p>
            </div>
        @else
            <div class="flex items-center justify-between px-1">
                <h2 class="text-lg font-semibold text-white">{{ __('All Bookings') }}</h2>
                <p class="text-xs text-zinc-500">{{ __('Pending first · showing :from–:to of :total', ['from' => $bookings->firstItem(), 'to' => $bookings->lastItem(), 'total' => $bookings->total()]) }}</p>
            </div>

            <div class="space-y-3">
                @foreach ($bookings as $booking)
                    @php $pending = $booking->status === ServiceBooking::STATUS_PENDING; @endphp
                    <a
                        href="{{ route('admin.bookings.show', $booking) }}"
                        class="group relative block overflow-hidden rounded-3xl bg-rx-surface ring-1 ring-rx-line transition hover:-translate-y-0.5 hover:ring-rx-accent/40"
                    >
                        <span class="absolute inset-y-0 left-0 w-1 {{ $bars[$booking->status] ?? 'bg-zinc-500' }}"></span>

                        <div class="grid grid-cols-1 gap-4 p-5 pl-6 md:grid-cols-2 xl:grid-cols-[auto_minmax(0,1.1fr)_minmax(0,1.3fr)_minmax(0,1fr)_auto] xl:items-center">
                            <!-- Date block -->
                            <div class="flex items-center gap-3 xl:block">
                                <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl bg-black/40 ring-1 ring-white/10">
                                    <span class="text-[11px] font-semibold uppercase tracking-wide text-blue-400">{{ $booking->preferred_date->format('M') }}</span>
                                    <span class="text-2xl font-bold leading-none text-white">{{ $booking->preferred_date->format('d') }}</span>
                                </div>
                                <p class="text-sm text-zinc-400 xl:hidden">{{ $booking->preferred_date->format('D, g:i A') }}</p>
                            </div>

                            <!-- Customer -->
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/5 text-sm font-semibold text-zinc-300 ring-1 ring-white/10">
                                    {{ str($booking->customer->name)->substr(0, 1)->upper() }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-white">{{ $booking->customer->name }}</p>
                                    <p class="truncate text-xs text-zinc-500">{{ $booking->customer->email }}</p>
                                </div>
                            </div>

                            <!-- Vehicle + service -->
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-12 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-black/40 ring-1 ring-white/10">
                                    @if ($booking->vehicle->image_url)
                                        <img src="{{ $booking->vehicle->image_url }}" alt="" class="h-full w-full object-cover" />
                                    @else
                                        <x-car-illustration uid="abk{{ $booking->id }}" class="w-16" />
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-zinc-200">{{ $booking->vehicle->display_name }}</p>
                                    <span class="mt-1 inline-flex items-center rounded-full bg-rx-accent/10 px-2.5 py-0.5 text-xs font-medium text-blue-400">{{ $booking->requested_service }}</span>
                                </div>
                            </div>

                            <!-- Time + mechanic -->
                            <div class="min-w-0 space-y-1.5 text-sm">
                                <p class="hidden items-center gap-1.5 text-zinc-300 xl:flex">
                                    <x-icon name="clock" class="h-4 w-4 text-zinc-500" />
                                    {{ $booking->preferred_date->format('D, g:i A') }}
                                </p>
                                <p class="flex items-center gap-1.5 {{ $booking->mechanic ? 'text-zinc-300' : 'text-zinc-500' }}">
                                    <x-icon name="wrench" class="h-4 w-4 shrink-0 text-zinc-500" />
                                    <span class="truncate">{{ $booking->mechanic?->user->name ?? __('No mechanic assigned') }}</span>
                                </p>
                            </div>

                            <!-- Status + action -->
                            <div class="flex items-center justify-between gap-3 md:col-span-2 xl:col-span-1 xl:flex-col xl:items-end xl:gap-2">
                                <x-dark-status :status="$booking->status" />
                                @if ($pending)
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-rx-accent px-3 py-1.5 text-xs font-semibold text-white shadow-lg shadow-rx-accent/20">
                                        {{ __('Review & Assign') }} <x-icon name="chevron-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-400">
                                        {{ __('View Details') }} <x-icon name="chevron-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($bookings->hasPages())
            <div class="rounded-2xl bg-rx-surface px-4 py-3 ring-1 ring-rx-line">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
