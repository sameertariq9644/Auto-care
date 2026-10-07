<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-native="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $vehicle->make }} {{ $vehicle->model }} — Service History</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @include('components.partials.theme-head')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased bg-slate-50">
        <div class="min-h-screen">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex h-16 max-w-3xl items-center justify-between px-4 sm:px-6">
                    <a href="/" class="flex items-center gap-2">
                        <x-application-logo class="h-7 w-auto fill-current text-indigo-600" />
                        <span class="text-base font-semibold tracking-tight text-slate-900">AutoCare <span class="text-indigo-600">Pro</span></span>
                    </a>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                        <x-icon name="qr" class="h-3.5 w-3.5" />
                        {{ __('Verified Vehicle Record') }}
                    </span>
                </div>
            </header>

            <main class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
                <!-- Vehicle identity card -->
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-8 text-white">
                        <p class="text-xs font-medium uppercase tracking-wider text-indigo-100">{{ __('Digital Vehicle Passport') }}</p>
                        <h1 class="mt-1 text-3xl font-bold tracking-tight">
                            {{ $vehicle->display_name }}
                        </h1>
                    </div>
                    <div class="grid grid-cols-2 gap-4 px-6 py-5 sm:grid-cols-3">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Plate') }}</p>
                            <p class="mt-0.5 font-mono text-sm font-semibold text-slate-900">{{ $vehicle->plate_number }}</p>
                        </div>
                        @if ($vehicle->vin)
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('VIN') }}</p>
                                <p class="mt-0.5 font-mono text-sm font-semibold text-slate-900">{{ $vehicle->vin }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Service Records') }}</p>
                            <p class="mt-0.5 text-sm font-semibold text-slate-900">{{ $serviceRecords->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <h2 class="mb-6 mt-10 text-lg font-semibold text-slate-900">{{ __('Service History') }}</h2>

                @forelse ($serviceRecords as $record)
                    <div class="relative flex gap-4 pb-8 last:pb-0">
                        @if (! $loop->last)
                            <span class="absolute left-[15px] top-8 h-[calc(100%-1rem)] w-px bg-slate-200" aria-hidden="true"></span>
                        @endif

                        <span class="relative z-10 mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                            <x-icon name="wrench" class="h-4 w-4" />
                        </span>

                        <div class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-500">
                                <span class="font-medium text-slate-700">{{ $record->serviced_at->format('F j, Y') }}</span>
                                @if ($record->mechanic)
                                    <span>{{ __('Serviced by') }} {{ $record->mechanic->user->name }}</span>
                                @endif
                            </div>
                            <p class="mt-2 text-slate-900">{{ $record->description }}</p>

                            @if ($record->items->isNotEmpty())
                                <ul class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($record->items as $item)
                                        <li class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                            {{ $item->inventoryItem->name }} &times; {{ $item->quantity_used }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center rounded-2xl border border-dashed border-slate-300 bg-white py-12 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <x-icon name="inbox" class="h-6 w-6" />
                        </span>
                        <p class="mt-3 text-sm font-medium text-slate-700">{{ __('No service history recorded yet.') }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Completed services for this vehicle will appear here.') }}</p>
                    </div>
                @endforelse
            </main>

            <footer class="border-t border-slate-200 py-8 text-center text-xs text-slate-400">
                {{ __('Powered by AutoCare Pro') }} &middot; {{ now()->year }}
            </footer>
        </div>
        <x-theme-toggle class="fixed bottom-5 right-5 z-50 !h-11 !w-11 !rounded-full bg-rx-accent text-white shadow-lg shadow-rx-accent/30 hover:bg-rx-accent-hover" />
    </body>
</html>
