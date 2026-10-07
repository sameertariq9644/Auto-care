<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-native="dark">
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
    <body class="font-sans antialiased bg-rx-ink text-zinc-100">
        <div class="flex min-h-screen gap-4 p-4">

            <!-- Branding panel -->
            <aside class="relative hidden w-1/2 flex-col justify-between overflow-hidden rounded-3xl bg-rx-accent p-10 shadow-xl shadow-rx-accent/20 lg:flex xl:p-12">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute -bottom-32 -left-16 h-[26rem] w-[26rem] rounded-full bg-black/20 blur-3xl"></div>
                    <div class="absolute inset-0 [background-image:linear-gradient(to_right,rgba(255,255,255,0.06)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.06)_1px,transparent_1px)] [background-size:44px_44px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]"></div>
                </div>

                <a href="/" class="relative flex items-center gap-2.5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                        <x-application-logo class="h-5 w-auto fill-current text-white" />
                    </span>
                    <span class="text-lg font-bold italic tracking-tight text-white">AutoCare Pro</span>
                </a>

                <div class="relative">
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Workshop, simplified') }}</p>
                    <h2 class="mt-3 text-4xl font-extrabold italic leading-tight tracking-tight text-white xl:text-5xl">
                        {{ __('Every vehicle\'s story,') }}<br>{{ __('one scan away.') }}
                    </h2>

                    <ul class="mt-8 space-y-3">
                        @foreach ([
                            ['icon' => 'calendar', 'text' => __('Book services online in a couple of taps.')],
                            ['icon' => 'wrench', 'text' => __('Mechanics, inventory and invoicing stay in sync.')],
                            ['icon' => 'qr', 'text' => __('Every vehicle gets a QR code with its full history.')],
                        ] as $feature)
                            <li class="flex items-center gap-3 rounded-2xl bg-white/10 p-3 ring-1 ring-white/15 backdrop-blur">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white">
                                    <x-icon :name="$feature['icon']" class="h-5 w-5" />
                                </span>
                                <span class="text-sm font-medium text-white">{{ $feature['text'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="relative text-xs text-white/70">&copy; {{ now()->year }} AutoCare Pro</p>
            </aside>

            <!-- Form panel -->
            <main class="relative flex flex-1 flex-col">
                <div class="flex items-center justify-between px-2 py-2">
                    <a href="/" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium text-zinc-400 transition hover:bg-white/5 hover:text-white">
                        <x-icon name="chevron-right" class="h-4 w-4 rotate-180" /> {{ __('Back to home') }}
                    </a>
                    <x-theme-toggle class="text-zinc-400 ring-1 ring-rx-line hover:bg-white/5 hover:text-white" />
                </div>

                <div class="flex flex-1 items-center justify-center py-8">
                    <div class="w-full max-w-md">
                        <a href="/" class="mb-6 flex items-center justify-center gap-2.5 lg:hidden">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rx-accent shadow-lg shadow-rx-accent/30">
                                <x-application-logo class="h-5 w-auto fill-current text-white" />
                            </span>
                            <span class="text-lg font-bold italic tracking-tight text-white">AutoCare <span class="text-blue-400">Pro</span></span>
                        </a>

                        <div class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">
                            @isset($heading)
                                <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">{{ $heading }}</h1>
                            @endisset
                            @isset($subheading)
                                <p class="mt-2 text-sm text-zinc-400">{{ $subheading }}</p>
                            @endisset

                            <div class="{{ isset($heading) ? 'mt-7' : '' }}">
                                {{ $slot }}
                            </div>
                        </div>

                        @isset($footer)
                            <p class="mt-6 text-center text-sm text-zinc-500">{{ $footer }}</p>
                        @endisset
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
