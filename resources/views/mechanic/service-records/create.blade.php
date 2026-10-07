@php
    $input = 'block w-full rounded-xl border-0 bg-white/5 px-4 py-3 text-sm text-white placeholder-zinc-500 ring-1 ring-inset ring-rx-line transition [color-scheme:dark] focus:bg-white/[0.07] focus:ring-2 focus:ring-rx-accent';
    $label = 'block text-sm font-medium text-zinc-200';
    $hint = 'mt-0.5 text-xs text-zinc-500';
    $itemErrors = collect($errors->get('items.*.inventory_item_id'))->flatten()
        ->merge(collect($errors->get('items.*.quantity'))->flatten());
@endphp

<x-dashboard-layout>
    <div class="px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">
        <form method="POST" action="{{ route('mechanic.bookings.complete.store', $booking) }}" class="space-y-5"
              x-data="{ items: {{ old('items') ? json_encode(old('items')) : '[]' }} }">
            @csrf

            <!-- Banner -->
            <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
                <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative flex items-center gap-4">
                    <a href="{{ route('mechanic.bookings.show', $booking) }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25" title="{{ __('Back to Job') }}">
                        <x-icon name="chevron-right" class="h-5 w-5 rotate-180" />
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Job') }} #{{ $booking->id }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">{{ __('Log Service & Complete') }}</h1>
                        <p class="mt-0.5 text-sm text-white/80">{{ $booking->vehicle->display_name }} — {{ $booking->requested_service }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <!-- Work performed -->
                <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                            <x-icon name="wrench" class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="{{ $label }}">{{ __('Work Performed') }}</p>
                            <p class="{{ $hint }}">{{ __('Describe what was done — the customer sees this in their history.') }}</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="description" class="sr-only">{{ __('Work Performed') }}</label>
                        <textarea id="description" name="description" rows="6" required class="{{ $input }}" placeholder="{{ __('e.g. Replaced front brake pads, topped up brake fluid…') }}">{{ old('description') }}</textarea>
                        @error('description') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-4">
                        <label for="labor_cost" class="{{ $label }}">{{ __('Labor Cost') }}</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-zinc-500">Rs</span>
                            <input id="labor_cost" class="{{ $input }} !pl-11" type="number" step="0.01" min="0" name="labor_cost" value="{{ old('labor_cost', 0) }}" required />
                        </div>
                        @error('labor_cost') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </section>

                <!-- Parts used -->
                <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                                <x-icon name="archive" class="h-4 w-4" />
                            </span>
                            <div>
                                <p class="{{ $label }}">{{ __('Parts Used') }}</p>
                                <p class="{{ $hint }}">{{ __('Stock is deducted from inventory automatically.') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="items.push({ inventory_item_id: '', quantity: 1 })" class="inline-flex items-center gap-1.5 rounded-xl bg-rx-accent/10 px-3 py-2 text-xs font-semibold text-blue-400 ring-1 ring-rx-accent/20 transition hover:bg-rx-accent/20">
                            <x-icon name="plus" class="h-3.5 w-3.5" /> {{ __('Add Part') }}
                        </button>
                    </div>

                    <div class="mt-5 space-y-3">
                        <p x-show="items.length === 0" class="rounded-2xl border border-dashed border-white/10 py-8 text-center text-sm text-zinc-500">{{ __('No parts used.') }}</p>

                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex items-center gap-3">
                                <select :name="`items[${index}][inventory_item_id]`" x-model="item.inventory_item_id" required class="{{ $input }} min-w-0 flex-1">
                                    <option value="">{{ __('Select a part') }}</option>
                                    @foreach ($inventoryItems as $inventoryItem)
                                        <option value="{{ $inventoryItem->id }}">{{ $inventoryItem->name }} ({{ $inventoryItem->quantity }} {{ __('in stock') }})</option>
                                    @endforeach
                                </select>
                                <input type="number" min="1" :name="`items[${index}][quantity]`" x-model="item.quantity" required class="{{ $input }} !w-24" aria-label="{{ __('Quantity') }}">
                                <button type="button" @click="items.splice(index, 1)" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-rose-500/10 hover:text-rose-400" title="{{ __('Remove') }}">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </template>
                    </div>

                    @foreach ($itemErrors as $message)
                        <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                    @endforeach
                </section>
            </div>

            <div class="flex items-center justify-end gap-3 rounded-3xl bg-rx-surface px-6 py-4 ring-1 ring-rx-line">
                <a href="{{ route('mechanic.bookings.show', $booking) }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-zinc-400 transition hover:bg-white/5 hover:text-white">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                    <x-icon name="check-circle" class="h-4 w-4" /> {{ __('Complete Booking') }}
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
