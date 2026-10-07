@php
    $input = 'mt-1.5 block w-full rounded-xl border-0 bg-white/5 px-4 py-3 text-sm text-white placeholder-zinc-500 ring-1 ring-inset ring-rx-line transition [color-scheme:dark] focus:bg-white/[0.07] focus:ring-2 focus:ring-rx-accent';
    $label = 'block text-sm font-medium text-zinc-200';
    $hint = 'mt-0.5 text-xs text-zinc-500';
@endphp

<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    <!-- Item -->
    <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                <x-icon name="archive" class="h-4 w-4" />
            </span>
            <div>
                <p class="{{ $label }}">{{ __('Item Details') }}</p>
                <p class="{{ $hint }}">{{ __('How this part is named and identified.') }}</p>
            </div>
        </div>

        <div class="mt-5">
            <label for="name" class="{{ $label }}">{{ __('Name') }}</label>
            <input id="name" class="{{ $input }}" type="text" name="name" value="{{ old('name', $item->name ?? '') }}" required autofocus />
            @error('name') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div class="mt-4">
            <label for="sku" class="{{ $label }}">{{ __('SKU') }}</label>
            <input id="sku" class="{{ $input }} font-mono uppercase tracking-wider" type="text" name="sku" value="{{ old('sku', $item->sku ?? '') }}" required />
            @error('sku') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div class="mt-4">
            <label for="unit_price" class="{{ $label }}">{{ __('Unit Price') }}</label>
            <input id="unit_price" class="{{ $input }}" type="number" step="0.01" min="0" name="unit_price" value="{{ old('unit_price', $item->unit_price ?? 0) }}" required />
            @error('unit_price') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
        </div>
    </section>

    <!-- Stock -->
    <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                <x-icon name="clipboard-check" class="h-4 w-4" />
            </span>
            <div>
                <p class="{{ $label }}">{{ __('Stock Levels') }}</p>
                <p class="{{ $hint }}">{{ __('Current quantity and when to reorder.') }}</p>
            </div>
        </div>

        <div class="mt-5">
            <label for="quantity" class="{{ $label }}">{{ __('Quantity') }}</label>
            <input id="quantity" class="{{ $input }}" type="number" min="0" name="quantity" value="{{ old('quantity', $item->quantity ?? 0) }}" required />
            @error('quantity') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div class="mt-4">
            <label for="reorder_level" class="{{ $label }}">{{ __('Reorder Level') }}</label>
            <p class="{{ $hint }}">{{ __('An alert is shown once quantity drops to this level or below.') }}</p>
            <input id="reorder_level" class="{{ $input }}" type="number" min="0" name="reorder_level" value="{{ old('reorder_level', $item->reorder_level ?? 0) }}" required />
            @error('reorder_level') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
        </div>
    </section>
</div>
