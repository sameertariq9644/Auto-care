<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('invoices.index') }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25" title="{{ __('Back to My Invoices') }}">
                        <x-icon name="chevron-right" class="h-5 w-5 rotate-180" />
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('My Invoices') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">{{ __('Invoice') }} #{{ $invoice->id }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-xl bg-black/30 p-1">
                        <x-dark-status :status="$invoice->status" />
                    </span>
                    <a href="{{ route('invoices.download', $invoice) }}" class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/25">
                        <x-icon name="download" class="h-4 w-4" /> {{ __('Download PDF') }}
                    </a>
                    @if ($invoice->status !== \App\Models\Invoice::STATUS_PAID)
                        <a href="{{ route('invoices.pay', $invoice) }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rx-accent shadow-lg transition hover:bg-blue-50">
                            <x-icon name="receipt" class="h-4 w-4" /> {{ __('Pay Now') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice document -->
        <div class="mx-auto max-w-4xl overflow-hidden rounded-3xl bg-rx-surface ring-1 ring-rx-line">
            <div class="flex flex-wrap items-start justify-between gap-6 border-b border-white/5 p-6 sm:p-8">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rx-accent shadow-lg shadow-rx-accent/30">
                        <x-application-logo class="h-5 w-auto fill-current text-white" />
                    </span>
                    <span class="text-lg font-bold italic tracking-tight text-white">AutoCare <span class="text-blue-400">Pro</span></span>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold uppercase tracking-widest text-white">{{ __('Invoice') }}</p>
                    <p class="mt-1 font-mono text-sm text-blue-400">#{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</p>
                    <p class="text-sm text-zinc-500">{{ $invoice->created_at->format('F j, Y') }}</p>
                    @if ($invoice->paymentSummary())
                        <p class="mt-1 text-sm font-medium text-blue-400">{{ $invoice->paymentSummary() }}</p>
                    @endif
                </div>
            </div>

            @if ($invoice->booking)
                <div class="flex items-center gap-4 border-b border-white/5 p-6 sm:p-8">
                    <span class="flex h-14 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-black/40">
                        @if ($invoice->booking->vehicle->image_url)
                            <img src="{{ $invoice->booking->vehicle->image_url }}" alt="" class="h-full w-full object-contain" />
                        @else
                            <x-car-illustration uid="inv" class="w-16" />
                        @endif
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Vehicle') }}</p>
                        <p class="mt-1 font-medium text-white">
                            {{ $invoice->booking->vehicle->display_name }}
                        </p>
                        <p class="text-sm text-zinc-400">{{ $invoice->booking->requested_service }}</p>
                    </div>
                </div>
            @endif

            <div class="p-6 sm:p-8">
                <div class="overflow-x-auto rounded-2xl ring-1 ring-rx-line">
                    <table class="w-full min-w-[480px] border-collapse text-left text-sm">
                        <thead class="bg-white/[0.03]">
                            <tr class="text-xs text-zinc-500">
                                <th class="px-4 py-3 font-medium">{{ __('Description') }}</th>
                                <th class="px-4 py-3 text-right font-medium">{{ __('Qty') }}</th>
                                <th class="px-4 py-3 text-right font-medium">{{ __('Unit Price') }}</th>
                                <th class="px-4 py-3 text-right font-medium">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach ($invoice->items as $item)
                                <tr class="text-zinc-300">
                                    <td class="px-4 py-3">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-right">Rs {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-white">Rs {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <dl class="w-64 rounded-2xl bg-white/[0.03] p-4 ring-1 ring-rx-line">
                        <div class="flex justify-between py-1 text-sm">
                            <dt class="text-zinc-500">{{ __('Subtotal') }}</dt>
                            <dd class="text-zinc-200">Rs {{ number_format($invoice->subtotal, 2) }}</dd>
                        </div>
                        <div class="flex justify-between py-1 text-sm">
                            <dt class="text-zinc-500">{{ __('Tax') }}</dt>
                            <dd class="text-zinc-200">Rs {{ number_format($invoice->tax, 2) }}</dd>
                        </div>
                        <div class="mt-2 flex justify-between border-t border-white/10 pt-3 text-lg font-bold text-white">
                            <dt>{{ __('Total') }}</dt>
                            <dd>Rs {{ number_format($invoice->total, 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
