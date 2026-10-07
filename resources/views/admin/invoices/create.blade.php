@php
    $select = 'mt-1.5 block w-full rounded-xl border-0 bg-white/5 px-4 py-3 text-sm text-white ring-1 ring-inset ring-rx-line [color-scheme:dark] focus:ring-2 focus:ring-rx-accent';
    $initialItems = old('items') ?? [['inventory_item_id' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0]];
@endphp

<x-dashboard-layout>
    <div class="px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">
        <form method="POST" action="{{ route('admin.invoices.store') }}" class="space-y-5" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <!-- Banner -->
            <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
                <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative flex items-center gap-4">
                    <a href="{{ route('admin.invoices.index') }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25" title="{{ __('Back to Invoices') }}">
                        <x-icon name="chevron-right" class="h-5 w-5 rotate-180" />
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Invoices') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">{{ __('Create Invoice') }}</h1>
                        <p class="mt-0.5 text-sm text-white/80">{{ __('Bill a customer for the work done and the parts used.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Customer & booking -->
            <section
                class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line"
                x-data="{
                    customer: {{ json_encode((string) old('customer_id', '')) }},
                    booking: {{ json_encode((string) old('booking_id', '')) }},
                    bookings: {{ json_encode($bookings) }},
                    get options() { return this.bookings.filter(b => b.customer_id == this.customer); },
                }"
            >
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                        <x-icon name="users" class="h-4 w-4" />
                    </span>
                    <div>
                        <p class="block text-sm font-medium text-zinc-200">{{ __('Bill To') }}</p>
                        <p class="mt-0.5 text-xs text-zinc-500">{{ __('The invoice appears on this customer\'s invoices page and is emailed to them.') }}</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-zinc-200">{{ __('Customer') }}</label>
                        <select id="customer_id" name="customer_id" x-model="customer" @change="booking = ''" class="{{ $select }}" required>
                            <option value="">{{ __('Select a customer') }}</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                            @endforeach
                        </select>
                        @error('customer_id') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="booking_id" class="block text-sm font-medium text-zinc-200">{{ __('Booking (optional)') }}</label>
                        <select id="booking_id" name="booking_id" x-model="booking" class="{{ $select }}">
                            <option value="">{{ __('No linked booking') }}</option>
                            <template x-for="option in options" :key="option.id">
                                <option :value="option.id" x-text="option.label" :selected="option.id == booking"></option>
                            </template>
                        </select>
                        <p x-show="customer && ! options.length" x-cloak class="mt-2 text-xs text-zinc-500">{{ __('This customer has no bookings.') }}</p>
                        @error('booking_id') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            @include('admin.invoices._form', ['initialItems' => $initialItems, 'tax' => old('tax', 0)])

            <div class="flex items-center justify-end gap-3 rounded-3xl bg-rx-surface px-6 py-4 ring-1 ring-rx-line">
                <a href="{{ route('admin.invoices.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-zinc-400 transition hover:bg-white/5 hover:text-white">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" :disabled="submitting" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover disabled:cursor-not-allowed disabled:opacity-60">
                    <x-icon name="check-circle" class="h-4 w-4" /> {{ __('Create & Send Invoice') }}
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
