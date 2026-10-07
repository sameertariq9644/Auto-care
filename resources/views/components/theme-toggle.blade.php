{{-- Light/dark switch. Pass classes to style it for its surroundings. --}}
<button
    type="button"
    x-data="{
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = ! this.dark;
            const root = document.documentElement;
            root.classList.toggle('dark', this.dark);
            root.classList.toggle('light', ! this.dark);
            try { localStorage.setItem('theme', this.dark ? 'dark' : 'light'); } catch (e) {}
        },
    }"
    @click="toggle()"
    :title="dark ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}'"
    :aria-label="dark ? '{{ __('Switch to light mode') }}' : '{{ __('Switch to dark mode') }}'"
    {{ $attributes->merge(['class' => 'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition']) }}
>
    <x-icon name="sun" class="h-5 w-5" x-show="dark" />
    <x-icon name="moon" class="h-5 w-5" x-show="! dark" x-cloak />
</button>
