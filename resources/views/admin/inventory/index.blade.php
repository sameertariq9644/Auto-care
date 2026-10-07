@php
    use App\Models\InventoryItem;

    $lowCount = InventoryItem::whereColumn('quantity', '<=', 'reorder_level')->count();
    $stats = [
        ['icon' => 'archive', 'label' => __('Items'), 'value' => number_format(InventoryItem::count()), 'hint' => __('Different parts stocked')],
        ['icon' => 'grid', 'label' => __('Units in Stock'), 'value' => number_format(InventoryItem::sum('quantity')), 'hint' => __('Across all items')],
        ['icon' => 'exclamation-triangle', 'label' => __('Low Stock'), 'value' => number_format($lowCount), 'hint' => $lowCount ? __('At or below reorder level') : __('Everything is well stocked'), 'alert' => $lowCount > 0],
        ['icon' => 'receipt', 'label' => __('Stock Value'), 'value' => 'Rs '.number_format(InventoryItem::selectRaw('coalesce(sum(quantity * unit_price), 0) as value')->value('value'), 2), 'hint' => __('Quantity × unit price')],
    ];
@endphp

<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="hidden h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-white sm:flex">
                        <x-icon name="archive" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Inventory') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-4xl">{{ __('Parts & Supplies') }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full bg-black/20 px-3 py-1.5 text-xs font-medium text-white">
                        {{ __(':count item(s).', ['count' => $items->total()]) }}
                    </span>
                    <a href="{{ route('admin.inventory.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rx-accent shadow-lg transition hover:bg-blue-50">
                        <x-icon name="plus" class="h-4 w-4" /> {{ __('Add Item') }}
                    </a>
                </div>
            </div>
        </div>

        @if ($items->isEmpty())
            <div class="flex flex-col items-center rounded-3xl bg-rx-surface py-16 text-center ring-1 ring-rx-line">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rx-accent/15 text-blue-400">
                    <x-icon name="archive" class="h-7 w-7" />
                </span>
                <p class="mt-4 text-base font-medium text-white">{{ __('No inventory items yet.') }}</p>
                <a href="{{ route('admin.inventory.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                    <x-icon name="plus" class="h-4 w-4" /> {{ __('Add Item') }}
                </a>
            </div>
        @else
            <!-- Overview -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($stats as $stat)
                    @php $alert = $stat['alert'] ?? false; @endphp
                    <div class="rounded-3xl bg-rx-surface p-5 ring-1 {{ $alert ? 'ring-rose-500/30' : 'ring-rx-line' }}">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-zinc-400">{{ $stat['label'] }}</p>
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $alert ? 'bg-rose-500/10 text-rose-400' : 'bg-rx-accent/15 text-blue-400' }}">
                                <x-icon :name="$stat['icon']" class="h-4 w-4" />
                            </span>
                        </div>
                        <p class="mt-2 truncate text-3xl font-bold tracking-tight text-white">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-xs {{ $alert ? 'text-rose-400' : 'text-zinc-500' }}">{{ $stat['hint'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between px-1">
                <h2 class="text-lg font-semibold text-white">{{ __('Stock List') }}</h2>
                <p class="text-xs text-zinc-500">{{ __('Showing :from–:to of :total', ['from' => $items->firstItem(), 'to' => $items->lastItem(), 'total' => $items->total()]) }}</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($items as $item)
                    @php
                        $low = $item->isLowStock();
                        // Bar is full at three times the reorder level, so "healthy" stock reads as a long bar.
                        $full = max($item->reorder_level * 3, 1);
                        $level = (int) min(100, round($item->quantity / $full * 100));
                    @endphp
                    <div class="group relative flex flex-col overflow-hidden rounded-3xl bg-rx-surface ring-1 transition {{ $low ? 'ring-rose-500/30 hover:ring-rose-500/50' : 'ring-rx-line hover:ring-rx-accent/40' }}">
                        <div class="flex items-start justify-between gap-3 p-5 pb-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $low ? 'bg-rose-500/10 text-rose-400' : 'bg-rx-accent/15 text-blue-400' }}">
                                    <x-icon name="archive" class="h-5 w-5" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-white">{{ $item->name }}</p>
                                    <p class="truncate font-mono text-xs text-zinc-500">{{ $item->sku }}</p>
                                </div>
                            </div>
                            @if ($low)
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-rose-500/10 px-2 py-0.5 text-xs font-medium text-rose-300 ring-1 ring-inset ring-rose-500/30">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>
                                    {{ __('Low Stock') }}
                                </span>
                            @else
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-rx-accent/10 px-2 py-0.5 text-xs font-medium text-blue-400 ring-1 ring-inset ring-rx-accent/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-rx-accent"></span>
                                    {{ __('In Stock') }}
                                </span>
                            @endif
                        </div>

                        <!-- Stock level -->
                        <div class="px-5">
                            <div class="flex items-end justify-between">
                                <p class="text-3xl font-bold tracking-tight text-white">
                                    {{ number_format($item->quantity) }}
                                    <span class="text-sm font-medium text-zinc-500">{{ __('units') }}</span>
                                </p>
                                <p class="text-xs text-zinc-500">{{ __('Reorder at') }} {{ number_format($item->reorder_level) }}</p>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">
                                <span class="block h-full rounded-full {{ $low ? 'bg-rose-400' : 'bg-rx-accent' }}" style="width: {{ $level }}%"></span>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3 px-5">
                            <div class="rounded-2xl bg-white/[0.03] p-3 ring-1 ring-rx-line">
                                <p class="text-[11px] uppercase tracking-wide text-zinc-500">{{ __('Unit Price') }}</p>
                                <p class="mt-0.5 truncate text-sm font-semibold text-white">Rs {{ number_format($item->unit_price, 2) }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/[0.03] p-3 ring-1 ring-rx-line">
                                <p class="text-[11px] uppercase tracking-wide text-zinc-500">{{ __('Stock Value') }}</p>
                                <p class="mt-0.5 truncate text-sm font-semibold text-white">Rs {{ number_format($item->quantity * $item->unit_price, 2) }}</p>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between gap-2 border-t border-white/5 bg-white/[0.02] px-5 py-3">
                            <a href="{{ route('admin.inventory.edit', $item) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-white/5 px-3.5 py-2 text-xs font-semibold text-zinc-200 ring-1 ring-white/10 transition hover:bg-white/10" title="{{ __('Edit') }}">
                                <x-icon name="pencil" class="h-3.5 w-3.5" /> {{ __('Edit') }}
                            </a>
                            <x-confirm-modal
                                name="delete-item-{{ $item->id }}"
                                :action="route('admin.inventory.destroy', $item)"
                                variant="icon"
                                title="{{ __('Delete :name?', ['name' => $item->name]) }}"
                                body="{{ __('This action cannot be undone.') }}"
                                confirm-label="{{ __('Delete') }}"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($items->hasPages())
            <div class="rounded-2xl bg-rx-surface px-4 py-3 ring-1 ring-rx-line">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</x-dashboard-layout>
