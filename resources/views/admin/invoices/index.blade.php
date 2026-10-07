@php
    use App\Models\Invoice;

    $collected = Invoice::where('status', Invoice::STATUS_PAID)->sum('total');
    $outstanding = Invoice::where('status', Invoice::STATUS_UNPAID)->sum('total');
    $unpaidCount = Invoice::where('status', Invoice::STATUS_UNPAID)->count();
    $paidCount = Invoice::where('status', Invoice::STATUS_PAID)->count();
    $billed = $collected + $outstanding;
    $collectedPercent = $billed > 0 ? (int) round($collected / $billed * 100) : 0;
@endphp

<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="hidden h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-white sm:flex">
                        <x-icon name="receipt" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Billing') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-4xl">{{ __('Invoices') }}</h1>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-black/20 px-3 py-1.5 text-xs font-medium text-white">
                        {{ __(':count invoice(s) total.', ['count' => $invoices->total()]) }}
                    </span>
                    <a href="{{ route('admin.invoices.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rx-accent shadow-lg transition hover:bg-blue-50">
                        <x-icon name="plus" class="h-4 w-4" /> {{ __('Create Invoice') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-[1.4fr_1fr_1fr]">
            <div class="relative overflow-hidden rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                <div class="pointer-events-none absolute -right-12 -top-20 h-56 w-56 rounded-full bg-rx-accent/15 blur-3xl"></div>
                <div class="relative">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <p class="text-sm text-zinc-400">{{ __('Collected') }}</p>
                            <span class="rounded-full bg-rx-accent px-2 py-0.5 text-[11px] font-bold text-white">{{ $paidCount }} {{ __('paid') }}</span>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                            <x-icon name="check-circle" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-white">Rs {{ number_format($collected, 2) }}</p>

                    <div class="mt-5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-500">{{ __('Collection rate') }}</span>
                            <span class="font-semibold text-blue-400">{{ $collectedPercent }}%</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/10">
                            <span class="block h-full rounded-full bg-rx-accent" style="width: {{ $collectedPercent }}%"></span>
                        </div>
                        <p class="mt-2 text-xs text-zinc-500">
                            {{ __('Rs :paid of Rs :billed billed', ['paid' => number_format($collected, 2), 'billed' => number_format($billed, 2)]) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <p class="text-sm text-zinc-400">{{ __('Outstanding') }}</p>
                        <span class="rounded-full bg-[#34343A] px-2 py-0.5 text-[11px] font-bold text-white">{{ $unpaidCount }} {{ __('unpaid') }}</span>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                        <x-icon name="exclamation-triangle" class="h-5 w-5" />
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold tracking-tight text-white">Rs {{ number_format($outstanding, 2) }}</p>
                <p class="mt-auto pt-4 text-xs text-zinc-500">{{ __('Waiting to be paid by customers') }}</p>
            </div>

            <div class="flex flex-col rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-zinc-400">{{ __('Total Billed') }}</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                        <x-icon name="receipt" class="h-5 w-5" />
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold tracking-tight text-white">Rs {{ number_format($billed, 2) }}</p>
                <p class="mt-auto pt-4 text-xs text-zinc-500">{{ trans_choice(':count invoice issued|:count invoices issued', $paidCount + $unpaidCount, ['count' => $paidCount + $unpaidCount]) }}</p>
            </div>
        </div>

        @if ($invoices->isEmpty())
            <div class="flex flex-col items-center rounded-3xl bg-rx-surface py-16 text-center ring-1 ring-rx-line">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rx-accent/15 text-blue-400">
                    <x-icon name="receipt" class="h-7 w-7" />
                </span>
                <p class="mt-4 text-base font-medium text-white">{{ __('No invoices yet.') }}</p>
            </div>
        @else
            <div class="flex items-center justify-between px-1">
                <h2 class="text-lg font-semibold text-white">{{ __('All Invoices') }}</h2>
                <p class="text-xs text-zinc-500">{{ __('Newest first · showing :from–:to of :total', ['from' => $invoices->firstItem(), 'to' => $invoices->lastItem(), 'total' => $invoices->total()]) }}</p>
            </div>

            <div class="space-y-3">
                @foreach ($invoices as $invoice)
                    @php $isPaid = $invoice->status === Invoice::STATUS_PAID; @endphp
                    <div class="group relative overflow-hidden rounded-3xl bg-rx-surface ring-1 ring-rx-line transition hover:ring-rx-accent/40">
                        <span class="absolute inset-y-0 left-0 w-1 {{ $isPaid ? 'bg-rx-accent' : 'bg-zinc-400' }}"></span>

                        <div class="grid grid-cols-1 gap-4 p-5 pl-6 md:grid-cols-2 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)_minmax(0,1.2fr)_auto] xl:items-center">
                            <!-- Number + date -->
                            <div class="flex items-center gap-4">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rx-accent/15 text-blue-400">
                                    <x-icon name="receipt" class="h-5 w-5" />
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="font-mono text-base font-semibold text-white hover:text-blue-400">#{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</a>
                                        <x-dark-status :status="$invoice->status" />
                                    </div>
                                    <p class="mt-1 truncate text-xs text-zinc-500">
                                        {{ __('Issued') }} {{ $invoice->created_at->format('M j, Y') }}
                                        @if ($isPaid && $invoice->paymentSummary() !== 'Paid')
                                            · {{ $invoice->paymentSummary() }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Customer -->
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/5 text-sm font-semibold text-zinc-300 ring-1 ring-white/10">
                                    {{ str($invoice->customer->name)->substr(0, 1)->upper() }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-white">{{ $invoice->customer->name }}</p>
                                    <p class="truncate text-xs text-zinc-500">{{ $invoice->customer->email }}</p>
                                </div>
                            </div>

                            <!-- Vehicle -->
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-12 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-black/40 ring-1 ring-white/10">
                                    @if ($invoice->booking?->vehicle->image_url)
                                        <img src="{{ $invoice->booking->vehicle->image_url }}" alt="" class="h-full w-full object-cover" />
                                    @else
                                        <x-car-illustration uid="ainv{{ $invoice->id }}" class="w-16" />
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    @if ($invoice->booking)
                                        <p class="truncate text-sm font-medium text-zinc-200">{{ $invoice->booking->vehicle->display_name }}</p>
                                        <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $invoice->booking->requested_service }}</p>
                                    @else
                                        <p class="text-sm font-medium text-zinc-200">{{ __('Workshop service') }}</p>
                                        <p class="mt-0.5 text-xs text-zinc-500">{{ __('No linked booking') }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Amount + actions -->
                            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-white/5 pt-4 xl:justify-end xl:border-0 xl:pt-0">
                                <div class="xl:text-right">
                                    <p class="text-[11px] font-medium uppercase tracking-wide text-zinc-500">{{ $isPaid ? __('Amount Paid') : __('Amount Due') }}</p>
                                    <p class="text-xl font-bold tracking-tight text-white">Rs {{ number_format($invoice->total, 2) }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @unless ($isPaid)
                                        <form method="POST" action="{{ route('admin.invoices.markPaid', $invoice) }}" x-data @submit="if (! confirm('{{ __('Confirm that :name paid Rs :amount in cash?', ['name' => e($invoice->customer->name), 'amount' => number_format($invoice->total, 2)]) }}')) $event.preventDefault()">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-rx-accent px-3.5 py-2.5 text-xs font-semibold text-white shadow-lg shadow-rx-accent/20 transition hover:bg-rx-accent-hover">
                                                <x-icon name="check-circle" class="h-3.5 w-3.5" /> {{ __('Cash Received') }}
                                            </button>
                                        </form>
                                    @endunless
                                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-zinc-200 ring-1 ring-white/10 transition hover:bg-white/10" title="{{ __('View') }}" aria-label="{{ __('View') }}">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                    <a href="{{ route('admin.invoices.download', $invoice) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-zinc-200 ring-1 ring-white/10 transition hover:bg-white/10" title="{{ __('Download PDF') }}" aria-label="{{ __('Download PDF') }}">
                                        <x-icon name="download" class="h-4 w-4" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($invoices->hasPages())
            <div class="rounded-2xl bg-rx-surface px-4 py-3 ring-1 ring-rx-line">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
