{{-- Dark-theme form field used by the auth pages: label, icon, input (with show/hide for passwords) and errors. --}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'icon' => null, 'messages' => null])

@php
    $id = $attributes->get('id', $name);
    $isPassword = $type === 'password';
@endphp

<div @if ($isPassword) x-data="{ show: false }" @endif>
    <label for="{{ $id }}" class="block text-sm font-medium text-zinc-200">{{ $label }}</label>

    <div class="relative mt-1.5">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-500">
                <x-icon :name="$icon" class="h-5 w-5" />
            </span>
        @endif

        <input
            id="{{ $id }}"
            name="{{ $name }}"
            @if ($isPassword) :type="show ? 'text' : 'password'" type="password" @else type="{{ $type }}" @endif
            @unless ($isPassword) value="{{ $value }}" @endunless
            {{ $attributes->except('id')->class([
                'block w-full rounded-xl border-0 bg-white/5 py-3 text-sm text-white placeholder-zinc-500 ring-1 ring-inset ring-rx-line transition [color-scheme:dark] focus:bg-white/[0.07] focus:ring-2 focus:ring-rx-accent',
                'pl-12' => $icon,
                'pl-4' => ! $icon,
                'pr-12' => $isPassword,
                'pr-4' => ! $isPassword,
            ]) }}
        />

        @if ($isPassword)
            <button type="button" @click="show = ! show" class="absolute inset-y-0 right-0 flex items-center pr-4 text-zinc-500 transition hover:text-white" :aria-label="show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'">
                <x-icon name="eye" class="h-5 w-5" x-show="! show" />
                <x-icon name="x-mark" class="h-5 w-5" x-show="show" x-cloak />
            </button>
        @endif
    </div>

    @foreach ((array) ($messages ?? $errors->get($name)) as $message)
        <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
    @endforeach
</div>
