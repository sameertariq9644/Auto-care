@php
    $input = 'block w-full rounded-xl border-0 bg-white/5 px-4 py-3 text-sm text-white placeholder-zinc-500 ring-1 ring-inset ring-rx-line transition [color-scheme:dark] focus:bg-white/[0.07] focus:ring-2 focus:ring-rx-accent';
    $itemErrors = collect($errors->get('items'))
        ->merge(collect($errors->get('items.*.inventory_item_id'))->flatten())
        ->merge(collect($errors->get('items.*.description'))->flatten())
        ->merge(collect($errors->get('items.*.quantity'))->flatten())
        ->merge(collect($errors->get('items.*.unit_price'))->flatten());
@endphp

<div
    class="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_300px]"
    x-data="{
        items: {{ json_encode($initialItems) }},
        tax: {{ json_encode((float) $tax) }},
        stock: {{ json_encode($stock) }},
        part(item) {
            return this.stock.find(p => p.id == item.inventory_item_id) || null;
        },
        pick(item) {
            const part = this.part(item);
            if (! part) return;
            item.description = part.name;
            item.unit_price = part.unit_price;
        },
        get subtotal() {
            return this.items.reduce((s, i) => s + (Number(i.quantity) || 0) * (Number(i.unit_price) || 0), 0);
        },
    }"
>
    <!-- Line items -->
    <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                    <x-icon name="receipt" class="h-4 w-4" />
                </span>
                <div>
                    <p class="block text-sm font-medium text-zinc-200">{{ __('Work Done & Parts Used') }}</p>
                    <p class="mt-0.5 text-xs text-zinc-500">{{ __('Pick a stock item to bill a part (its stock goes down), or leave it as custom for labor.') }}</p>
                </div>
            </div>
            <button type="button" @click="items.push({ inventory_item_id: '', description: '', quantity: 1, unit_price: 0 })" class="inline-flex items-center gap-1.5 rounded-xl bg-rx-accent/10 px-3 py-2 text-xs font-semibold text-blue-400 ring-1 ring-rx-accent/20 transition hover:bg-rx-accent/20">
                <x-icon name="plus" class="h-3.5 w-3.5" /> {{ __('Add Line') }}
            </button>
        </div>

        <div class="mt-5 space-y-3">
            <template x-for="(item, index) in items" :key="index">
                <div class="rounded-2xl bg-white/[0.02] p-3 ring-1 ring-rx-line">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-12">
                        <div class="col-span-2 sm:col-span-4">
                            <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">{{ __('Stock Item') }}</label>
                            <select :name="`items[${index}][inventory_item_id]`" x-model="item.inventory_item_id" @change="pick(item)" class="{{ $input }}">
                                <option value="">{{ __('Custom / labor') }}</option>
                                @foreach ($stock as $part)
                                    <option value="{{ $part['id'] }}">{{ $part['name'] }} ({{ $part['available'] }} {{ __('in stock') }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-4">
                            <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">{{ __('Description') }}</label>
                            <input type="text" :name="`items[${index}][description]`" x-model="item.description" placeholder="{{ __('e.g. Brake pad replacement labor') }}" required class="{{ $input }}">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">{{ __('Qty') }}</label>
                            <input type="number" min="1" :max="part(item) ? part(item).available : null" :name="`items[${index}][quantity]`" x-model="item.quantity" required class="{{ $input }} !px-2 text-center">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-[11px] uppercase tracking-wide text-zinc-500">{{ __('Unit Price') }}</label>
                            <input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" x-model="item.unit_price" required class="{{ $input }}">
                        </div>
                        <div class="col-span-2 flex items-end justify-end sm:col-span-1">
                            <button type="button" @click="items.splice(index, 1)" class="flex h-11 w-9 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-rose-500/10 hover:text-rose-400" title="{{ __('Remove') }}">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2 px-1 text-xs">
                        <span>
                            <span x-show="part(item) && Number(item.quantity) <= part(item)?.available" class="text-zinc-500" x-text="part(item) ? part(item).available + ' {{ __('in stock — this quantity will be deducted') }}' : ''"></span>
                            <span x-show="part(item) && Number(item.quantity) > part(item)?.available" x-cloak class="text-rose-400" x-text="part(item) ? '{{ __('Only') }} ' + part(item).available + ' {{ __('in stock') }}' : ''"></span>
                        </span>
                        <span class="font-medium text-zinc-300" x-text="'{{ __('Line total') }}: Rs ' + ((Number(item.quantity) || 0) * (Number(item.unit_price) || 0)).toFixed(2)"></span>
                    </div>
                </div>
            </template>
            <p x-show="! items.length" x-cloak class="rounded-2xl border border-dashed border-white/10 py-6 text-center text-sm text-zinc-500">{{ __('No line items. Add one to get started.') }}</p>
        </div>

        @foreach ($itemErrors->unique() as $message)
            <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
        @endforeach
    </section>

    <!-- Summary -->
    <section class="h-fit rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
        <label for="tax" class="block text-sm font-medium text-zinc-200">{{ __('Tax') }}</label>
        <input id="tax" class="{{ $input }} mt-1.5" type="number" step="0.01" min="0" name="tax" x-model.number="tax" required />
        @error('tax') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror

        <dl class="mt-5 rounded-2xl bg-white/[0.03] p-4 ring-1 ring-rx-line">
            <div class="flex justify-between py-1 text-sm">
                <dt class="text-zinc-500">{{ __('Subtotal') }}</dt>
                <dd class="text-zinc-200" x-text="'Rs ' + subtotal.toFixed(2)"></dd>
            </div>
            <div class="flex justify-between py-1 text-sm">
                <dt class="text-zinc-500">{{ __('Tax') }}</dt>
                <dd class="text-zinc-200" x-text="'Rs ' + (Number(tax) || 0).toFixed(2)"></dd>
            </div>
            <div class="mt-2 flex justify-between border-t border-white/10 pt-3 text-lg font-bold text-white">
                <dt>{{ __('Total') }}</dt>
                <dd x-text="'Rs ' + (subtotal + (Number(tax) || 0)).toFixed(2)"></dd>
            </div>
        </dl>
    </section>
</div>
