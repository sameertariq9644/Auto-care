@php
    $accents = [
        'pending' => ['bar' => 'bg-zinc-400', 'chip' => 'bg-white/5 text-zinc-300', 'date' => 'text-zinc-300'],
        'confirmed' => ['bar' => 'bg-rx-accent', 'chip' => 'bg-rx-accent/10 text-blue-400', 'date' => 'text-blue-400'],
        'in_progress' => ['bar' => 'bg-blue-300', 'chip' => 'bg-rx-accent/20 text-blue-300', 'date' => 'text-blue-300'],
        'completed' => ['bar' => 'bg-rx-accent', 'chip' => 'bg-rx-accent text-white', 'date' => 'text-blue-400'],
        'cancelled' => ['bar' => 'bg-zinc-600', 'chip' => 'bg-white/[0.03] text-zinc-500', 'date' => 'text-zinc-500'],
    ];
@endphp

<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="hidden h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-white sm:flex">
                        <x-icon name="wrench" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Workshop') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-4xl">{{ __('My Jobs') }}</h1>
                    </div>
                </div>
                <span class="rounded-full bg-black/20 px-3 py-1.5 text-xs font-medium text-white">
                    {{ __(':count job(s) total.', ['count' => $bookings->total()]) }}
                </span>
            </div>
        </div>

        @if ($bookings->isEmpty())
            <div class="flex flex-col items-center rounded-3xl bg-rx-surface py-16 text-center ring-1 ring-rx-line">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rx-accent/15 text-blue-400">
                    <x-icon name="wrench" class="h-7 w-7" />
                </span>
                <p class="mt-4 text-base font-medium text-white">{{ __('No jobs assigned yet.') }}</p>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Jobs will show up here once the workshop assigns them to you.') }}</p>
            </div>
        @else
            <div class="rounded-3xl bg-rx-surface p-4 ring-1 ring-rx-line sm:p-6">
                <div class="space-y-3">
                    @foreach ($bookings as $booking)
                        @php $accent = $accents[$booking->status] ?? $accents['pending']; @endphp
                        <a
                            href="{{ route('mechanic.bookings.show', $booking) }}"
                            class="group relative flex items-stretch overflow-hidden rounded-2xl bg-white/[0.03] ring-1 ring-rx-line transition hover:-translate-y-0.5 hover:bg-white/[0.05] hover:ring-rx-accent/30"
                        >
                            <span class="w-1.5 shrink-0 {{ $accent['bar'] }}"></span>

                            <div class="flex flex-1 flex-col gap-4 p-4 sm:flex-row sm:items-center sm:p-5">
                                <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl bg-black/40 ring-1 ring-white/10">
                                    <span class="text-[11px] font-semibold uppercase tracking-wide {{ $accent['date'] }}">{{ $booking->preferred_date->format('M') }}</span>
                                    <span class="text-2xl font-bold leading-none text-white">{{ $booking->preferred_date->format('d') }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-semibold text-white">{{ $booking->vehicle->display_name }}</p>
                                        <span class="inline-flex items-center rounded-full {{ $accent['chip'] }} px-2.5 py-0.5 text-xs font-medium">{{ $booking->requested_service }}</span>
                                    </div>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-zinc-400">
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="clock" class="h-4 w-4 text-zinc-500" /> {{ $booking->preferred_date->format('g:i A') }}
                                        </span>
                                        <span class="flex items-center gap-1.5 font-mono text-xs">
                                            <x-icon name="qr" class="h-4 w-4 text-zinc-500" /> {{ $booking->vehicle->plate_number }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-3 sm:flex-col sm:items-end sm:gap-2">
                                    <x-dark-status :status="$booking->status" />
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-400">
                                        {{ __('View Job') }} <x-icon name="chevron-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            @if ($bookings->hasPages())
                <div class="rounded-2xl bg-rx-surface px-4 py-3 ring-1 ring-rx-line">
                    {{ $bookings->links() }}
                </div>
            @endif
        @endif
    </div>
</x-dashboard-layout>
