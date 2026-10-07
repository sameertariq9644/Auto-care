@props(['status'])

@php
    $styles = [
        'pending' => 'bg-white/5 text-zinc-300 ring-white/15',
        'confirmed' => 'bg-rx-accent/10 text-blue-400 ring-rx-accent/20',
        'in_progress' => 'bg-rx-accent/20 text-blue-300 ring-rx-accent/50',
        'completed' => 'bg-rx-accent text-white ring-rx-accent',
        'cancelled' => 'bg-white/[0.02] text-zinc-500 ring-white/10',
        'unpaid' => 'bg-white/5 text-zinc-300 ring-white/15',
        'paid' => 'bg-rx-accent text-white ring-rx-accent',
    ];
    $dots = [
        'pending' => 'bg-zinc-400',
        'confirmed' => 'bg-rx-accent',
        'in_progress' => 'bg-blue-300',
        'completed' => 'bg-white',
        'cancelled' => 'bg-zinc-600',
        'unpaid' => 'bg-zinc-400',
        'paid' => 'bg-white',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg px-2.5 py-1 text-xs font-medium ring-1 ring-inset '.($styles[$status] ?? 'bg-white/5 text-zinc-300 ring-white/10')]) }}>
    <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $dots[$status] ?? 'bg-zinc-400' }}"></span>
    {{ str($status)->replace('_', ' ')->title() }}
</span>
