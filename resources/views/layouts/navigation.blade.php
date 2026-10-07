@php
    $navUser = Auth::user();
@endphp

<div class="flex h-full flex-col bg-white">
    <div class="flex h-16 shrink-0 items-center gap-2 border-b border-slate-200 px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <x-application-logo class="h-7 w-auto fill-current text-indigo-600" />
            <span class="text-base font-semibold tracking-tight text-slate-900">AutoCare <span class="text-indigo-600">Pro</span></span>
        </a>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <x-nav-item :href="route('dashboard')" :active="request()->routeIs('*dashboard')" icon="home">
            {{ __('Dashboard') }}
        </x-nav-item>

        @role('admin')
            <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Workshop') }}</p>
            <x-nav-item :href="route('admin.mechanics.index')" :active="request()->routeIs('admin.mechanics.*')" icon="users">
                {{ __('Mechanics') }}
            </x-nav-item>
            <x-nav-item :href="route('admin.bookings.index')" :active="request()->routeIs('admin.bookings.*')" icon="calendar">
                {{ __('Bookings') }}
            </x-nav-item>
            <x-nav-item :href="route('admin.inventory.index')" :active="request()->routeIs('admin.inventory.*')" icon="archive">
                {{ __('Inventory') }}
            </x-nav-item>
            <x-nav-item :href="route('admin.invoices.index')" :active="request()->routeIs('admin.invoices.*')" icon="receipt">
                {{ __('Invoices') }}
            </x-nav-item>
        @endrole

        @role('mechanic')
            <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Work') }}</p>
            <x-nav-item :href="route('mechanic.bookings.index')" :active="request()->routeIs('mechanic.bookings.*')" icon="wrench">
                {{ __('My Jobs') }}
            </x-nav-item>
        @endrole

        @role('customer')
            <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('My Account') }}</p>
            <x-nav-item :href="route('vehicles.index')" :active="request()->routeIs('vehicles.*')" icon="truck">
                {{ __('My Vehicles') }}
            </x-nav-item>
            <x-nav-item :href="route('bookings.index')" :active="request()->routeIs('bookings.*')" icon="calendar">
                {{ __('My Bookings') }}
            </x-nav-item>
            <x-nav-item :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" icon="receipt">
                {{ __('My Invoices') }}
            </x-nav-item>
        @endrole
    </nav>

    <div class="shrink-0 border-t border-slate-200 p-3">
        <div class="flex items-center gap-2 rounded-md px-1 py-1">
            <a href="{{ route('profile.edit') }}" class="flex min-w-0 flex-1 items-center gap-3 rounded-md px-1 py-1 transition hover:bg-slate-50">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">
                    {{ str($navUser->name)->substr(0, 1)->upper() }}
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-medium text-slate-900">{{ $navUser->name }}</span>
                    <span class="block truncate text-xs text-slate-500">{{ $navUser->email }}</span>
                </span>
            </a>

            <x-theme-toggle class="rounded-md text-slate-400 hover:bg-slate-50 hover:text-slate-900" />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="{{ __('Log out') }}" class="flex h-9 w-9 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-50 hover:text-slate-900">
                    <x-icon name="logout" class="h-5 w-5" />
                </button>
            </form>
        </div>
    </div>
</div>
