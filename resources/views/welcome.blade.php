<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-native="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'AutoCare Pro') }} — Workshop &amp; Vehicle Service Management</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@800;900&family=Poppins:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @include('components.partials.theme-head')
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            html { scroll-behavior: smooth; }

            .rx-eyebrow {
                font-family: theme('fontFamily.label');
                font-size: 0.8125rem;
                font-weight: 600;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: #1652F0;
            }

            .rx-display {
                font-family: theme('fontFamily.display');
                text-transform: uppercase;
                line-height: 0.96;
                letter-spacing: -0.02em;
            }

            .rx-label {
                font-family: theme('fontFamily.label');
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            /* Sticky header: transparent over hero, solid once scrolled */
            #rx-header {
                background: transparent;
                border-bottom: 1px solid transparent;
                transition: background-color 0.25s ease, border-color 0.25s ease;
            }
            #rx-header.rx-scrolled {
                background-color: rgba(10, 10, 10, 0.92);
                backdrop-filter: blur(8px);
                border-bottom-color: #2A2A2A;
            }

            details > summary::-webkit-details-marker { display: none; }

            .reveal {
                opacity: 0;
                transform: translateY(24px);
                transition: opacity 0.6s ease-out, transform 0.6s ease-out, border-color 0.3s ease-out;
            }
            .reveal.reveal-visible {
                opacity: 1;
                transform: translateY(0);
            }
            @media (prefers-reduced-motion: reduce) {
                .reveal, html { transition: none; scroll-behavior: auto; }
                .reveal { opacity: 1; transform: none; }
            }
        </style>
    </head>
    <body class="font-label antialiased bg-rx-ink text-white">

        <!-- Header -->
        <header id="rx-header" class="fixed inset-x-0 top-0 z-50">
            <div class="max-w-[1440px] mx-auto px-6 lg:px-10 h-20 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2.5 shrink-0">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" aria-hidden="true">
                        <path d="M15 1.5 27.5 8.5V21.5L15 28.5 2.5 21.5V8.5L15 1.5Z" stroke="#1652F0" stroke-width="2"/>
                        <path d="M15 9 20.5 12.25V18.75L15 22 9.5 18.75V12.25L15 9Z" fill="#1652F0"/>
                    </svg>
                    <span class="rx-label text-[15px] font-semibold text-white">AutoCare<span class="text-rx-accent">/</span>Pro</span>
                </a>

                <nav class="hidden lg:flex items-center gap-9 rx-label text-[13px] font-medium text-white">
                    <a href="/" class="hover:text-rx-accent transition-colors">Home</a>
                    <a href="#services" class="hover:text-rx-accent transition-colors">Services</a>
                    <a href="#about" class="hover:text-rx-accent transition-colors">About</a>
                    <a href="#contact" class="hover:text-rx-accent transition-colors">Contact</a>

                    <div class="relative group">
                        <button type="button" class="flex items-center gap-1.5 hover:text-rx-accent transition-colors">
                            Pages
                            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </button>
                        <div class="invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-opacity absolute left-1/2 -translate-x-1/2 top-full pt-3 w-44">
                            <div class="bg-rx-surface border border-rx-line rounded-sm py-2">
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="block px-4 py-2 text-[12px] hover:text-rx-accent hover:bg-white/5 transition-colors">Log In</a>
                                @endif
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="block px-4 py-2 text-[12px] hover:text-rx-accent hover:bg-white/5 transition-colors">Register</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </nav>

                <div class="flex items-center gap-5">
                    <x-theme-toggle class="-mx-2 text-white hover:text-rx-accent" />
                    @auth
                        <a href="{{ url('/dashboard') }}" class="rx-label hidden sm:inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-rx-accent text-white text-[13px] font-semibold hover:bg-rx-accent-hover transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rx-label hidden sm:inline-flex text-[13px] font-medium text-white hover:text-rx-accent transition-colors">
                            Log In
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rx-label inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-rx-accent text-white text-[13px] font-semibold hover:bg-rx-accent-hover transition-colors">
                                Get Started
                            </a>
                        @endif
                    @endauth

                    <button type="button" id="rx-menu-btn" class="lg:hidden flex flex-col gap-1.5 w-6" aria-label="Open menu" aria-expanded="false">
                        <span class="h-[2px] w-full bg-white"></span>
                        <span class="h-[2px] w-full bg-white"></span>
                    </button>
                </div>
            </div>

            <div id="rx-mobile-menu" class="hidden lg:hidden border-t border-rx-line bg-rx-ink px-6 py-6 flex flex-col gap-5 rx-label text-sm font-medium">
                <a href="/" class="hover:text-rx-accent">Home</a>
                <a href="#services" class="hover:text-rx-accent">Services</a>
                <a href="#about" class="hover:text-rx-accent">About</a>
                <a href="#contact" class="hover:text-rx-accent">Contact</a>
                @guest
                    <a href="{{ route('login') }}" class="hover:text-rx-accent">Log In</a>
                @endguest
            </div>
        </header>

        @php
            $services = [
                [
                    'title' => 'Car Engine Repair',
                    'image' => 'images/services/engine-repair.webp',
                    'icon' => '<path d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.94.777" />',
                    'text' => 'Complete engine diagnostics and repair. We fix overheating, oil leaks, misfires and performance issues to keep your engine running smoothly.',
                    'includes' => ['Computer diagnostics & fault-code scan', 'Spark plug, ignition coil & sensor replacement', 'Gasket, seal & oil leak repair', 'Cooling system, radiator & thermostat repair'],
                    'signs' => 'Check-engine light, rough idle, knocking sounds, blue or black exhaust smoke, or the temperature gauge running high.',
                ],
                [
                    'title' => 'Tire & Wheel Repair',
                    'image' => 'images/services/tire-wheel-repair.jpg',
                    'icon' => '<circle cx="12" cy="12" r="9" /><circle cx="12" cy="12" r="3.5" /><path d="M12 3v5.5M12 15.5V21M3 12h5.5M15.5 12H21" />',
                    'text' => 'Puncture repair, tire replacement, wheel balancing and alignment for better grip, smoother handling and longer tire life.',
                    'includes' => ['Puncture plug & patch repair', 'New tire fitting & computerised wheel balancing', 'Wheel alignment check & adjustment', 'Tire rotation & pressure check'],
                    'signs' => 'Car pulls to one side, steering wheel vibrates at speed, uneven tread wear, or tread worn close to the 1.6 mm legal limit.',
                ],
                [
                    'title' => 'Wiper Change & Repair',
                    'image' => 'images/services/wiper-repair.jpg',
                    'icon' => '<path d="M3 19a13 13 0 0 1 18 0" /><path d="M12 19 6 9" /><circle cx="12" cy="19" r="1" />',
                    'text' => 'We replace worn blades and repair wiper motors so you get a clear view in rain, dust and every season.',
                    'includes' => ['Front & rear wiper blade replacement', 'Wiper motor & linkage repair', 'Washer pump, jets & hose repair', 'Washer fluid top-up'],
                    'signs' => 'Streaks or smears on the glass, squeaking or chattering, blades skipping, or cracked rubber. Most blades need changing every 6–12 months.',
                ],
                [
                    'title' => 'Car Battery Repair',
                    'image' => 'images/services/battery-repair.jpg',
                    'icon' => '<rect x="3" y="7" width="18" height="12" rx="1.5" /><path d="M7 7V5h2v2M15 7V5h2v2M6.5 13h3M8 11.5v3M14.5 13h3" />',
                    'text' => 'Battery testing, charging, terminal cleaning and replacement, so your car starts reliably every time.',
                    'includes' => ['Battery health & load test', 'Charging system & alternator check', 'Terminal cleaning & corrosion removal', 'Battery replacement & fitting'],
                    'signs' => 'Slow engine crank, dim headlights, battery warning light, or white/green corrosion on the terminals. Most batteries last 3–5 years.',
                ],
                [
                    'title' => 'Gearbox Repair',
                    'image' => 'images/services/gearbox-repair.webp',
                    'icon' => '<circle cx="6" cy="5" r="1.5" /><circle cx="12" cy="5" r="1.5" /><circle cx="18" cy="5" r="1.5" /><circle cx="6" cy="19" r="1.5" /><circle cx="12" cy="19" r="1.5" /><path d="M6 6.5v11M12 6.5v11M18 6.5V12H6" />',
                    'text' => 'Manual and automatic gearbox repair, clutch service and transmission oil change for smooth, quiet gear shifts.',
                    'includes' => ['Manual & automatic transmission diagnostics', 'Clutch plate, pressure plate & bearing replacement', 'Transmission fluid change', 'Gear linkage & mount repair'],
                    'signs' => 'Grinding or slipping gears, delayed engagement, a burning smell, or red/brown fluid leaking under the car.',
                ],
                [
                    'title' => 'Car Check Up',
                    'image' => 'images/services/car-check-up.png',
                    'icon' => '<path d="M9 5H7.5A1.5 1.5 0 0 0 6 6.5v13A1.5 1.5 0 0 0 7.5 21h9a1.5 1.5 0 0 0 1.5-1.5v-13A1.5 1.5 0 0 0 16.5 5H15" /><rect x="9" y="3" width="6" height="4" rx="1" /><path d="m9 13.5 2 2 4-4" />',
                    'text' => 'A full multi-point inspection of brakes, suspension, fluids, lights and underbody. Catch small problems before they get costly.',
                    'includes' => ['Brake pad, disc & fluid inspection', 'Suspension & steering check', 'Engine oil, coolant & fluid levels', 'Lights, belts, hoses & underbody check'],
                    'signs' => 'Before a long trip, when buying a used car, or every 10,000 km / 6 months as routine care.',
                ],
            ];

            $highlights = [
                ['title' => 'Online Booking', 'icon' => '<rect x="3" y="5" width="18" height="16" rx="2" /><path d="M3 10h18M8 3v4M16 3v4" />'],
                ['title' => 'Written Quotes', 'icon' => '<path d="M7 3h7l5 5v13H7z" /><path d="M14 3v5h5M10 13h6M10 17h6" />'],
                ['title' => 'Live Progress', 'icon' => '<circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />'],
                ['title' => 'QR Service History', 'icon' => '<rect x="4" y="4" width="6" height="6" /><rect x="14" y="4" width="6" height="6" /><rect x="4" y="14" width="6" height="6" /><path d="M14 14h2v2h-2zM18 18h2v2h-2zM14 18h2M18 14h2" />'],
            ];

            $steps = [
                ['title' => 'Book Online', 'text' => 'Pick a service and a time that suits you — no phone queues.'],
                ['title' => 'Inspect & Quote', 'text' => 'A mechanic checks your car and sends a clear written quote.'],
                ['title' => 'Repair & Update', 'text' => 'You approve the work and follow its progress from your account.'],
                ['title' => 'Pay & Drive', 'text' => 'Collect your car, pay the invoice, and the visit is saved to its history.'],
            ];

            $values = [
                ['title' => 'Honest Pricing', 'text' => 'Written quotes before any work starts. You only pay for work you approve.', 'icon' => '<path d="M12 3v18M16.5 7.5c0-1.7-2-3-4.5-3s-4.5 1.3-4.5 3 2 2.6 4.5 3.2 4.5 1.5 4.5 3.3-2 3-4.5 3-4.5-1.3-4.5-3" />'],
                ['title' => 'Skilled Mechanics', 'text' => 'Experienced technicians using modern diagnostic tools on every job.', 'icon' => '<circle cx="12" cy="7" r="4" /><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1" />'],
                ['title' => 'Quality Parts', 'text' => 'Reliable, good-quality parts backed by a repair warranty.', 'icon' => '<path d="M12 3 4 6v6c0 4.5 3.4 8.3 8 9 4.6-.7 8-4.5 8-9V6l-8-3Z" /><path d="m8.5 12 2.5 2.5 4.5-4.5" />'],
                ['title' => 'Digital Records', 'text' => 'Every visit saved to your car\'s history, one QR scan away.', 'icon' => '<rect x="4" y="4" width="6" height="6" /><rect x="14" y="4" width="6" height="6" /><rect x="4" y="14" width="6" height="6" /><path d="M14 14h2v2h-2zM18 18h2v2h-2zM14 18h2M18 14h2" />'],
            ];

            // Placeholder details — replace with the workshop's real contact info.
            $contact = [
                'phone' => '+92 300 0000000',
                'email' => 'info@autocarepro.com',
                'address' => '123 Main Boulevard, Your City',
            ];

            $channels = [
                ['label' => 'Call Us', 'value' => $contact['phone'], 'href' => 'tel:' . preg_replace('/\s+/', '', $contact['phone']), 'icon' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2" />'],
                ['label' => 'Email Us', 'value' => $contact['email'], 'href' => 'mailto:' . $contact['email'], 'icon' => '<rect x="3" y="5" width="18" height="14" rx="1.5" /><path d="m3 7 9 6 9-6" />'],
                ['label' => 'Visit Us', 'value' => $contact['address'], 'href' => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($contact['address']), 'icon' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" /><circle cx="12" cy="9.5" r="2.5" />'],
            ];

            $hours = [
                ['day' => 'Monday – Friday', 'time' => '8:00 AM – 7:00 PM'],
                ['day' => 'Saturday', 'time' => '9:00 AM – 5:00 PM'],
                ['day' => 'Sunday', 'time' => 'Closed'],
            ];

            $faqs = [
                ['q' => 'Do I need an appointment?', 'a' => 'Booking online guarantees your slot, but walk-ins are welcome when a bay is free.'],
                ['q' => 'How long will my repair take?', 'a' => 'Most routine jobs are done the same day. We give you a time estimate with your quote.'],
                ['q' => 'Will I be charged for extra work?', 'a' => 'Never without asking. We call you and wait for your approval first.'],
            ];

            $arrow = '<svg width="16" height="12" viewBox="0 0 16 12" fill="none" aria-hidden="true"><path d="M9.5 1l5 5-5 5M14 6H1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            $btnPrimary = 'rx-label inline-flex items-center gap-3 px-7 py-4 rounded-xl bg-rx-accent text-white text-[13px] font-semibold hover:bg-rx-accent-hover transition-colors';
            $btnGhost = 'rx-label inline-flex items-center gap-3 px-7 py-4 rounded-xl border border-rx-line text-white text-[13px] font-semibold hover:border-white transition-colors';
            $card = 'rounded-2xl border border-rx-line bg-rx-surface';
        @endphp

        <!-- Hero -->
        <section class="relative overflow-hidden bg-rx-ink pt-20">
            <img src="{{ asset('images/hero-industrial/garage-hero.jpg') }}" alt="" aria-hidden="true" class="absolute inset-0 w-full h-full object-cover">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-rx-ink via-rx-ink/85 to-rx-ink/40" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-rx-ink to-transparent" aria-hidden="true"></div>

            <div class="relative max-w-[1280px] mx-auto px-6 lg:px-10 pt-20 pb-24 lg:pt-32 lg:pb-36 grid lg:grid-cols-12 gap-14 lg:gap-12 items-end">
                <div class="lg:col-span-7 min-w-0">
                    <span class="rx-eyebrow">Car Service &amp; Repair Workshop</span>
                    <h1 class="rx-display mt-5 text-[clamp(2.5rem,5.5vw,4.5rem)] text-white">
                        Expert Car Care.<br>Clear Prices.<br>No Surprises.
                    </h1>
                    <p class="mt-7 text-base sm:text-lg text-white/75 leading-relaxed max-w-md">
                        Book online, get a written quote, and follow your repair step by step.
                        Every visit is saved to your car's history — one QR scan away.
                    </p>
                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="{{ $btnPrimary }}">Go To Dashboard {!! $arrow !!}</a>
                        @else
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="{{ $btnPrimary }}">Book A Service {!! $arrow !!}</a>
                            @endif
                        @endauth
                        <a href="#services" class="{{ $btnGhost }} bg-rx-ink/40 backdrop-blur-sm">Our Services</a>
                    </div>
                </div>

                <div class="lg:col-span-5 lg:justify-self-end w-full max-w-sm">
                    <div class="rounded-2xl border border-white/10 bg-rx-ink/60 backdrop-blur-md p-6 shadow-2xl shadow-black/50">
                        <div class="flex items-center gap-3">
                            <span class="shrink-0 w-11 h-11 rounded-xl bg-rx-accent flex items-center justify-center">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5" /></svg>
                            </span>
                            <p class="text-base font-semibold text-white">Nothing done without your OK</p>
                        </div>
                        <p class="mt-4 text-sm text-white/70 leading-relaxed">
                            You get a written quote first and approve every repair before it starts — no hidden charges.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Highlights -->
            <div class="relative max-w-[1280px] mx-auto px-6 lg:px-10 pb-24">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($highlights as $item)
                        <div class="reveal rounded-2xl border border-white/10 bg-rx-ink/60 backdrop-blur-md p-5 sm:p-6 flex items-center gap-4" style="transition-delay: {{ $loop->index * 60 }}ms">
                            <svg class="shrink-0" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1652F0" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg>
                            <span class="text-sm font-semibold text-white">{{ $item['title'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Services -->
        <section id="services" class="bg-rx-surface py-28 lg:py-36">
            <div class="max-w-[1280px] mx-auto px-6 lg:px-10">
                <div class="reveal text-center max-w-2xl mx-auto">
                    <span class="rx-eyebrow">What We Do</span>
                    <h2 class="rx-display mt-4 text-[clamp(2rem,4vw,3rem)] text-white">Our Services</h2>
                    <p class="mt-5 text-base text-rx-muted leading-relaxed">
                        From a quick wiper change to a full gearbox repair — tap any service to see what's included and when you need it.
                    </p>
                </div>

                <div class="mt-16 grid sm:grid-cols-2 lg:grid-cols-3 sm:auto-rows-fr gap-6">
                    @foreach ($services as $service)
                        <article class="reveal group/card relative flex flex-col h-full overflow-hidden rounded-2xl border border-rx-line bg-rx-ink hover:border-rx-accent/60 hover:shadow-2xl hover:shadow-black/40" style="transition-delay: {{ ($loop->index % 3) * 60 }}ms">
                            <div class="relative aspect-[16/10] overflow-hidden">
                                <img src="{{ asset($service['image']) }}" alt="{{ $service['title'] }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover/card:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-rx-ink via-rx-ink/10 to-transparent"></div>
                            </div>

                            <div class="flex flex-1 flex-col px-7 pt-6 pb-7">
                                <div class="flex items-center gap-3">
                                    <span class="shrink-0 w-11 h-11 rounded-xl bg-rx-accent flex items-center justify-center">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $service['icon'] !!}</svg>
                                    </span>
                                    <h3 class="rx-label text-base font-semibold text-white leading-snug">{{ $service['title'] }}</h3>
                                </div>
                                <p class="mt-4 flex-1 text-sm text-rx-muted leading-relaxed">{{ $service['text'] }}</p>

                                <details name="service-details" class="group mt-6 border-t border-rx-line pt-5">
                                    <summary class="rx-label flex items-center justify-between cursor-pointer list-none text-xs font-semibold text-rx-accent">
                                        View Details
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 4v16M4 12h16" /></svg>
                                    </summary>

                                    <!-- Opens as a panel over the card, so the card never changes size -->
                                    <div class="absolute inset-0 z-10 flex flex-col bg-rx-ink/95 backdrop-blur-sm p-7 overflow-y-auto">
                                        <div class="flex items-center justify-between gap-4">
                                            <h4 class="rx-label text-sm font-semibold text-white">{{ $service['title'] }}</h4>
                                            <button type="button" onclick="this.closest('details').open = false" class="shrink-0 w-9 h-9 rounded-lg border border-rx-line flex items-center justify-center text-white hover:border-rx-accent hover:text-rx-accent transition-colors" aria-label="Close details">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
                                            </button>
                                        </div>

                                        <p class="rx-label mt-6 text-[11px] font-semibold text-rx-muted">What's Included</p>
                                        <ul class="mt-3 flex flex-col gap-2.5">
                                            @foreach ($service['includes'] as $item)
                                                <li class="flex items-start gap-2 text-sm text-white/90">
                                                    <svg class="mt-0.5 shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1652F0" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5" /></svg>
                                                    {{ $item }}
                                                </li>
                                            @endforeach
                                        </ul>

                                        <div class="mt-6 rounded-xl bg-rx-surface p-4">
                                            <p class="rx-label text-[11px] font-semibold text-white">{{ $loop->last ? 'When To Book' : 'Signs You Need It' }}</p>
                                            <p class="mt-1.5 text-sm text-rx-muted leading-relaxed">{{ $service['signs'] }}</p>
                                        </div>
                                    </div>
                                </details>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- About -->
        <section id="about" class="relative overflow-hidden bg-rx-ink py-28 lg:py-36">
            <img src="{{ asset('images/about-shop-floor.jpg') }}" alt="" aria-hidden="true" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
            <div class="pointer-events-none absolute inset-0 bg-rx-ink/[0.88]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-rx-ink via-transparent to-rx-ink" aria-hidden="true"></div>

            <div class="relative max-w-[1280px] mx-auto px-6 lg:px-10">
                <div class="reveal text-center max-w-2xl mx-auto">
                    <span class="rx-eyebrow">Who We Are</span>
                    <h2 class="rx-display mt-4 text-[clamp(2rem,4vw,3rem)] text-white">About Us</h2>
                    <p class="mt-5 text-base text-rx-muted leading-relaxed">
                        A modern car workshop built on one simple idea — repairs should be honest, fast and easy to follow.
                    </p>
                </div>

                <!-- Story -->
                <div class="mt-16 grid lg:grid-cols-2 gap-6 lg:gap-8 items-stretch">
                    <div class="reveal relative overflow-hidden rounded-2xl border border-rx-line min-h-[20rem]">
                        <img src="{{ asset('images/about-team.png') }}" alt="Mechanics inspecting a car in a bright, modern workshop" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-rx-ink/90 via-transparent to-transparent"></div>
                        <div class="absolute left-6 right-6 bottom-6 flex items-center gap-3">
                            <span class="shrink-0 w-11 h-11 rounded-xl bg-rx-accent flex items-center justify-center">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5" /></svg>
                            </span>
                            <p class="text-sm font-semibold text-white">Everyday cars, family vehicles and fleets</p>
                        </div>
                    </div>

                    <div class="reveal rounded-2xl border border-white/10 bg-rx-surface/75 backdrop-blur-md p-8 sm:p-10 flex flex-col">
                        <h3 class="rx-label text-xl sm:text-2xl font-semibold text-white leading-snug">Your Car, In Expert Hands</h3>
                        <p class="mt-5 text-base text-rx-muted leading-relaxed">
                            AutoCare Pro is run by experienced mechanics who combine hands-on skill with modern
                            diagnostic equipment, so problems are found quickly and fixed right the first time.
                        </p>
                        <p class="mt-4 text-base text-rx-muted leading-relaxed">
                            From your first booking to the final invoice, you always know what is being done to
                            your car and why — and every visit is saved to its service history.
                        </p>

                        <div class="mt-8 rounded-xl border border-rx-accent/40 bg-rx-accent/5 p-5 flex items-start gap-4">
                            <svg class="shrink-0 mt-0.5" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1652F0" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v6c0 4.5 3.4 8.3 8 9 4.6-.7 8-4.5 8-9V6l-8-3Z" /><path d="m8.5 12 2.5 2.5 4.5-4.5" /></svg>
                            <div>
                                <p class="rx-label text-xs font-semibold text-rx-accent">Our Promise</p>
                                <p class="mt-1.5 text-sm text-white leading-relaxed">No hidden charges. If we find anything extra during a repair, we call you first — nothing is done without your approval.</p>
                            </div>
                        </div>

                        <div class="mt-auto pt-8">
                            <a href="#contact" class="{{ $btnPrimary }}">Talk To Our Team {!! $arrow !!}</a>
                        </div>
                    </div>
                </div>

                <!-- Values -->
                <div class="mt-6 lg:mt-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($values as $value)
                        <div class="reveal rounded-2xl border border-white/10 bg-rx-surface/75 backdrop-blur-md p-7 hover:border-rx-accent/60 transition-colors" style="transition-delay: {{ $loop->index * 60 }}ms">
                            <div class="flex items-center gap-3">
                                <span class="shrink-0 w-11 h-11 rounded-xl bg-rx-accent/10 flex items-center justify-center">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1652F0" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $value['icon'] !!}</svg>
                                </span>
                                <h4 class="rx-label text-sm font-semibold text-white leading-snug">{{ $value['title'] }}</h4>
                            </div>
                            <p class="mt-4 text-sm text-rx-muted leading-relaxed">{{ $value['text'] }}</p>
                        </div>
                    @endforeach
                </div>

                <!-- How it works -->
                <div class="mt-28 lg:mt-36">
                    <div class="reveal text-center max-w-2xl mx-auto">
                        <span class="rx-eyebrow">How It Works</span>
                        <h2 class="rx-display mt-4 text-[clamp(2rem,4vw,3rem)] text-white">Four Simple Steps</h2>
                        <p class="mt-5 text-base text-rx-muted leading-relaxed">From booking to pick-up, you are never left guessing.</p>
                    </div>

                    <ol class="mt-16 relative grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <span class="hidden lg:block absolute top-[2.75rem] left-[12.5%] right-[12.5%] h-px bg-gradient-to-r from-rx-accent/0 via-rx-accent/60 to-rx-accent/0" aria-hidden="true"></span>
                        @foreach ($steps as $step)
                            <li class="reveal relative text-center rounded-2xl border border-white/10 bg-rx-surface/75 backdrop-blur-md px-6 pt-5 pb-8" style="transition-delay: {{ $loop->index * 60 }}ms">
                                <span class="rx-display mx-auto w-12 h-12 rounded-full bg-rx-accent flex items-center justify-center text-lg text-white ring-8 ring-rx-ink">{{ $loop->iteration }}</span>
                                <h4 class="rx-label mt-6 text-base font-semibold text-white">{{ $step['title'] }}</h4>
                                <p class="mt-2 text-sm text-rx-muted leading-relaxed">{{ $step['text'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </section>

        <!-- Contact -->
        <section id="contact" class="bg-[#2A2A2F] py-28 lg:py-36">
            <div class="max-w-[1280px] mx-auto px-6 lg:px-10">
                <div class="reveal text-center max-w-2xl mx-auto">
                    <span class="rx-eyebrow">Contact</span>
                    <h2 class="rx-display mt-4 text-[clamp(2rem,4vw,3rem)] text-white">Get In Touch</h2>
                    <p class="mt-5 text-base text-[#B4B4BC] leading-relaxed">
                        Have a question, need a quote, or want to book a service? Our team usually replies within one working day.
                    </p>
                </div>

                <div class="mt-16 grid lg:grid-cols-12 gap-6 lg:gap-8">
                    <!-- Info column -->
                    <div class="lg:col-span-5 flex flex-col gap-6">
                        @foreach ($channels as $channel)
                            <a href="{{ $channel['href'] }}" @if (str_starts_with($channel['href'], 'http')) target="_blank" rel="noopener" @endif
                               class="reveal group rounded-2xl border border-white/10 bg-[#34343A] p-6 flex items-center gap-5 hover:border-rx-accent/60 transition-colors">
                                <span class="shrink-0 w-12 h-12 rounded-xl bg-rx-accent flex items-center justify-center">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $channel['icon'] !!}</svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="rx-label block text-xs font-semibold text-[#B4B4BC]">{{ $channel['label'] }}</span>
                                    <span class="mt-1 block text-base text-white break-words group-hover:text-rx-accent transition-colors">{{ $channel['value'] }}</span>
                                </span>
                            </a>
                        @endforeach

                        <div class="reveal rounded-2xl border border-white/10 bg-[#34343A] p-6">
                            <p class="rx-label text-xs font-semibold text-[#B4B4BC]">Opening Hours</p>
                            <dl class="mt-4 flex flex-col gap-3">
                                @foreach ($hours as $row)
                                    <div class="flex items-center justify-between">
                                        <dt class="text-sm text-white">{{ $row['day'] }}</dt>
                                        <dd class="text-sm {{ $row['time'] === 'Closed' ? 'text-[#B4B4BC]' : 'text-white font-semibold' }}">{{ $row['time'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>

                    <!-- Form -->
                    <form id="rx-contact-form" class="reveal lg:col-span-7 rounded-2xl border border-white/10 bg-[#34343A] p-6 sm:p-10" data-email="{{ $contact['email'] }}">
                        <h3 class="rx-label text-xl font-semibold text-white">Send Us A Message</h3>
                        <p class="mt-2 text-sm text-[#B4B4BC]">Fill in the form and it will open in your email app, ready to send.</p>

                        <div class="mt-8 grid sm:grid-cols-2 gap-5">
                            <label class="block">
                                <span class="rx-label text-xs font-semibold text-white">Full Name</span>
                                <input name="name" type="text" required autocomplete="name" class="mt-2 w-full rounded-xl bg-[#2A2A2F] border-white/10 text-white placeholder-white/40 focus:border-rx-accent focus:ring-rx-accent" placeholder="Your name">
                            </label>
                            <label class="block">
                                <span class="rx-label text-xs font-semibold text-white">Phone</span>
                                <input name="phone" type="tel" autocomplete="tel" class="mt-2 w-full rounded-xl bg-[#2A2A2F] border-white/10 text-white placeholder-white/40 focus:border-rx-accent focus:ring-rx-accent" placeholder="Your phone number">
                            </label>
                            <label class="block">
                                <span class="rx-label text-xs font-semibold text-white">Car Make &amp; Model</span>
                                <input name="car" type="text" class="mt-2 w-full rounded-xl bg-[#2A2A2F] border-white/10 text-white placeholder-white/40 focus:border-rx-accent focus:ring-rx-accent" placeholder="e.g. Toyota Corolla 2018">
                            </label>
                            <label class="block">
                                <span class="rx-label text-xs font-semibold text-white">Service Needed</span>
                                <select name="service" class="mt-2 w-full rounded-xl bg-[#2A2A2F] border-white/10 text-white focus:border-rx-accent focus:ring-rx-accent">
                                    <option value="General enquiry">General enquiry</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service['title'] }}">{{ $service['title'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block sm:col-span-2">
                                <span class="rx-label text-xs font-semibold text-white">Message</span>
                                <textarea name="message" rows="5" required class="mt-2 w-full rounded-xl bg-[#2A2A2F] border-white/10 text-white placeholder-white/40 focus:border-rx-accent focus:ring-rx-accent" placeholder="Tell us what's wrong with your car or what you need"></textarea>
                            </label>
                        </div>

                        <button type="submit" class="{{ $btnPrimary }} mt-8">Send Message {!! $arrow !!}</button>
                    </form>
                </div>

                <!-- FAQ -->
                <div class="mt-24">
                    <h3 class="reveal rx-label text-center text-xs font-semibold text-[#B4B4BC]">Quick Answers</h3>
                    <div class="mt-8 grid md:grid-cols-3 gap-6 items-start">
                        @foreach ($faqs as $faq)
                            <details class="reveal group rounded-2xl border border-white/10 bg-[#34343A] open:border-rx-accent/60 transition-colors">
                                <summary class="flex items-center justify-between gap-4 cursor-pointer list-none p-6 text-sm font-semibold text-white">
                                    {{ $faq['q'] }}
                                    <svg class="shrink-0 transition-transform group-open:rotate-45" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1652F0" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 4v16M4 12h16" /></svg>
                                </summary>
                                <p class="px-6 pb-6 -mt-2 text-sm text-[#B4B4BC] leading-relaxed">{{ $faq['a'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- Booking CTA -->
        <section class="bg-rx-ink py-24">
            <div class="max-w-[1280px] mx-auto px-6 lg:px-10">
                <div class="reveal relative overflow-hidden rounded-3xl bg-rx-accent px-8 py-7 sm:px-14 flex flex-col lg:flex-row items-center justify-between gap-5">
                    <div class="pointer-events-none absolute -right-16 -top-16 w-56 h-56 rounded-full bg-white/10" aria-hidden="true"></div>
                    <div class="relative text-center lg:text-left">
                        <h2 class="rx-display text-[clamp(1.35rem,2.5vw,1.85rem)] text-white">Ready To Book Your Service?</h2>
                        <p class="mt-2 text-sm text-white/80">Pick a time online — it only takes a couple of minutes.</p>
                    </div>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="relative rx-label shrink-0 inline-flex items-center gap-3 px-7 py-4 rounded-xl bg-white text-rx-accent text-[13px] font-semibold hover:bg-rx-ink hover:text-white transition-colors">Go To Dashboard</a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="relative rx-label shrink-0 inline-flex items-center gap-3 px-7 py-4 rounded-xl bg-white text-rx-accent text-[13px] font-semibold hover:bg-rx-ink hover:text-white transition-colors">Book A Service</a>
                        @endif
                    @endauth
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-rx-ink border-t border-rx-line">
            <div class="max-w-[1440px] mx-auto px-6 lg:px-10 py-10 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <svg width="22" height="22" viewBox="0 0 30 30" fill="none" aria-hidden="true">
                        <path d="M15 1.5 27.5 8.5V21.5L15 28.5 2.5 21.5V8.5L15 1.5Z" stroke="#1652F0" stroke-width="2"/>
                        <path d="M15 9 20.5 12.25V18.75L15 22 9.5 18.75V12.25L15 9Z" fill="#1652F0"/>
                    </svg>
                    <span class="rx-label text-sm font-semibold text-white">AutoCare<span class="text-rx-accent">/</span>Pro</span>
                </div>
                <p class="text-sm text-rx-muted">&copy; {{ now()->year }} AutoCare Pro. All rights reserved.</p>
            </div>
        </footer>

        <script>
            (function () {
                var header = document.getElementById('rx-header');
                var onScroll = function () {
                    header.classList.toggle('rx-scrolled', window.scrollY > 12);
                };
                onScroll();
                window.addEventListener('scroll', onScroll, { passive: true });

                var menuBtn = document.getElementById('rx-menu-btn');
                var menu = document.getElementById('rx-mobile-menu');
                menuBtn.addEventListener('click', function () {
                    var isOpen = menu.classList.toggle('hidden') === false;
                    menuBtn.setAttribute('aria-expanded', String(isOpen));
                });

                // Only one service "Details" panel open at a time (fallback for browsers without <details name>).
                var serviceDetails = document.querySelectorAll('details[name="service-details"]');
                serviceDetails.forEach(function (d) {
                    d.addEventListener('toggle', function () {
                        if (!d.open) return;
                        serviceDetails.forEach(function (other) { if (other !== d) other.open = false; });
                    });
                });

                // No backend endpoint yet: open the message in the visitor's email app.
                var contactForm = document.getElementById('rx-contact-form');
                contactForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var f = contactForm.elements;
                    var body = 'Name: ' + f.name.value + '\nPhone: ' + f.phone.value +
                        '\nCar: ' + f.car.value + '\nService: ' + f.service.value + '\n\n' + f.message.value;
                    window.location.href = 'mailto:' + contactForm.dataset.email +
                        '?subject=' + encodeURIComponent(f.service.value + ' — ' + f.name.value) +
                        '&body=' + encodeURIComponent(body);
                });

                var revealEls = document.querySelectorAll('.reveal');
                var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                if (reduceMotion || !('IntersectionObserver' in window)) {
                    revealEls.forEach(function (el) { el.classList.add('reveal-visible'); });
                    return;
                }

                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('reveal-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

                revealEls.forEach(function (el) { observer.observe(el); });
            })();
        </script>
    </body>
</html>
