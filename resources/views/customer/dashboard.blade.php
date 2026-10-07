@php
    // Smooth sparkline path builder for the stat cards.
    $spark = function (array $values, int $w = 320, int $h = 80) {
        $n = count($values);
        $max = max($values) ?: 1;
        $pts = [];
        foreach ($values as $i => $v) {
            $x = $n > 1 ? round($i * $w / ($n - 1), 1) : $w / 2;
            $y = max($values) > 0 ? round($h - 8 - ($v / $max) * ($h - 20), 1) : $h / 2;
            $pts[] = [$x, $y];
        }
        $d = 'M'.$pts[0][0].','.$pts[0][1];
        for ($i = 1; $i < $n; $i++) {
            $cx = round(($pts[$i - 1][0] + $pts[$i][0]) / 2, 1);
            $d .= " C{$cx},{$pts[$i - 1][1]} {$cx},{$pts[$i][1]} {$pts[$i][0]},{$pts[$i][1]}";
        }
        $peak = array_search(max($values), $values);

        return ['d' => $d, 'area' => $d." L{$w},{$h} L0,{$h} Z", 'peak' => $pts[$peak], 'points' => $pts];
    };

    $spendSpark = $spark($monthlySpend->values()->all());
    $bookingSpark = $spark($monthlyBookings->values()->all());
    $unpaidCount = $invoices->where('status', \App\Models\Invoice::STATUS_UNPAID)->count();

    $bookingValues = $monthlyBookings->values();
    $maxBookings = max($bookingValues->max(), 1);
    $peakMonth = $bookingValues->search($bookingValues->max());

    $chipColors = ['bg-rx-accent', 'bg-blue-400', 'bg-[#B4B4BC]', 'bg-blue-200', 'bg-[#34343A]', 'bg-white'];

    $statusPill = [
        'pending' => 'bg-white/5 text-zinc-300 ring-white/15',
        'confirmed' => 'bg-rx-accent/10 text-blue-400 ring-rx-accent/20',
        'in_progress' => 'bg-rx-accent/20 text-blue-300 ring-rx-accent/50',
        'completed' => 'bg-rx-accent text-white ring-rx-accent',
        'cancelled' => 'bg-white/[0.02] text-zinc-500 ring-white/10',
        'unpaid' => 'bg-white/5 text-zinc-300 ring-white/15',
        'paid' => 'bg-rx-accent text-white ring-rx-accent',
    ];
@endphp

