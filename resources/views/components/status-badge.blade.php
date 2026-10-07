@props(['status'])

@php
    $dots = [
        'pending' => 'bg-amber-500',
        'confirmed' => 'bg-indigo-500',
        'in_progress' => 'bg-indigo-500',
        'completed' => 'bg-emerald-500',
        'cancelled' => 'bg-red-500',
        'unpaid' => 'bg-amber-500',
        'paid' => 'bg-emerald-500',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-200']) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $dots[$status] ?? 'bg-slate-400' }}"></span>
    {{ str($status)->replace('_', ' ')->title() }}
</span>
