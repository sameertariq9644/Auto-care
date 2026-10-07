@php
    $input = 'mt-1.5 block w-full rounded-xl border-0 bg-white/5 px-4 py-3 text-sm text-white placeholder-zinc-500 ring-1 ring-inset ring-rx-line transition [color-scheme:dark] focus:bg-white/[0.07] focus:ring-2 focus:ring-rx-accent';
    $label = 'block text-sm font-medium text-zinc-200';
    $hint = 'mt-0.5 text-xs text-zinc-500';
    $selectedServices = old('services', $vehicle->services ?? []);
    $serviceErrors = collect($errors->get('services'))->merge(collect($errors->get('services.*'))->flatten());
@endphp

<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

    <!-- Photo -->
    <section
        class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line"
        x-data="{
            preview: @js($vehicle->image_url ?? null),
            removed: false,
            dragging: false,
            pick(file) {
                if (! file) return;
                const dt = new DataTransfer();
                dt.items.add(file);
                this.$refs.input.files = dt.files;
                this.preview = URL.createObjectURL(file);
                this.removed = false;
            },
            clear() {
                this.$refs.input.value = '';
                this.preview = null;
                this.removed = true;
            },
        }"
    >
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                <x-icon name="eye" class="h-4.5 w-4.5" />
            </span>
            <div>
                <label for="image" class="{{ $label }}">{{ __('Vehicle Photo (optional)') }}</label>
                <p class="{{ $hint }}">{{ __('JPG, PNG or WEBP, up to 5 MB. A side or front-angle shot looks best.') }}</p>
            </div>
        </div>

        <input type="hidden" name="remove_image" :value="removed ? 1 : 0" />
        <input x-ref="input" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="pick($event.target.files[0])" />

        <!-- Preview -->
        <div x-show="preview" x-cloak class="relative mt-4 overflow-hidden rounded-2xl bg-gradient-to-b from-white/[0.06] to-transparent ring-1 ring-white/10">
            <img :src="preview" alt="" class="mx-auto h-56 w-full object-contain p-3" />
            <div class="absolute right-3 top-3 flex gap-2">
                <label for="image" class="flex h-9 cursor-pointer items-center gap-1.5 rounded-full bg-black/60 px-3 text-xs font-semibold text-white ring-1 ring-white/15 backdrop-blur hover:bg-black/80">
                    <x-icon name="pencil" class="h-3.5 w-3.5" /> {{ __('Change') }}
                </label>
                <button type="button" @click="clear()" class="flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-zinc-300 ring-1 ring-white/15 backdrop-blur hover:text-rose-400" title="{{ __('Remove photo') }}">
                    <x-icon name="trash" class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- Drop zone -->
        <label
            for="image"
            x-show="! preview"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="dragging = false; pick($event.dataTransfer.files[0])"
            :class="dragging ? 'border-rx-accent bg-rx-accent/10' : 'border-white/15 bg-white/[0.02] hover:border-rx-accent/70 hover:bg-blue-500/5'"
            class="mt-4 flex h-56 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed px-6 text-center transition"
        >
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rx-accent text-white shadow-lg shadow-rx-accent/30">
                <x-icon name="download" class="h-6 w-6 rotate-180" />
            </span>
            <span class="mt-4 text-sm font-medium text-white">{{ __('Click to upload') }} <span class="font-normal text-zinc-500">{{ __('or drag and drop') }}</span></span>
        </label>

        @error('image') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
    </section>

    <!-- Details -->
    <section class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                <x-icon name="car" class="h-4.5 w-4.5" />
            </span>
            <div>
                <p class="{{ $label }}">{{ __('Vehicle Details') }}</p>
                <p class="{{ $hint }}">{{ __('Basic information about your car.') }}</p>
            </div>
        </div>

        <!-- Make -->
        <div class="mt-5">
            <label for="make" class="{{ $label }}">{{ __('Make') }}</label>
            <p class="{{ $hint }}">{{ __('The manufacturer or brand of your vehicle.') }}</p>
            <input id="make" class="{{ $input }}" type="text" name="make" placeholder="{{ __('e.g. Toyota, Honda, Ford') }}" value="{{ old('make', $vehicle->make ?? '') }}" required autofocus />
            @error('make') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- Model -->
            <div>
                <label for="model" class="{{ $label }}">{{ __('Model') }}</label>
                <input id="model" class="{{ $input }}" type="text" name="model" placeholder="{{ __('e.g. Corolla, Civic') }}" value="{{ old('model', $vehicle->model ?? '') }}" required />
                @error('model') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
            </div>

            <!-- Year -->
            <div>
                <label for="year" class="{{ $label }}">{{ __('Year') }}</label>
                <input id="year" class="{{ $input }}" type="number" name="year" placeholder="{{ __('e.g. 2022') }}" min="1900" max="{{ date('Y') + 1 }}" value="{{ old('year', $vehicle->year ?? '') }}" required />
                @error('year') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
            </div>
        </div>
        <p class="mt-1 {{ $hint }}">{{ __('Model is the car\'s name (e.g. Corolla). Year is the model year on your registration (e.g. 2022).') }}</p>

        <!-- Plate Number -->
        <div class="mt-4">
            <label for="plate_number" class="{{ $label }}">{{ __('Plate Number') }}</label>
            <input id="plate_number" class="{{ $input }} font-mono uppercase tracking-wider" type="text" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number ?? '') }}" required />
            @error('plate_number') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
        </div>

        <!-- VIN -->
        <div class="mt-4">
            <label for="vin" class="{{ $label }}">{{ __('VIN (optional)') }}</label>
            <p class="{{ $hint }}">{{ __('Vehicle Identification Number — a 17-character code found on your registration, insurance card, or lower windshield.') }}</p>
            <input id="vin" class="{{ $input }} font-mono uppercase" type="text" name="vin" maxlength="17" placeholder="{{ __('e.g. 1HGCM82633A123456') }}" value="{{ old('vin', $vehicle->vin ?? '') }}" />
            @error('vin') <p class="mt-2 text-sm text-rose-400">{{ $message }}</p> @enderror
        </div>
    </section>

    <!-- Services -->
    <fieldset class="rounded-3xl bg-rx-surface p-6 ring-1 ring-rx-line lg:col-span-2" x-data="{ selected: @js(array_values($selectedServices)) }">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                    <x-icon name="wrench" class="h-4.5 w-4.5" />
                </span>
                <div>
                    <legend class="{{ $label }}">{{ __('Services Wanted (optional)') }}</legend>
                    <p class="{{ $hint }}">{{ __('Select every service you\'d like for this vehicle. You can pick more than one.') }}</p>
                </div>
            </div>
            <span class="rounded-full bg-rx-accent/15 px-3 py-1 text-xs font-semibold text-blue-400" x-show="selected.length" x-text="selected.length + ' {{ __('selected') }}'"></span>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
            @foreach (\App\Models\ServiceBooking::SERVICE_TYPES as $service)
                <label
                    class="relative flex cursor-pointer flex-col items-center gap-3 rounded-2xl px-3 py-4 text-center text-sm font-medium transition"
                    :class="selected.includes(@js($service))
                        ? 'bg-rx-accent text-white shadow-lg shadow-rx-accent/30 -translate-y-0.5'
                        : 'bg-white/[0.03] text-zinc-300 ring-1 ring-white/10 hover:bg-white/[0.06] hover:text-white'"
                >
                    <input
                        type="checkbox"
                        name="services[]"
                        value="{{ $service }}"
                        x-model="selected"
                        class="absolute right-2.5 top-2.5 h-4 w-4 rounded border-white/20 bg-transparent text-blue-400 focus:ring-rx-accent focus:ring-offset-0"
                        @checked(in_array($service, $selectedServices))
                    />
                    <x-icon :name="[
                        'Oil Change' => 'beaker',
                        'Brake Inspection' => 'shield-check',
                        'Tire Rotation' => 'arrow-path',
                        'Battery Check' => 'bolt',
                        'General Inspection' => 'clipboard-check',
                        'Diagnostic' => 'cog',
                    ][$service] ?? 'ellipsis'" class="mt-2 h-7 w-7" />
                    {{ $service }}
                </label>
            @endforeach
        </div>

        @foreach ($serviceErrors as $message)
            <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
        @endforeach
    </fieldset>
</div>