<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Welcome hero (photo: Unsplash, free to use) -->
        <div class="relative overflow-hidden rounded-3xl ring-1 ring-rx-line">
            <img src="{{ asset('images/customer-dashboard-bg.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-right" />
            <div class="absolute inset-0 bg-gradient-to-r from-rx-ink via-rx-ink/80 to-rx-ink/10"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-rx-ink/70 to-transparent"></div>

            <div class="relative flex min-h-[15rem] flex-col justify-end gap-6 p-6 sm:p-8 lg:min-h-[17rem]">
                <div class="max-w-xl">
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-blue-400">{{ now()->format('l, F j') }}</p>
                    <h1 class="mt-2 text-3xl font-extrabold italic tracking-tight text-white sm:text-4xl">{{ __('Welcome back, :name.', ['name' => Auth::user()->name]) }}</h1>
                    <p class="mt-2 text-sm text-zinc-300">{{ __('Here\'s what\'s happening with your vehicles.') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('bookings.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                        <x-icon name="plus" class="h-4 w-4" /> {{ __('Book a service') }}
                    </a>
                    <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-rx-ink/40 px-4 py-2.5 text-sm font-medium text-white backdrop-blur transition hover:bg-white/10">
                        <x-icon name="car" class="h-4 w-4" /> {{ __('My Vehicles') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">

            <!-- Left column -->
            <div class="space-y-5 xl:col-span-7">

                <!-- Stat cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <!-- Outstanding balance -->
                    <div class="relative overflow-hidden rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2">
                                <p class="text-sm text-zinc-400">{{ __('Outstanding Balance') }}</p>
                                <span class="rounded-full bg-rx-accent px-2 py-0.5 text-[11px] font-bold text-white">{{ $unpaidCount }} {{ __('unpaid') }}</span>
                            </div>
                            <a href="{{ route('invoices.index') }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-white/5 text-zinc-400 transition hover:text-white" title="{{ __('View invoices') }}">
                                <x-icon name="chevron-right" class="h-4 w-4" />
                            </a>
                        </div>
                        <p class="mt-3 text-3xl font-bold tracking-tight text-white">Rs {{ number_format($outstandingTotal, 2) }}</p>
                        <p class="mt-1 text-xs text-zinc-500">{{ __('Invoice spend over the last 12 months') }}</p>

                        <svg viewBox="0 0 320 80" class="mt-4 h-20 w-full" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="spendFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0" stop-color="#1652F0" stop-opacity=".25" />
                                    <stop offset="1" stop-color="#1652F0" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            @foreach ($spendSpark['points'] as [$x, $y])
                                <line x1="{{ $x }}" x2="{{ $x }}" y1="{{ $y }}" y2="80" stroke="#1652F0" stroke-opacity=".12" stroke-width="3" />
                            @endforeach
                            <path d="{{ $spendSpark['area'] }}" fill="url(#spendFill)" />
                            <path d="{{ $spendSpark['d'] }}" fill="none" stroke="#1652F0" stroke-width="2.5" stroke-linecap="round" />
                            <line x1="{{ $spendSpark['peak'][0] }}" x2="{{ $spendSpark['peak'][0] }}" y1="0" y2="80" stroke="#1652F0" stroke-opacity=".5" stroke-width="6" stroke-linecap="round" />
                            <circle cx="{{ $spendSpark['peak'][0] }}" cy="{{ $spendSpark['peak'][1] }}" r="6" fill="#fff" stroke="#1652F0" stroke-width="3" />
                        </svg>
                    </div>

                    <!-- Upcoming bookings -->
                    <div class="relative overflow-hidden rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-2">
                                <p class="text-sm text-zinc-400">{{ __('Upcoming Bookings') }}</p>
                                <span class="rounded-full bg-[#34343A] px-2 py-0.5 text-[11px] font-bold text-white">{{ $totalBookings }} {{ __('total') }}</span>
                            </div>
                            <a href="{{ route('bookings.index') }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-white/5 text-zinc-400 transition hover:text-white" title="{{ __('View bookings') }}">
                                <x-icon name="chevron-right" class="h-4 w-4" />
                            </a>
                        </div>
                        <p class="mt-3 text-3xl font-bold tracking-tight text-white">{{ $upcomingBookings->count() }}</p>
                        <p class="mt-1 text-xs text-zinc-500">{{ __('Bookings made over the last 12 months') }}</p>

                        <svg viewBox="0 0 320 80" class="mt-4 h-20 w-full" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="bookFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0" stop-color="#B4B4BC" stop-opacity=".22" />
                                    <stop offset="1" stop-color="#B4B4BC" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            @foreach ($bookingSpark['points'] as [$x, $y])
                                <line x1="{{ $x }}" x2="{{ $x }}" y1="{{ $y }}" y2="80" stroke="#B4B4BC" stroke-opacity=".12" stroke-width="3" />
                            @endforeach
                            <path d="{{ $bookingSpark['area'] }}" fill="url(#bookFill)" />
                            <path d="{{ $bookingSpark['d'] }}" fill="none" stroke="#B4B4BC" stroke-width="2.5" stroke-linecap="round" />
                            <line x1="{{ $bookingSpark['peak'][0] }}" x2="{{ $bookingSpark['peak'][0] }}" y1="0" y2="80" stroke="#B4B4BC" stroke-opacity=".5" stroke-width="6" stroke-linecap="round" />
                            <circle cx="{{ $bookingSpark['peak'][0] }}" cy="{{ $bookingSpark['peak'][1] }}" r="6" fill="#fff" stroke="#B4B4BC" stroke-width="3" />
                        </svg>
                    </div>
                </div>

                <!-- Service statistics -->
                <div x-data="{ range: 12 }" class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-white">{{ __('Service Statistics') }}</h3>
                        <div class="flex gap-2">
                            <button @click="range = 6" :class="range === 6 ? 'bg-rx-accent text-white border-transparent' : 'text-zinc-300 border-white/10 hover:border-white/20'" class="rounded-xl border px-3 py-1.5 text-xs font-medium transition">{{ __('6 Months') }}</button>
                            <button @click="range = 12" :class="range === 12 ? 'bg-rx-accent text-white border-transparent' : 'text-zinc-300 border-white/10 hover:border-white/20'" class="rounded-xl border px-3 py-1.5 text-xs font-medium transition">{{ __('Yearly') }}</button>
                        </div>
                    </div>

                    <!-- Service type chips -->
                    <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2">
                        @forelse ($serviceBreakdown as $service => $count)
                            <span class="flex items-center gap-2 text-xs text-zinc-300">
                                <span class="h-2.5 w-2.5 rounded-sm {{ $chipColors[$loop->index % count($chipColors)] }}"></span>
                                {{ $service }}
                                <span class="text-zinc-500">{{ round($count / max($totalBookings, 1) * 100, 1) }}%</span>
                            </span>
                        @empty
                            <span class="text-xs text-zinc-500">{{ __('No bookings yet — your service mix will appear here.') }}</span>
                        @endforelse
                    </div>

                    <!-- Dot-matrix chart -->
                    <div class="relative mt-6 flex h-56 items-end gap-1.5 sm:gap-3">
                        @foreach ($bookingValues as $idx => $value)
                            @php $filled = $value > 0 ? max(1, (int) round($value / $maxBookings * 11)) : 0; @endphp
                            <div x-show="range === 12 || {{ $idx }} >= 6" class="relative flex flex-1 flex-col items-center gap-2">
                                @if ($idx === $peakMonth && $value > 0)
                                    <div class="absolute -top-16 z-10 whitespace-nowrap rounded-xl bg-zinc-800/95 px-3 py-2 text-[11px] shadow-xl ring-1 ring-white/10">
                                        <p class="text-zinc-400">{{ __('Bookings') }} <span class="ml-1 font-semibold text-white">{{ $value }}</span></p>
                                        <p class="text-zinc-400">{{ __('Spent') }} <span class="ml-1 font-semibold text-white">Rs {{ number_format($monthlySpend[$idx], 0) }}</span></p>
                                    </div>
                                @endif
                                <div class="flex flex-col-reverse gap-1">
                                    @for ($r = 0; $r < 12; $r++)
                                        @if ($r === $filled && $value > 0)
                                            <span class="-mx-0.5 block h-2.5 w-2.5 rounded-full border-2 border-white bg-rx-accent sm:h-3 sm:w-3 {{ $idx === $peakMonth ? 'shadow-[0_0_10px_rgba(22,82,240,.9)]' : 'opacity-70' }}"></span>
                                            @continue
                                        @endif
                                        <span @class([
                                            'block h-1.5 w-1.5 rounded-full sm:h-2 sm:w-2',
                                            'bg-rx-accent shadow-[0_0_6px_rgba(22,82,240,.6)]' => $r < $filled,
                                            'bg-white/10' => $r >= $filled,
                                        ])></span>
                                    @endfor
                                </div>
                                <span class="text-[10px] text-zinc-500">{{ $monthLabels[$idx] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right column: vehicles showcase -->
            <div class="xl:col-span-5">
                <div x-data="{ i: 0, n: {{ max($vehicles->count(), 1) }} }" class="flex h-full flex-col rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">
                            {{ __('My Vehicles') }}
                            <span class="ml-1 text-sm font-normal text-zinc-500">{{ $vehicles->count() }}</span>
                        </h3>
                        @if ($vehicles->count() > 1)
                            <div class="flex gap-2">
                                <button @click="i = (i - 1 + n) % n" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/5 text-zinc-300 transition hover:bg-white/10" aria-label="{{ __('Previous') }}">
                                    <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
                                </button>
                                <button @click="i = (i + 1) % n" class="flex h-9 w-9 items-center justify-center rounded-full bg-rx-accent text-white shadow-lg shadow-rx-accent/30" aria-label="{{ __('Next') }}">
                                    <x-icon name="chevron-right" class="h-4 w-4" />
                                </button>
                            </div>
                        @endif
                    </div>

                    @forelse ($vehicles as $vehicle)
                        <div x-show="i === {{ $loop->index }}" @if (! $loop->first) x-cloak @endif x-transition.opacity.duration.300ms class="flex flex-1 flex-col">
                            <!-- Showcase -->
                            <div class="relative mt-4 flex h-56 items-center justify-center overflow-hidden">
                                @if ($vehicle->image_url)
                                    <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->display_name }}" class="h-full w-full rounded-2xl object-cover" />
                                @else
                                <span class="pointer-events-none absolute inset-x-0 bottom-2 select-none text-center font-serif text-5xl uppercase leading-none text-white/[0.06] sm:text-6xl">
                                    {{ $vehicle->make }} {{ $vehicle->model }}
                                </span>

                                <svg viewBox="0 0 400 180" class="relative w-full max-w-sm">
                                    <defs>
                                        <linearGradient id="body{{ $vehicle->id }}" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0" stop-color="#c7d2de" />
                                            <stop offset=".55" stop-color="#7d8b9b" />
                                            <stop offset="1" stop-color="#3b4450" />
                                        </linearGradient>
                                        <linearGradient id="glass{{ $vehicle->id }}" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0" stop-color="#1f2937" />
                                            <stop offset="1" stop-color="#0b0f14" />
                                        </linearGradient>
                                        <radialGradient id="rim{{ $vehicle->id }}">
                                            <stop offset="0" stop-color="#d4a857" />
                                            <stop offset=".6" stop-color="#6b5530" />
                                            <stop offset="1" stop-color="#1c1c1c" />
                                        </radialGradient>
                                    </defs>
                                    <!-- turntable -->
                                    <ellipse cx="200" cy="150" rx="185" ry="22" fill="#1652F0" fill-opacity=".05" stroke="#1652F0" stroke-opacity=".6" stroke-width="1.5" />
                                    <ellipse cx="200" cy="150" rx="150" ry="14" fill="#000" fill-opacity=".45" />
                                    <!-- body -->
                                    <path d="M40,120 L44,94 Q48,80 66,76 L122,70 L160,42 Q168,36 184,36 L292,36 Q308,36 318,47 L346,74 L364,80 Q378,85 379,100 L379,120 Q379,127 371,127 L339,127 A33,33 0 0 0 273,127 L139,127 A33,33 0 0 0 73,127 L47,127 Q40,127 40,120 Z" fill="url(#body{{ $vehicle->id }})" />
                                    <path d="M134,71 L166,46 Q172,42 181,42 L226,42 L226,71 Z" fill="url(#glass{{ $vehicle->id }})" />
                                    <path d="M234,42 L289,42 Q299,42 307,50 L331,73 L234,73 Z" fill="url(#glass{{ $vehicle->id }})" />
                                    <path d="M60,96 L372,96" stroke="#B4B4BC" stroke-width="1.5" stroke-opacity=".7" />
                                    <rect x="360" y="86" width="16" height="6" rx="3" fill="#e0f2fe" />
                                    <rect x="42" y="92" width="10" height="7" rx="2" fill="#B4B4BC" />
                                    <!-- wheels -->
                                    <circle cx="106" cy="127" r="28" fill="#0a0a0a" />
                                    <circle cx="106" cy="127" r="18" fill="url(#rim{{ $vehicle->id }})" />
                                    <circle cx="106" cy="127" r="5" fill="#111" />
                                    <circle cx="306" cy="127" r="28" fill="#0a0a0a" />
                                    <circle cx="306" cy="127" r="18" fill="url(#rim{{ $vehicle->id }})" />
                                    <circle cx="306" cy="127" r="5" fill="#111" />
                                </svg>

                                <span class="absolute bottom-6 left-1/2 flex h-7 w-7 -translate-x-1/2 items-center justify-center rounded-full bg-rx-accent/20 ring-2 ring-rx-accent/70">
                                    <span class="h-2 w-2 rounded-full bg-white"></span>
                                </span>
                                @endif
                            </div>

                            <div class="text-center">
                                <p class="text-base font-semibold text-white">{{ $vehicle->display_name }}</p>
                            </div>

                            <!-- Feature tiles -->
                            @php
                                $tiles = [
                                    ['icon' => 'calendar', 'label' => $vehicle->year, 'sub' => __('Year')],
                                    ['icon' => 'qr', 'label' => $vehicle->plate_number ?: '—', 'sub' => __('Plate')],
                                    ['icon' => 'shield-check', 'label' => $vehicle->vin ? '…'.substr($vehicle->vin, -6) : '—', 'sub' => __('VIN')],
                                    ['icon' => 'wrench', 'label' => $vehicle->latestServiceRecord?->serviced_at?->format('M j, Y') ?? '—', 'sub' => __('Last Service')],
                                    ['icon' => 'clock', 'label' => $vehicle->nextBooking?->preferred_date?->format('M j') ?? '—', 'sub' => __('Next Booking')],
                                ];
                            @endphp
                            <div class="mt-5 grid grid-cols-3 gap-3">
                                @foreach ($tiles as $tile)
                                    <div class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-white/[0.03] px-2 py-4 text-center ring-1 ring-rx-line">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-zinc-300">
                                            <x-icon :name="$tile['icon']" class="h-5 w-5" />
                                        </span>
                                        <span class="w-full truncate text-sm font-medium text-white">{{ $tile['label'] }}</span>
                                        <span class="text-[11px] text-zinc-500">{{ $tile['sub'] }}</span>
                                    </div>
                                @endforeach
                                <a href="{{ route('vehicles.show', $vehicle) }}" class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-rx-accent/10 px-2 py-4 text-center ring-1 ring-rx-accent/20 transition hover:bg-blue-500/20">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/20 text-blue-400">
                                        <x-icon name="eye" class="h-5 w-5" />
                                    </span>
                                    <span class="text-sm font-medium text-blue-400">{{ __('Details') }}</span>
                                    <span class="text-[11px] text-blue-400/60">{{ __('View vehicle') }}</span>
                                </a>
                            </div>

                            <!-- QR code -->
                            <div class="mt-3 flex items-center gap-4 rounded-2xl bg-white/[0.03] p-3 ring-1 ring-rx-line">
                                @if ($vehicle->qr_code_url)
                                    <a href="{{ route('vehicles.history', $vehicle->qr_uuid) }}" target="_blank" class="shrink-0 rounded-xl bg-white p-1.5" title="{{ __('Open public service history') }}">
                                        <img src="{{ $vehicle->qr_code_url }}" alt="{{ __('QR code') }}" class="h-20 w-20" />
                                    </a>
                                @else
                                    <span class="flex h-[5.75rem] w-[5.75rem] shrink-0 items-center justify-center rounded-xl bg-white/5 text-zinc-500">
                                        <x-icon name="qr" class="h-8 w-8" />
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-white">{{ __('Service History QR') }}</p>
                                    <p class="mt-0.5 text-xs text-zinc-500">{{ __('Scan to see every service done on this vehicle.') }}</p>
                                    @if ($vehicle->qr_code_url)
                                        <a href="{{ $vehicle->qr_code_url }}" download class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-400 hover:text-blue-300">
                                            <x-icon name="download" class="h-3.5 w-3.5" /> {{ __('Download') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-1 flex-col items-center justify-center py-12 text-center">
                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/5 text-zinc-500">
                                <x-icon name="car" class="h-7 w-7" />
                            </span>
                            <p class="mt-3 text-sm text-zinc-400">{{ __('No vehicles yet.') }}</p>
                            <a href="{{ route('vehicles.create') }}" class="mt-2 text-sm font-medium text-blue-400 hover:text-blue-400">{{ __('Add a vehicle') }}</a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Upcoming bookings table -->
        <div class="overflow-hidden rounded-3xl bg-rx-surface ring-1 ring-rx-line">
            <div class="flex flex-wrap items-end justify-between gap-4 p-6">
                <div>
                    <h3 class="text-lg font-semibold text-white">{{ __('Upcoming Bookings') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Keep track of your scheduled services and their status.') }}</p>
                </div>
                <a href="{{ route('bookings.index') }}" class="rounded-xl border border-white/20 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/5">{{ __('View all') }}</a>
            </div>

            @if ($upcomingBookings->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead>
                            <tr class="bg-white/[0.03] text-xs text-zinc-500">
                                <th class="px-6 py-3 font-medium">{{ __('Vehicle') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Booking #') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Service') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Plate') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Preferred Date') }}</th>
                                <th class="px-6 py-3 font-medium">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right font-medium">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach ($upcomingBookings as $booking)
                                <tr class="transition hover:bg-white/[0.02]">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-12 items-center justify-center rounded-lg bg-white/5 text-zinc-300">
                                                <x-icon name="car" class="h-5 w-5" />
                                            </span>
                                            <span class="font-medium text-white">{{ $booking->vehicle->display_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-blue-400">#{{ $booking->id }}</td>
                                    <td class="px-6 py-4 text-zinc-300">{{ $booking->requested_service }}</td>
                                    <td class="px-6 py-4 text-zinc-400">{{ $booking->vehicle->plate_number }}</td>
                                    <td class="px-6 py-4 text-zinc-300">{{ $booking->preferred_date->format('M j, Y g:i A') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusPill[$booking->status] ?? 'bg-white/5 text-zinc-300 ring-white/10' }}">
                                            {{ str($booking->status)->replace('_', ' ')->title() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('bookings.show', $booking) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/5 hover:text-white" title="{{ __('View') }}">
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
                    <p class="mt-2 text-sm text-zinc-400">{{ __('No upcoming bookings.') }}</p>
                    <a href="{{ route('bookings.create') }}" class="mt-2 text-sm font-medium text-blue-400 hover:text-blue-400">{{ __('Book a service') }}</a>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <!-- Recent service history -->
            <div class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                <h3 class="mb-4 text-lg font-semibold text-white">{{ __('Recent Service History') }}</h3>

                @forelse ($recentServiceRecords as $record)
                    <div class="flex items-center gap-3 border-b border-white/5 py-3 last:border-0">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rx-accent/10 text-blue-400">
                            <x-icon name="wrench" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-white">{{ $record->vehicle->display_name }}</p>
                            <p class="truncate text-xs text-zinc-500">{{ $record->description }} — {{ $record->serviced_at->format('M j, Y') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center py-6 text-center">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 text-zinc-500">
                            <x-icon name="wrench" class="h-5 w-5" />
                        </span>
                        <p class="mt-2 text-sm text-zinc-400">{{ __('No service history yet.') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Recent invoices -->
            <div class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">{{ __('Recent Invoices') }}</h3>
                    <a href="{{ route('invoices.index') }}" class="text-sm text-blue-400 hover:text-blue-400">{{ __('View all') }}</a>
                </div>

                @forelse ($invoices as $invoice)
                    <div class="flex items-center justify-between border-b border-white/5 py-3 last:border-0">
                        <a href="{{ route('invoices.show', $invoice) }}" class="flex items-center gap-3 text-sm font-medium text-white hover:text-blue-400">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/10 text-blue-400">
                                <x-icon name="receipt" class="h-4 w-4" />
                            </span>
                            {{ __('Invoice') }} #{{ $invoice->id }}
                        </a>
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-semibold text-white">Rs {{ number_format($invoice->total, 2) }}</span>
                            <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusPill[$invoice->status] ?? 'bg-white/5 text-zinc-300 ring-white/10' }}">
                                {{ str($invoice->status)->title() }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center py-6 text-center">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 text-zinc-500">
                            <x-icon name="receipt" class="h-5 w-5" />
                        </span>
                        <p class="mt-2 text-sm text-zinc-400">{{ __('No invoices yet.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-dashboard-layout>
