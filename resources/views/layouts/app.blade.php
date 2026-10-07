<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-native="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AutoCare Pro') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @include('components.partials.theme-head')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-slate-50">

            <!-- Mobile top bar -->
            <div class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 lg:hidden">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <x-application-logo class="h-7 w-auto fill-current text-indigo-600" />

                    <span class="text-base font-semibold tracking-tight text-slate-900">
                        AutoCare <span class="text-indigo-600">Pro</span>
                    </span>
                </a>

                <div class="flex items-center gap-1">
                    <x-theme-toggle class="rounded-md text-slate-500 hover:bg-slate-50 hover:text-slate-900" />
                    <button
                        @click="sidebarOpen = true"
                        class="-mr-2 p-2 text-slate-500 hover:text-slate-900"
                        aria-label="{{ __('Open menu') }}"
                    >
                        <x-icon name="menu" class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <!-- Mobile slide-over sidebar -->
            <div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden">

                <div
                    x-show="sidebarOpen"
                    x-transition:enter="transition-opacity ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="sidebarOpen = false"
                    class="fixed inset-0 bg-slate-900/50"
                ></div>

                <div
                    x-show="sidebarOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    class="fixed inset-y-0 left-0 w-72 bg-white shadow-xl"
                >
                    <div class="flex justify-end p-3">
                        <button
                            @click="sidebarOpen = false"
                            class="p-2 text-slate-400 hover:text-slate-900"
                            aria-label="{{ __('Close menu') }}"
                        >
                            <x-icon name="x-mark" class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="h-[calc(100%-3.5rem)]">
                        @include('layouts.navigation')
                    </div>
                </div>
            </div>

            <!-- Desktop sidebar -->
            <aside class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col lg:border-r lg:border-slate-200 lg:bg-white">
                @include('layouts.navigation')
            </aside>

            <!-- Main content -->
            <div class="flex min-h-screen flex-col lg:pl-64">

                @isset($header)
                    <header class="border-b border-slate-200 bg-white">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1">

                    @php
                        // These slugs are handled inline by Breeze's own profile forms.
                        $reservedStatuses = [
                            'profile-updated',
                            'password-updated',
                            'verification-link-sent',
                        ];
                    @endphp

                    @if (session('status') && ! in_array(session('status'), $reservedStatuses))
                        <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif

                    {{ $slot }}

                </main>
            </div>
        </div>
    </body>
</html>