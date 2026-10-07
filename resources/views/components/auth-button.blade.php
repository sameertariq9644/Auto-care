@props(['icon' => 'arrow-right'])

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rx-accent px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover focus:outline-none focus:ring-2 focus:ring-rx-accent focus:ring-offset-2 focus:ring-offset-rx-surface']) }}>
    {{ $slot }}
    @if ($icon)
        <x-icon :name="$icon" class="h-4 w-4 transition group-hover:translate-x-0.5" />
    @endif
</button>
