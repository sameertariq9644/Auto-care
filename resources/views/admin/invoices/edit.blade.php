@php
    $initialItems = old('items') ?? $invoice->items->map(fn ($item) => [
        'inventory_item_id' => $item->inventory_item_id ?? '',
        'description' => $item->description,
        'quantity' => $item->quantity,
        'unit_price' => $item->unit_price,
    ])->all();
@endphp

<x-dashboard-layout>
    <div class="px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">
        <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Banner -->
            <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
                <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative flex items-center gap-4">
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25" title="{{ __('Back to Invoice') }}">
                        <x-icon name="chevron-right" class="h-5 w-5 rotate-180" />
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Invoices') }} · #{{ $invoice->id }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">{{ __('Edit Invoice') }}</h1>
                        <p class="mt-0.5 text-sm text-white/80">{{ __('Adjust line items and tax before finalizing.') }}</p>
                    </div>
                </div>
            </div>

            @include('admin.invoices._form', ['initialItems' => $initialItems, 'tax' => old('tax', $invoice->tax)])

            <div class="flex items-center justify-end gap-3 rounded-3xl bg-rx-surface px-6 py-4 ring-1 ring-rx-line">
                <a href="{{ route('admin.invoices.show', $invoice) }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-zinc-400 transition hover:bg-white/5 hover:text-white">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                    <x-icon name="check-circle" class="h-4 w-4" /> {{ __('Save Invoice') }}
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
