@props([
    'name',
    'action',
    'method' => 'DELETE',
    'title',
    'body' => null,
    'confirmLabel' => 'Confirm',
    'triggerLabel' => null,
    'variant' => 'button',
    'icon' => null,
])

<span x-data="">
    @if ($variant === 'icon')
        <button type="button" x-on:click="$dispatch('open-modal', '{{ $name }}')" title="{{ $triggerLabel ?? $confirmLabel }}" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600">
            <x-icon :name="$icon ?? 'trash'" class="h-4.5 w-4.5" />
        </button>
    @elseif ($variant === 'link')
        <button type="button" x-on:click="$dispatch('open-modal', '{{ $name }}')" class="text-red-600 hover:text-red-900 text-sm">
            {{ $triggerLabel ?? $confirmLabel }}
        </button>
    @else
        <x-danger-button type="button" x-on:click="$dispatch('open-modal', '{{ $name }}')">
            {{ $triggerLabel ?? $confirmLabel }}
        </x-danger-button>
    @endif

    <x-modal :name="$name" max-width="md">
        <form method="POST" action="{{ $action }}" class="p-6">
            @csrf
            @if (strtoupper($method) !== 'POST')
                @method($method)
            @endif

            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <x-icon name="exclamation-triangle" class="h-5 w-5" />
                </span>
                <div class="pt-1.5">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $title }}</h2>

                    @if ($body)
                        <p class="mt-1 text-sm text-slate-600">{{ $body }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button>
                    {{ $confirmLabel }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</span>
