@php
    $specs = [
        ['icon' => 'car', 'label' => __('Make'), 'value' => $vehicle->make],
        ['icon' => 'sparkles', 'label' => __('Model'), 'value' => $vehicle->model],
        ['icon' => 'calendar', 'label' => __('Year'), 'value' => $vehicle->year],
        ['icon' => 'qr', 'label' => __('Plate Number'), 'value' => $vehicle->plate_number, 'mono' => true],
        ['icon' => 'shield-check', 'label' => __('VIN'), 'value' => $vehicle->vin ?? '—', 'mono' => true],
        ['icon' => 'wrench', 'label' => __('Service Records'), 'value' => $serviceRecords->count()],
    ];
@endphp

<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('vehicles.index') }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25" title="{{ __('Back to My Vehicles') }}">
                        <x-icon name="chevron-right" class="h-5 w-5 rotate-180" />
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Vehicle Details') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">{{ $vehicle->display_name }}</h1>
                        <p class="mt-0.5 font-mono text-sm tracking-wider text-white/80">{{ $vehicle->plate_number }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rx-accent shadow-lg transition hover:bg-blue-50">
                        <x-icon name="pencil" class="h-4 w-4" /> {{ __('Edit') }}
                    </a>
                    <x-confirm-modal
                        name="delete-vehicle"
                        :action="route('vehicles.destroy', $vehicle)"
                        title="{{ __('Remove this vehicle?') }}"
                        body="{{ __('This will permanently delete the vehicle and its QR code. This action cannot be undone.') }}"
                        confirm-label="{{ __('Delete Vehicle') }}"
                        trigger-label="{{ __('Delete') }}"
                    />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_340px]">

            <!-- Showcase + specs -->
            <div class="min-w-0 rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">
                <div class="relative flex h-72 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-b from-white/[0.04] to-transparent">
                    <span class="pointer-events-none absolute inset-x-0 bottom-3 select-none text-center font-serif text-5xl uppercase leading-none text-white/[0.05] sm:text-7xl">
                        {{ $vehicle->make }} {{ $vehicle->model }}
                    </span>
                    <div class="absolute inset-x-10 bottom-6 h-10 rounded-[50%] border border-rx-accent/40 bg-rx-accent/[0.04]"></div>
                    @if ($vehicle->image_url)
                        <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->display_name }}" class="relative max-h-64 w-full object-contain drop-shadow-2xl" />
                    @else
                        <x-car-illustration uid="show" class="relative w-full max-w-md" />
                    @endif
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($specs as $spec)
                        <div class="flex items-center gap-3 rounded-2xl bg-white/[0.03] p-4 ring-1 ring-rx-line">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5 text-zinc-300">
                                <x-icon :name="$spec['icon']" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] uppercase tracking-wide text-zinc-500">{{ $spec['label'] }}</p>
                                <p class="truncate text-sm font-semibold text-white {{ ! empty($spec['mono']) ? 'font-mono' : '' }}">{{ $spec['value'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    <p class="text-sm font-medium text-zinc-300">{{ __('Services Wanted') }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($vehicle->services ?? [] as $service)
                            <span class="rounded-full bg-rx-accent/15 px-3 py-1 text-xs font-medium text-blue-400 ring-1 ring-inset ring-rx-accent/20">{{ $service }}</span>
                        @empty
                            <span class="text-sm text-zinc-500">—</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- QR card -->
            <div class="flex flex-col items-center rounded-3xl bg-rx-surface p-6 text-center ring-1 ring-rx-line sm:p-8">
                <h2 class="text-xl font-bold italic text-white">{{ __('Service Passport') }}</h2>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Scan to view service history') }}</p>

                @if ($qrCodeUrl)
                    <div class="mt-6 rounded-3xl bg-rx-accent p-[2px] shadow-xl shadow-rx-accent/40">
                        <div class="rounded-[22px] bg-white p-3">
                            <img src="{{ $qrCodeUrl }}" alt="{{ __('QR code') }}" class="h-44 w-44">
                        </div>
                    </div>

                    <a href="{{ $qrCodeUrl }}" download class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rx-accent px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                        <x-icon name="download" class="h-4 w-4" /> {{ __('Download QR Code') }}
                    </a>
                @else
                    <div class="mt-6 flex h-44 w-44 items-center justify-center rounded-3xl bg-white/5 text-zinc-600">
                        <x-icon name="qr" class="h-12 w-12" />
                    </div>
                @endif
            </div>
        </div>

        <!-- Service history -->
        <div class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">{{ __('Service History') }}</h3>
                <span class="rounded-full bg-white/5 px-3 py-1 text-xs text-zinc-400">{{ $serviceRecords->count() }}</span>
            </div>

            @forelse ($serviceRecords as $record)
                <div class="relative mt-5 pl-8">
                    @unless ($loop->last)
                        <span class="absolute left-[11px] top-7 h-[calc(100%+0.25rem)] w-px bg-white/10"></span>
                    @endunless
                    <span class="absolute left-0 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-rx-accent/15 ring-1 ring-rx-accent/30">
                        <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                    </span>

                    <div class="rounded-2xl bg-white/[0.03] p-4 ring-1 ring-rx-line">
                        <div class="flex flex-wrap justify-between gap-2 text-sm">
                            <span class="font-semibold text-white">{{ $record->serviced_at->format('M j, Y') }}</span>
                            @if ($record->mechanic)
                                <span class="text-zinc-500">{{ __('Mechanic') }}: <span class="text-zinc-300">{{ $record->mechanic->user->name }}</span></span>
                            @endif
                        </div>
                        <p class="mt-2 text-sm text-zinc-300">{{ $record->description }}</p>

                        @if ($record->items->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($record->items as $item)
                                    <span class="rounded-lg bg-white/5 px-2.5 py-1 text-xs text-zinc-300 ring-1 ring-white/10">{{ $item->inventoryItem->name }} × {{ $item->quantity_used }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="mt-4 flex flex-col items-center py-8 text-center">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 text-zinc-500">
                        <x-icon name="wrench" class="h-5 w-5" />
                    </span>
                    <p class="mt-2 text-sm text-zinc-400">{{ __('No service history recorded yet.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</x-dashboard-layout>
