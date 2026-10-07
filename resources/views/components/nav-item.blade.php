@props(['active' => false, 'icon' => null])

@php
$classes = ($active ?? false)
    ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-[inset_2px_0_0_0_#4f46e5]'
    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900';
$iconClasses = ($active ?? false)
    ? 'text-indigo-600'
    : 'text-slate-400 group-hover:text-slate-600';
@endphp

<a {{ $attributes->merge(['class' => $classes.' group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition']) }}>
    @if ($icon)
        <x-icon :name="$icon" class="{{ $iconClasses }} h-5 w-5 shrink-0" />
    @endif
    {{ $slot }}
</a>
