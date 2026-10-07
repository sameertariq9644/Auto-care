@php
    $inProgress = $todaysJobs->where('status', \App\Models\ServiceBooking::STATUS_IN_PROGRESS)->count();

    $stats = [
        ['icon' => 'calendar', 'label' => __("Today's Jobs"), 'value' => $todaysJobs->count(), 'accent' => true],
        ['icon' => 'wrench', 'label' => __('In Progress'), 'value' => $inProgress, 'accent' => false],
        ['icon' => 'clock', 'label' => __('Upcoming'), 'value' => $jobQueue->count(), 'accent' => false],
    ];
@endphp

<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Welcome -->
        <div class="flex flex-wrap items-center justify-between gap-4 px-1 pt-2">
            <div>
                <p class="text-2xl font-semibold tracking-tight text-white">{{ __('Welcome back, :name.', ['name' => Auth::user()->name]) }}</p>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Here\'s your job queue for today, :date.', ['date' => today()->format('l, M j')]) }}</p>
            </div>
            <a href="{{ route('mechanic.bookings.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                <x-icon name="wrench" class="h-4 w-4" /> {{ __('All my jobs') }}
            </a>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            @foreach ($stats as $stat)
                <div @class([
                    'relative overflow-hidden rounded-3xl p-6',
                    'bg-rx-accent shadow-xl shadow-rx-accent/30' => $stat['accent'],
                    'bg-rx-surface ring-1 ring-rx-line' => ! $stat['accent'],
                ])>
                    @if ($stat['accent'])
                        <div class="pointer-events-none absolute -right-10 -top-16 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                    @endif
                    <span @class([
                        'relative flex h-10 w-10 items-center justify-center rounded-xl',
                        'bg-white/15 text-white' => $stat['accent'],
                        'bg-rx-accent/15 text-blue-400' => ! $stat['accent'],
                    ])>
                        <x-icon :name="$stat['icon']" class="h-5 w-5" />
                    </span>
                    <p class="relative mt-4 text-sm {{ $stat['accent'] ? 'text-white/80' : 'text-zinc-400' }}">{{ $stat['label'] }}</p>
                    <p class="relative mt-1 text-3xl font-bold tracking-tight text-white">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <!-- Today's jobs -->
        <div class="rounded-3xl bg-rx-surface p-4 ring-1 ring-rx-line sm:p-6">
            <div class="mb-4 px-1">
                <h3 class="text-lg font-semibold text-white">{{ __("Today's Jobs") }}</h3>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Start a job when the car arrives, then log the work to complete it.') }}</p>
            </div>

            @forelse ($todaysJobs as $booking)
                @php $started = $booking->status === \App\Models\ServiceBooking::STATUS_IN_PROGRESS; @endphp
                <div class="mb-3 flex items-stretch overflow-hidden rounded-2xl bg-white/[0.03] ring-1 ring-rx-line last:mb-0">
                    <span class="w-1.5 shrink-0 {{ $started ? 'bg-blue-300' : 'bg-rx-accent' }}"></span>
                    <div class="flex flex-1 flex-col gap-4 p-4 sm:flex-row sm:items-center sm:p-5">
                        <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl bg-black/40 ring-1 ring-white/10">
                            <span class="text-[11px] font-semibold uppercase tracking-wide text-blue-400">{{ $booking->preferred_date->format('A') }}</span>
                            <span class="text-xl font-bold leading-none text-white">{{ $booking->preferred_date->format('g:i') }}</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-white">{{ $booking->vehicle->display_name }}</p>
                                <span class="inline-flex rounded-full bg-rx-accent/10 px-2.5 py-0.5 text-xs font-medium text-blue-400">{{ $booking->requested_service }}</span>
                            </div>
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-zinc-400">
                                <span class="flex items-center gap-1.5 font-mono text-xs">
                                    <x-icon name="qr" class="h-4 w-4 text-zinc-500" /> {{ $booking->vehicle->plate_number }}
                                </span>
                                <x-dark-status :status="$booking->status" />
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            @if ($booking->status === \App\Models\ServiceBooking::STATUS_CONFIRMED)
                                <form method="POST" action="{{ route('mechanic.bookings.start', $booking) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                                        <x-icon name="bolt" class="h-4 w-4" /> {{ __('Start') }}
                                    </button>
                                </form>
                            @elseif ($started)
                                <a href="{{ route('mechanic.bookings.complete.create', $booking) }}" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                                    <x-icon name="check-circle" class="h-4 w-4" /> {{ __('Complete') }}
                                </a>
                            @endif
                            <a href="{{ route('mechanic.bookings.show', $booking) }}" class="flex h-10 w-10 items-center justify-center rounded-xl text-zinc-400 ring-1 ring-rx-line transition hover:bg-white/5 hover:text-white" title="{{ __('View') }}">
                                <x-icon name="eye" class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center py-10 text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rx-accent/15 text-blue-400">
                        <x-icon name="check-circle" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-medium text-white">{{ __('No jobs scheduled for today.') }}</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ __('Enjoy the breather.') }}</p>
                </div>
            @endforelse
        </div>

        <!-- Upcoming queue -->
        <div class="overflow-hidden rounded-3xl bg-rx-surface ring-1 ring-rx-line">
            <div class="flex flex-wrap items-end justify-between gap-4 p-6">
                <div>
                    <h3 class="text-lg font-semibold text-white">{{ __('Upcoming Job Queue') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Jobs assigned to you on later days.') }}</p>
                </div>
                <a href="{{ route('mechanic.bookings.index') }}" class="rounded-xl border border-white/20 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/5">{{ __('View all') }}</a>
            </div>

            @if ($jobQueue->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead>
                            <tr class="bg-white/[0.03] text-xs text-zinc-500">
                                <th class="px-6 py-3 font-medium">{{ __('Vehicle') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Service') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Date') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right font-medium">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach ($jobQueue as $booking)
                                <tr class="transition hover:bg-white/[0.02]">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-12 items-center justify-center rounded-lg bg-white/5 text-zinc-300">
                                                <x-icon name="car" class="h-5 w-5" />
                                            </span>
                                            <span class="font-medium text-white">{{ $booking->vehicle->display_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-zinc-300">{{ $booking->requested_service }}</td>
                                    <td class="px-6 py-4 text-zinc-300">{{ $booking->preferred_date->format('M j, Y g:i A') }}</td>
                                    <td class="px-6 py-4"><x-dark-status :status="$booking->status" /></td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('mechanic.bookings.show', $booking) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/5 hover:text-white" title="{{ __('View') }}">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center border-t border-white/5 py-10 text-center">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 text-zinc-500">
                        <x-icon name="calendar" class="h-5 w-5" />
                    </span>
                    <p class="mt-2 text-sm text-zinc-400">{{ __('No upcoming jobs.') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
