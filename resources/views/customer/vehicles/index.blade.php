@php
    $serviceIcons = [
        'Oil Change' => 'beaker',
        'Brake Inspection' => 'shield-check',
        'Tire Rotation' => 'arrow-path',
        'Battery Check' => 'bolt',
        'General Inspection' => 'clipboard-check',
        'Diagnostic' => 'cog',
        'Other' => 'ellipsis',
    ];

    $tileMeta = $vehicles->getCollection()->mapWithKeys(fn ($v) => [
        $v->id => strtolower("{$v->year} {$v->make} {$v->model} {$v->plate_number}"),
    ]);

    $circumference = 2 * M_PI * 80;
@endphp

<x-dashboard-layout>
    <div
        class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6"
        x-data="{ sel: {{ $vehicles->first()?->id ?? 'null' }}, q: '', meta: @js($tileMeta) }"
    >
        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="hidden h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-white sm:flex">
                        <x-icon name="car" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('My Vehicles') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-4xl">{{ __('Your digital garage') }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full bg-black/20 px-3 py-1.5 text-xs font-medium text-white">
                        {{ __(':count vehicle(s) on your account.', ['count' => $vehicles->total()]) }}
                    </span>
                    <a href="{{ route('vehicles.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rx-accent shadow-lg transition hover:bg-blue-50">
                        <x-icon name="plus" class="h-4 w-4" /> {{ __('Add Vehicle') }}
                    </a>
                </div>
            </div>
        </div>

        @if ($vehicles->isEmpty())
            <div class="flex flex-col items-center rounded-3xl bg-rx-surface py-16 text-center ring-1 ring-rx-line">
                <x-car-illustration uid="empty" class="w-60 opacity-70" />
                <p class="mt-4 text-base font-medium text-white">{{ __('Your garage is empty.') }}</p>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Add your first vehicle to start booking services and tracking history.') }}</p>
                <a href="{{ route('vehicles.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                    <x-icon name="plus" class="h-4 w-4" /> {{ __('Add Vehicle') }}
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_380px]">

                <!-- Main panel -->
                <div class="min-w-0 rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">

                    <!-- Choose vehicle -->
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <h2 class="text-xl font-bold text-white">{{ __('Choose your vehicle') }}</h2>
                        <div class="relative w-full sm:w-64">
                            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-500" />
                            <input x-model="q" type="text" placeholder="{{ __('Search make, model, plate…') }}" class="w-full rounded-xl border-0 bg-white/5 py-2 pl-9 pr-3 text-sm text-white placeholder-zinc-500 ring-1 ring-white/10 focus:ring-2 focus:ring-rx-accent" />
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-4 text-xs text-zinc-400">
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-rx-accent"></span>{{ __('Selected') }}</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-blue-300"></span>{{ __('Service Scheduled') }}</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-white/10"></span>{{ __('No Service Due') }}</span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2.5">
                        @foreach ($vehicles as $vehicle)
                            <button
                                type="button"
                                x-show="! q || meta[{{ $vehicle->id }}].includes(q.toLowerCase().trim())"
                                @click="sel = {{ $vehicle->id }}"
                                :class="sel === {{ $vehicle->id }}
                                    ? 'bg-rx-accent text-white shadow-lg shadow-rx-accent/40 -translate-y-0.5 ring-rx-accent'
                                    : '{{ $vehicle->nextBooking ? 'bg-rx-accent/25 text-blue-200 ring-rx-accent/60 hover:bg-rx-accent/40' : 'bg-white/5 text-zinc-400 ring-white/10 hover:bg-white/10 hover:text-white' }}'"
                                class="flex h-14 min-w-[5.5rem] flex-col items-center justify-center rounded-xl px-3 ring-1 transition"
                                title="{{ $vehicle->display_name }}"
                            >
                                <span class="font-mono text-xs font-semibold tracking-wide">{{ $vehicle->plate_number }}</span>
                                <span class="max-w-[7rem] truncate text-[10px] opacity-70">{{ $vehicle->make }} {{ $vehicle->model }}</span>
                            </button>
                        @endforeach
                        <a href="{{ route('vehicles.create') }}" class="flex h-14 min-w-[5.5rem] items-center justify-center rounded-xl border border-dashed border-white/15 text-zinc-500 transition hover:border-rx-accent hover:text-blue-400" title="{{ __('Add Vehicle') }}">
                            <x-icon name="plus" class="h-5 w-5" />
                        </a>
                    </div>

                    @foreach ($vehicles as $vehicle)
                        @php $wanted = $vehicle->services ?? []; @endphp
                        <div x-show="sel === {{ $vehicle->id }}" @if (! $loop->first) x-cloak @endif x-transition.opacity.duration.250ms>

                            <!-- Vehicle stage -->
                            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-[1fr_240px]">
                                <div class="min-w-0">
                                    <h3 class="text-2xl font-bold italic text-white">{{ ucfirst($vehicle->make) }} {{ ucfirst($vehicle->model) }} <span class="text-zinc-500">({{ __('Year') }} {{ $vehicle->year }})</span></h3>
                                    <p class="mt-1 text-sm text-zinc-400">{{ __('Car plate') }} <span class="font-mono font-semibold text-zinc-200">{{ $vehicle->plate_number }}</span></p>

                                    <div class="relative mt-4 flex h-56 items-center justify-center">
                                        <div class="absolute inset-x-0 bottom-3 h-16 -skew-x-12 rounded-2xl bg-white/[0.03]"></div>
                                        @if ($vehicle->image_url)
                                            <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->display_name }}" class="relative max-h-56 w-full object-contain drop-shadow-2xl" />
                                        @else
                                            <svg viewBox="0 0 420 180" class="relative w-full max-w-lg" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M40,120 L44,94 Q48,80 66,76 L122,70 L160,42 Q168,36 184,36 L292,36 Q308,36 318,47 L346,74 L364,80 Q378,85 379,100 L379,120 Q379,127 371,127 L339,127 M273,127 L139,127 M73,127 L47,127 Q40,127 40,120 Z" stroke="#a1a1aa" stroke-width="1.6" />
                                                <path d="M134,71 L166,46 Q172,42 181,42 L226,42 L226,71 Z M234,42 L289,42 Q299,42 307,50 L331,73 L234,73 Z" stroke="#71717a" stroke-width="1.4" />
                                                <path d="M226,71 L226,120 M160,80 L172,80 M250,82 L262,82" stroke="#71717a" stroke-width="1.2" />
                                                <path d="M139,120 L273,120" stroke="#1652F0" stroke-width="5" />
                                                <path d="M52,94 L64,94 L64,104" stroke="#1652F0" stroke-width="4" />
                                                <rect x="128" y="114" width="22" height="10" rx="2" fill="#1652F0" />
                                                @foreach ([106, 306] as $cx)
                                                    <circle cx="{{ $cx }}" cy="127" r="28" stroke="#a1a1aa" stroke-width="1.6" />
                                                    <circle cx="{{ $cx }}" cy="127" r="19" stroke="#71717a" stroke-width="1.2" />
                                                    @for ($s = 0; $s < 6; $s++)
                                                        <line x1="{{ $cx }}" y1="127" x2="{{ round($cx + 18 * cos($s * M_PI / 3), 1) }}" y2="{{ round(127 + 18 * sin($s * M_PI / 3), 1) }}" stroke="#52525b" stroke-width="1.2" />
                                                    @endfor
                                                    <circle cx="{{ $cx }}" cy="127" r="4" fill="#71717a" />
                                                @endforeach
                                            </svg>
                                        @endif
                                    </div>
                                </div>

                                <!-- Quick facts -->
                                <div class="flex flex-col justify-center gap-4">
                                    <a href="{{ route('vehicles.show', $vehicle) }}" class="group flex items-center justify-between gap-3 text-sm font-semibold text-white">
                                        {{ __('View full service history') }}
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rx-accent/20 text-blue-400 transition group-hover:bg-blue-500 group-hover:text-white">
                                            <x-icon name="arrow-right" class="h-4 w-4" />
                                        </span>
                                    </a>
                                    <dl class="space-y-3 text-sm">
                                        <div class="flex justify-between gap-3">
                                            <dt class="italic text-zinc-500">{{ __('Last service') }}</dt>
                                            <dd class="font-semibold text-zinc-200">{{ $vehicle->latestServiceRecord?->serviced_at->format('m/d/y') ?? __('No history') }}</dd>
                                        </div>
                                        <div class="flex justify-between gap-3">
                                            <dt class="italic text-zinc-500">{{ __('Next service') }}</dt>
                                            <dd class="font-semibold text-zinc-200">{{ $vehicle->nextBooking?->preferred_date->format('m/d/y') ?? __('Not scheduled') }}</dd>
                                        </div>
                                        <div class="flex justify-between gap-3">
                                            <dt class="italic text-zinc-500">{{ __('VIN') }}</dt>
                                            <dd class="truncate font-mono text-xs font-semibold text-zinc-200">{{ $vehicle->vin ?: '—' }}</dd>
                                        </div>
                                    </dl>
                                    <div class="flex items-center gap-2 border-t border-white/5 pt-4">
                                        <a href="{{ route('vehicles.history', $vehicle->qr_uuid) }}" target="_blank" title="{{ __('View QR / Public History') }}" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 text-zinc-300 transition hover:bg-white/10 hover:text-white">
                                            <x-icon name="qr" class="h-4.5 w-4.5" />
                                        </a>
                                        <a href="{{ route('vehicles.edit', $vehicle) }}" title="{{ __('Edit') }}" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 text-zinc-300 transition hover:bg-white/10 hover:text-white">
                                            <x-icon name="pencil" class="h-4.5 w-4.5" />
                                        </a>
                                        <span class="rounded-lg bg-white/5">
                                            <x-confirm-modal
                                                name="delete-vehicle-{{ $vehicle->id }}"
                                                :action="route('vehicles.destroy', $vehicle)"
                                                variant="icon"
                                                title="{{ __('Remove this vehicle?') }}"
                                                body="{{ __('This will permanently delete the vehicle and its QR code. This action cannot be undone.') }}"
                                                confirm-label="{{ __('Delete Vehicle') }}"
                                                trigger-label="{{ __('Delete') }}"
                                            />
                                        </span>
                                        <a href="{{ route('vehicles.show', $vehicle) }}" class="ml-auto inline-flex items-center gap-1.5 rounded-lg bg-white/5 px-3 py-2 text-xs font-semibold text-zinc-200 transition hover:bg-white/10">
                                            <x-icon name="eye" class="h-3.5 w-3.5" /> {{ __('View') }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Services wanted -->
                            <div class="mt-8 flex flex-wrap items-end justify-between gap-3">
                                <h3 class="text-xl font-bold text-white">{{ __('Services wanted for this vehicle') }}</h3>
                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="group flex items-center gap-2 text-sm font-semibold text-zinc-300 hover:text-white">
                                    {{ __('Update services') }}
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-rx-accent/20 text-blue-400 transition group-hover:bg-blue-500 group-hover:text-white">
                                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                    </span>
                                </a>
                            </div>
                            <div class="-mx-2 mt-4 flex gap-3 overflow-x-auto px-2 pb-3">
                                @foreach (\App\Models\ServiceBooking::SERVICE_TYPES as $service)
                                    @php $on = in_array($service, $wanted); @endphp
                                    <div @class([
                                        'flex h-36 w-36 shrink-0 flex-col justify-between rounded-2xl p-4 transition',
                                        'bg-rx-accent text-white shadow-xl shadow-rx-accent/30 -translate-y-1' => $on,
                                        'bg-white/[0.03] text-zinc-400 ring-1 ring-white/10' => ! $on,
                                    ])>
                                        <p class="text-sm font-semibold {{ $on ? 'text-white' : 'text-zinc-200' }}">{{ $service }}</p>
                                        <x-icon :name="$serviceIcons[$service] ?? 'wrench'" class="mx-auto h-8 w-8 {{ $on ? 'text-white' : 'text-zinc-600' }}" />
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span>{{ $on ? __('Wanted') : __('Not selected') }}</span>
                                            @if ($on)<x-icon name="check-circle" class="h-4 w-4" />@endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Overview panel -->
                <div class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">
                    @foreach ($vehicles as $vehicle)
                        @php
                            $wanted = $vehicle->services ?? [];
                            $segments = collect([
                                ['value' => $vehicle->service_records_count, 'color' => '#B4B4BC'],
                                ['value' => count($wanted), 'color' => '#1652F0'],
                                ['value' => $vehicle->nextBooking ? 1 : 0, 'color' => '#1652F0'],
                            ])->filter(fn ($s) => $s['value'] > 0)->values();
                            $segTotal = max($segments->sum('value'), 1);
                            $gap = $segments->count() > 1 ? 14 : 0;
                            $offset = 0;
                        @endphp
                        <div x-show="sel === {{ $vehicle->id }}" @if (! $loop->first) x-cloak @endif class="flex h-full flex-col">
                            <h2 class="text-right text-2xl font-bold italic text-white">{{ __('Service overview') }}</h2>

                            <!-- Ring -->
                            <div class="relative mx-auto mt-6 h-52 w-52">
                                <svg viewBox="0 0 200 200" class="h-full w-full -rotate-90">
                                    <circle cx="100" cy="100" r="80" fill="none" stroke="#ffffff" stroke-opacity=".06" stroke-width="12" />
                                    @foreach ($segments as $seg)
                                        @php $len = max($seg['value'] / $segTotal * $circumference - $gap, 2); @endphp
                                        <circle cx="100" cy="100" r="80" fill="none" stroke="{{ $seg['color'] }}" stroke-width="12" stroke-linecap="round"
                                            stroke-dasharray="{{ round($len, 2) }} {{ round($circumference, 2) }}"
                                            stroke-dashoffset="{{ round(-$offset, 2) }}" />
                                        @php $offset += $seg['value'] / $segTotal * $circumference; @endphp
                                    @endforeach
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-sm text-zinc-400">{{ __('Service Records') }}</span>
                                    <span class="text-5xl font-bold text-white">{{ $vehicle->service_records_count }}</span>
                                </div>
                            </div>

                            <!-- Rows -->
                            <div class="mt-8 space-y-3">
                                <div class="flex items-center justify-between rounded-xl bg-white/[0.04] px-4 py-3 ring-1 ring-rx-line">
                                    <span class="flex items-center gap-3 text-sm font-medium text-zinc-200"><span class="h-3 w-3 rounded-sm bg-[#B4B4BC]"></span>{{ __('Last Service') }}</span>
                                    <span class="text-sm font-semibold text-white">{{ $vehicle->latestServiceRecord?->serviced_at->format('M j, Y') ?? '—' }}</span>
                                </div>
                                <div class="flex items-center justify-between rounded-xl bg-white/[0.04] px-4 py-3 ring-1 ring-rx-line">
                                    <span class="flex items-center gap-3 text-sm font-medium text-zinc-200"><span class="h-3 w-3 rounded-sm bg-rx-accent"></span>{{ __('Next Service') }}</span>
                                    <span class="text-sm font-semibold text-white">{{ $vehicle->nextBooking?->preferred_date->format('M j, Y') ?? '—' }}</span>
                                </div>
                                @foreach ($wanted as $service)
                                    <div class="flex items-center justify-between rounded-xl bg-white/[0.04] px-4 py-3 ring-1 ring-rx-line">
                                        <span class="flex items-center gap-3 text-sm font-medium text-zinc-200"><span class="h-3 w-3 rounded-sm bg-rx-accent"></span>{{ $service }}</span>
                                        <x-icon :name="$serviceIcons[$service] ?? 'wrench'" class="h-4 w-4 text-blue-400" />
                                    </div>
                                @endforeach

                                <a href="{{ route('vehicles.edit', $vehicle) }}" class="flex items-center justify-center rounded-xl border border-dashed border-white/15 py-3 text-sm text-zinc-500 transition hover:border-rx-accent hover:text-blue-400">
                                    + {{ count($wanted) ? __('Edit services') : __('Add service') }}
                                </a>
                            </div>

                            <a href="{{ route('bookings.create', ['vehicle_id' => $vehicle->id]) }}" class="mt-8 block rounded-2xl bg-rx-accent py-4 text-center text-sm font-bold uppercase tracking-widest text-white shadow-xl shadow-rx-accent/30 transition hover:bg-rx-accent-hover">
                                {{ __('Book a Service') }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($vehicles->hasPages())
                <div class="rounded-2xl bg-rx-surface px-4 py-3 ring-1 ring-rx-line">
                    {{ $vehicles->links() }}
                </div>
            @endif
        @endif
    </div>
</x-dashboard-layout>
