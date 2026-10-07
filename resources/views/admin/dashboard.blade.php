@php
    $revenueTotal = $revenuePaid + $revenueOutstanding;
    $paidPct = $revenueTotal > 0 ? round($revenuePaid / $revenueTotal * 100) : 0;

    $stats = [
        ['icon' => 'clock', 'label' => __('Pending Bookings'), 'value' => $bookingCounts->get('pending', 0), 'route' => 'admin.bookings.index', 'accent' => false],
        ['icon' => 'wrench', 'label' => __('In Progress'), 'value' => $bookingCounts->get('in_progress', 0), 'route' => 'admin.bookings.index', 'accent' => false],
        ['icon' => 'currency-dollar', 'label' => __('Revenue (Paid)'), 'value' => 'Rs '.number_format($revenuePaid, 2), 'route' => 'admin.invoices.index', 'accent' => true],
        ['icon' => 'exclamation-triangle', 'label' => __('Outstanding'), 'value' => 'Rs '.number_format($revenueOutstanding, 2), 'route' => 'admin.invoices.index', 'accent' => false],
    ];
@endphp

<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Welcome hero (photo: Unsplash, free to use) -->
        <div class="relative overflow-hidden rounded-3xl ring-1 ring-rx-line">
            <img src="{{ asset('images/admin-dashboard-bg.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center" />
            <div class="absolute inset-0 bg-gradient-to-r from-rx-ink via-rx-ink/85 to-rx-ink/20"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-rx-ink/70 to-transparent"></div>

            <div class="relative flex min-h-[15rem] flex-col justify-end gap-6 p-6 sm:p-8 lg:min-h-[17rem]">
                <div class="max-w-xl">
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-blue-400">{{ now()->format('l, F j') }}</p>
                    <h1 class="mt-2 text-3xl font-extrabold italic tracking-tight text-white sm:text-4xl">{{ __('Welcome back, :name.', ['name' => Auth::user()->name]) }}</h1>
                    <p class="mt-2 text-sm text-zinc-300">{{ __('Here\'s what\'s happening in the workshop today.') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-rx-ink/40 px-4 py-2.5 text-sm font-medium text-white backdrop-blur transition hover:bg-white/10">
                        <x-icon name="calendar" class="h-4 w-4" /> {{ __('Bookings') }}
                    </a>
                    <a href="{{ route('admin.mechanics.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                        <x-icon name="plus" class="h-4 w-4" /> {{ __('Add Mechanic') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <div @class([
                    'relative overflow-hidden rounded-3xl p-6',
                    'bg-rx-accent shadow-xl shadow-rx-accent/30' => $stat['accent'],
                    'bg-rx-surface ring-1 ring-rx-line' => ! $stat['accent'],
                ])>
                    @if ($stat['accent'])
                        <div class="pointer-events-none absolute -right-10 -top-16 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                    @endif
                    <div class="relative flex items-start justify-between">
                        <span @class([
                            'flex h-10 w-10 items-center justify-center rounded-xl',
                            'bg-white/15 text-white' => $stat['accent'],
                            'bg-rx-accent/15 text-blue-400' => ! $stat['accent'],
                        ])>
                            <x-icon :name="$stat['icon']" class="h-5 w-5" />
                        </span>
                        <a href="{{ route($stat['route']) }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-white/5 text-zinc-400 transition hover:text-white {{ $stat['accent'] ? '!bg-white/15 !text-white' : '' }}">
                            <x-icon name="chevron-right" class="h-4 w-4" />
                        </a>
                    </div>
                    <p class="relative mt-4 text-sm {{ $stat['accent'] ? 'text-white/80' : 'text-zinc-400' }}">{{ $stat['label'] }}</p>
                    <p class="relative mt-1 text-3xl font-bold tracking-tight text-white">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <!-- Revenue overview -->
        <div class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-white">{{ __('Revenue Overview') }}</h3>
                <span class="text-sm text-zinc-500">{{ __('Total') }}: <span class="font-semibold text-white">Rs {{ number_format($revenueTotal, 2) }}</span></span>
            </div>

            <div class="mt-5 flex h-3 w-full gap-1 overflow-hidden rounded-full bg-white/5">
                @if ($revenueTotal > 0)
                    <div class="h-full rounded-full bg-rx-accent shadow-[0_0_10px_rgba(22,82,240,.7)]" style="width: {{ $paidPct }}%"></div>
                    <div class="h-full rounded-full bg-[#B4B4BC]/60" style="width: {{ 100 - $paidPct }}%"></div>
                @endif
            </div>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm">
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-sm bg-rx-accent"></span>
                    <span class="text-zinc-400">{{ __('Paid') }}</span>
                    <span class="font-semibold text-white">Rs {{ number_format($revenuePaid, 2) }}</span>
                    <span class="text-zinc-500">({{ $paidPct }}%)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-sm bg-[#B4B4BC]"></span>
                    <span class="text-zinc-400">{{ __('Outstanding') }}</span>
                    <span class="font-semibold text-white">Rs {{ number_format($revenueOutstanding, 2) }}</span>
                    <span class="text-zinc-500">({{ 100 - $paidPct }}%)</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <!-- Bookings needing attention -->
            <div class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">{{ __('Bookings Needing Attention') }}</h3>
                    <a href="{{ route('admin.bookings.index') }}" class="text-sm text-blue-400 hover:text-blue-300">{{ __('View all') }}</a>
                </div>

                @forelse ($pendingBookings as $booking)
                    <div class="flex items-center justify-between gap-3 border-b border-white/5 py-3 last:border-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rx-accent/10 text-blue-400">
                                <x-icon name="calendar" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-white">{{ $booking->customer->name }} — {{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</p>
                                <p class="truncate text-xs text-zinc-500">{{ $booking->requested_service }} — {{ $booking->preferred_date->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.bookings.show', $booking) }}" class="shrink-0 rounded-lg bg-rx-accent/10 px-3 py-1.5 text-xs font-semibold text-blue-400 ring-1 ring-rx-accent/20 transition hover:bg-rx-accent/20">{{ __('Review') }}</a>
                    </div>
                @empty
                    <div class="flex flex-col items-center py-6 text-center">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 text-zinc-500">
                            <x-icon name="check-circle" class="h-5 w-5" />
                        </span>
                        <p class="mt-2 text-sm text-zinc-400">{{ __('No pending bookings.') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Low stock warnings -->
            <div class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">{{ __('Low Stock Warnings') }}</h3>
                    <a href="{{ route('admin.inventory.index') }}" class="text-sm text-blue-400 hover:text-blue-300">{{ __('View inventory') }}</a>
                </div>

                @forelse ($lowStockItems as $item)
                    <div class="flex items-center justify-between gap-3 border-b border-white/5 py-3 last:border-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-400">
                                <x-icon name="archive" class="h-4 w-4" />
                            </span>
                            <p class="truncate text-sm font-medium text-white">{{ $item->name }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-rose-500/10 px-2.5 py-1 text-xs font-medium text-rose-300 ring-1 ring-inset ring-rose-500/30">
                            <span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>
                            {{ $item->quantity }} {{ __('left') }}
                        </span>
                    </div>
                @empty
                    <div class="flex flex-col items-center py-6 text-center">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-rx-accent/10 text-blue-400">
                            <x-icon name="check-circle" class="h-5 w-5" />
                        </span>
                        <p class="mt-2 text-sm text-zinc-400">{{ __('All stock levels look healthy.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Mechanic workload -->
        <div class="overflow-hidden rounded-3xl bg-rx-surface ring-1 ring-rx-line">
            <div class="flex flex-wrap items-end justify-between gap-4 p-6">
                <div>
                    <h3 class="text-lg font-semibold text-white">{{ __('Mechanic Workload') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Active jobs currently assigned to each mechanic.') }}</p>
                </div>
                <a href="{{ route('admin.mechanics.index') }}" class="rounded-xl border border-white/20 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/5">{{ __('View all') }}</a>
            </div>

            @if ($mechanicWorkload->isNotEmpty())
                @php $maxJobs = max($mechanicWorkload->max('active_bookings_count'), 1); @endphp
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead>
                            <tr class="bg-white/[0.03] text-xs text-zinc-500">
                                <th class="px-6 py-3 font-medium">{{ __('Mechanic') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Specialty') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Active Jobs') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach ($mechanicWorkload as $mechanic)
                                <tr class="transition hover:bg-white/[0.02]">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-rx-accent text-sm font-semibold text-white">
                                                {{ str($mechanic->user->name)->substr(0, 1)->upper() }}
                                            </span>
                                            <span class="font-medium text-white">{{ $mechanic->user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-zinc-300">{{ $mechanic->specialty ?? '—' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-1.5 w-32 overflow-hidden rounded-full bg-white/5">
                                                <div class="h-full rounded-full bg-rx-accent" style="width: {{ round($mechanic->active_bookings_count / $maxJobs * 100) }}%"></div>
                                            </div>
                                            <span class="font-semibold text-white">{{ $mechanic->active_bookings_count }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center border-t border-white/5 py-10 text-center">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 text-zinc-500">
                        <x-icon name="users" class="h-5 w-5" />
                    </span>
                    <p class="mt-2 text-sm text-zinc-400">{{ __('No active mechanics.') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
