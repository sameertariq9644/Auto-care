<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex items-center gap-4">
                <a href="{{ route('invoices.show', $invoice) }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25" title="{{ __('Back to Invoice') }}">
                    <x-icon name="chevron-right" class="h-5 w-5 rotate-180" />
                </a>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Invoice') }} #{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</p>
                    <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">{{ __('Pay Invoice') }}</h1>
                </div>
            </div>
        </div>

        <div class="mx-auto grid max-w-4xl grid-cols-1 gap-5 lg:grid-cols-[1fr_300px]">
            <!-- Payment methods -->
            <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line sm:p-8">
                <h2 class="text-lg font-semibold text-white">{{ __('Payment Method') }}</h2>
                <div class="mt-5 flex flex-col items-center rounded-2xl border border-dashed border-white/10 px-6 py-10 text-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rx-accent/15 text-blue-400">
                        <x-icon name="clock" class="h-7 w-7" />
                    </span>
                    <p class="mt-4 text-base font-medium text-white">{{ __('Online payment is coming soon.') }}</p>
                    <p class="mt-1 max-w-sm text-sm text-zinc-500">{{ __('For now, please pay at the workshop. Your invoice will be marked as paid once we receive your payment.') }}</p>
                </div>
            </section>

            <!-- Summary -->
            <section class="h-fit rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Amount Due') }}</p>
                <p class="mt-1 text-3xl font-bold tracking-tight text-white">Rs {{ number_format($invoice->total, 2) }}</p>

                <dl class="mt-5 space-y-2 border-t border-white/5 pt-4 text-sm">
                    @foreach ($invoice->items as $item)
                        <div class="flex justify-between gap-3">
                            <dt class="truncate text-zinc-400">{{ $item->description }} × {{ $item->quantity }}</dt>
                            <dd class="shrink-0 text-zinc-200">Rs {{ number_format($item->quantity * $item->unit_price, 2) }}</dd>
                        </div>
                    @endforeach
                    <div class="flex justify-between gap-3">
                        <dt class="text-zinc-400">{{ __('Tax') }}</dt>
                        <dd class="text-zinc-200">Rs {{ number_format($invoice->tax, 2) }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</x-dashboard-layout>
