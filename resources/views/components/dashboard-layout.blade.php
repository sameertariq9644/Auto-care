@php
    $navUser = Auth::user();

    if ($navUser->hasRole('admin')) {
        $pendingCount = \App\Models\ServiceBooking::where('status', \App\Models\ServiceBooking::STATUS_PENDING)->count();
        $unpaidCount = \App\Models\Invoice::where('status', \App\Models\Invoice::STATUS_UNPAID)->count();

        $links = [
            ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'home', 'label' => __('Dashboard')],
            ['route' => 'admin.bookings.index', 'match' => 'admin.bookings.*', 'icon' => 'calendar', 'label' => __('Bookings'), 'badge' => $pendingCount],
            ['route' => 'admin.mechanics.index', 'match' => 'admin.mechanics.*', 'icon' => 'users', 'label' => __('Mechanics')],
            ['route' => 'admin.inventory.index', 'match' => 'admin.inventory.*', 'icon' => 'archive', 'label' => __('Inventory')],
            ['route' => 'admin.invoices.index', 'match' => 'admin.invoices.*', 'icon' => 'receipt', 'label' => __('Invoices'), 'badge' => $unpaidCount],
        ];
        $quickLink = ['route' => 'admin.mechanics.create', 'icon' => 'plus', 'label' => __('Add Mechanic')];
    } elseif ($navUser->hasRole('mechanic')) {
        $activeJobs = $navUser->mechanic?->bookings()
            ->whereIn('status', [\App\Models\ServiceBooking::STATUS_CONFIRMED, \App\Models\ServiceBooking::STATUS_IN_PROGRESS])
            ->count() ?? 0;

        $links = [
            ['route' => 'mechanic.dashboard', 'match' => 'mechanic.dashboard', 'icon' => 'home', 'label' => __('Dashboard')],
            ['route' => 'mechanic.bookings.index', 'match' => 'mechanic.bookings.*', 'icon' => 'wrench', 'label' => __('My Jobs'), 'badge' => $activeJobs],
        ];
        $quickLink = ['route' => 'mechanic.bookings.index', 'icon' => 'clipboard-check', 'label' => __('Job Queue')];
    } else {
        $unpaidCount = $navUser->invoices()->where('status', \App\Models\Invoice::STATUS_UNPAID)->count();

        $links = [
            ['route' => 'customer.dashboard', 'match' => '*dashboard', 'icon' => 'home', 'label' => __('Dashboard')],
            ['route' => 'vehicles.index', 'match' => 'vehicles.*', 'icon' => 'truck', 'label' => __('My Vehicles')],
            ['route' => 'bookings.index', 'match' => 'bookings.*', 'icon' => 'calendar', 'label' => __('My Bookings')],
            ['route' => 'invoices.index', 'match' => 'invoices.*', 'icon' => 'receipt', 'label' => __('My Invoices'), 'badge' => $unpaidCount],
        ];
        $quickLink = ['route' => 'bookings.create', 'icon' => 'plus', 'label' => __('Book a Service')];
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-native="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AutoCare Pro') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @include('components.partials.theme-head')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased bg-rx-ink text-zinc-100">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            <!-- Mobile top bar -->
            <div class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-white/5 bg-rx-ink/90 px-4 backdrop-blur lg:hidden">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <x-application-logo class="h-7 w-auto fill-current text-blue-400" />
                    <span class="text-base font-bold tracking-tight text-white">AutoCare <span class="text-blue-400">Pro</span></span>
                </a>
                <div class="flex items-center gap-1">
                    <x-theme-toggle class="text-zinc-400 hover:bg-white/5 hover:text-white" />
                    <button @click="sidebarOpen = true" class="-mr-2 p-2 text-zinc-400 hover:text-white" aria-label="{{ __('Open menu') }}">
                        <x-icon name="menu" class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <!-- Mobile slide-over -->
            <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden">
                <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 bg-black/70"></div>
                <div
                    x-show="sidebarOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    class="fixed inset-y-0 left-0 w-72 bg-rx-surface shadow-2xl"
                >
                    <button @click="sidebarOpen = false" class="absolute right-3 top-4 p-2 text-zinc-500 hover:text-white" aria-label="{{ __('Close menu') }}">
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                    @include('components.partials.dashboard-sidebar')
                </div>
            </div>

            <!-- Desktop sidebar -->
            <aside class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col lg:p-4">
                <div class="h-full rounded-3xl bg-rx-surface ring-1 ring-rx-line">
                    @include('components.partials.dashboard-sidebar')
                </div>
            </aside>

            <main class="lg:pl-64">
                @if (session('status') && ! in_array(session('status'), ['profile-updated', 'password-updated', 'verification-link-sent']))
                    <div class="px-4 pt-6 sm:px-6 lg:px-4">
                        <div class="rounded-xl border border-rx-accent/30 bg-rx-accent/10 px-4 py-3 text-sm font-medium text-blue-300">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
