<x-dashboard-layout>
    <div class="px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">
        <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Banner -->
            <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
                <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative flex items-center gap-4">
                    <a href="{{ route('vehicles.show', $vehicle) }}" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white transition hover:bg-white/25" title="{{ __('Back') }}">
                        <x-icon name="chevron-right" class="h-5 w-5 rotate-180" />
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('My Vehicles') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-3xl">{{ __('Edit Vehicle') }}</h1>
                        <p class="mt-0.5 text-sm text-white/80">{{ __('Update this vehicle\'s details.') }}</p>
                    </div>
                </div>
            </div>

            @include('customer.vehicles._form')

            <div class="flex items-center justify-end gap-3 rounded-3xl bg-rx-surface px-6 py-4 ring-1 ring-rx-line">
                <a href="{{ route('vehicles.show', $vehicle) }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-zinc-400 transition hover:bg-white/5 hover:text-white">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover focus:outline-none focus:ring-2 focus:ring-rx-accent focus:ring-offset-2 focus:ring-offset-rx-surface">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</x-dashboard-layout>
