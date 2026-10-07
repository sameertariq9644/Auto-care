@php
    use App\Models\Mechanic;
    use App\Models\ServiceBooking;

    $activeStatuses = [ServiceBooking::STATUS_CONFIRMED, ServiceBooking::STATUS_IN_PROGRESS];

    // Workload per mechanic on this page: [mechanic_id => [status => count]].
    $workload = ServiceBooking::whereIn('mechanic_id', $mechanics->pluck('id'))
        ->selectRaw('mechanic_id, status, count(*) as total')
        ->groupBy('mechanic_id', 'status')
        ->get()
        ->groupBy('mechanic_id')
        ->map(fn ($rows) => $rows->pluck('total', 'status'));

    $stats = [
        ['icon' => 'users', 'label' => __('Team Size'), 'value' => Mechanic::count(), 'hint' => __('Mechanics on the roster')],
        ['icon' => 'shield-check', 'label' => __('Active'), 'value' => Mechanic::where('status', 'active')->count(), 'hint' => __('Available for jobs')],
        ['icon' => 'wrench', 'label' => __('Open Jobs'), 'value' => ServiceBooking::whereNotNull('mechanic_id')->whereIn('status', $activeStatuses)->count(), 'hint' => __('Confirmed or in service')],
        ['icon' => 'check-circle', 'label' => __('Jobs Completed'), 'value' => ServiceBooking::whereNotNull('mechanic_id')->where('status', ServiceBooking::STATUS_COMPLETED)->count(), 'hint' => __('By the whole team')],
    ];
@endphp

<x-dashboard-layout>
    <div class="space-y-5 px-4 py-6 sm:px-6 lg:px-4 lg:py-4 lg:pr-6">

        <!-- Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-rx-accent px-6 py-6 shadow-xl shadow-rx-accent/30 sm:px-8">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="hidden h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-white sm:flex">
                        <x-icon name="users" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[.2em] text-white/80">{{ __('Team') }}</p>
                        <h1 class="text-2xl font-extrabold italic tracking-tight text-white sm:text-4xl">{{ __('Mechanics') }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full bg-black/20 px-3 py-1.5 text-xs font-medium text-white">
                        {{ __(':count mechanic(s).', ['count' => $mechanics->total()]) }}
                    </span>
                    <a href="{{ route('admin.mechanics.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-rx-accent shadow-lg transition hover:bg-blue-50">
                        <x-icon name="plus" class="h-4 w-4" /> {{ __('Add Mechanic') }}
                    </a>
                </div>
            </div>
        </div>

        @if ($mechanics->isEmpty())
            <div class="flex flex-col items-center rounded-3xl bg-rx-surface py-16 text-center ring-1 ring-rx-line">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rx-accent/15 text-blue-400">
                    <x-icon name="users" class="h-7 w-7" />
                </span>
                <p class="mt-4 text-base font-medium text-white">{{ __('No mechanics yet.') }}</p>
                <p class="mt-1 text-sm text-zinc-500">{{ __('Add your first team member to start assigning jobs.') }}</p>
                <a href="{{ route('admin.mechanics.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-rx-accent px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rx-accent/25 transition hover:bg-rx-accent-hover">
                    <x-icon name="plus" class="h-4 w-4" /> {{ __('Add Mechanic') }}
                </a>
            </div>
        @else
            <!-- Overview -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($stats as $stat)
                    <div class="rounded-3xl bg-rx-surface p-5 ring-1 ring-rx-line">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-zinc-400">{{ $stat['label'] }}</p>
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent/15 text-blue-400">
                                <x-icon :name="$stat['icon']" class="h-4 w-4" />
                            </span>
                        </div>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-white">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-xs text-zinc-500">{{ $stat['hint'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($mechanics as $mechanic)
                    @php
                        $active = $mechanic->status === 'active';
                        $jobs = $workload->get($mechanic->id, collect());
                        $openJobs = $jobs->get(ServiceBooking::STATUS_CONFIRMED, 0) + $jobs->get(ServiceBooking::STATUS_IN_PROGRESS, 0);
                        $doneJobs = $jobs->get(ServiceBooking::STATUS_COMPLETED, 0);
                    @endphp
                    <div class="group relative flex flex-col overflow-hidden rounded-3xl bg-rx-surface ring-1 ring-rx-line transition hover:-translate-y-0.5 hover:ring-rx-accent/40">
                        <!-- Header strip -->
                        <div class="relative h-20 bg-gradient-to-br from-rx-accent/25 via-rx-accent/5 to-transparent">
                            <div class="pointer-events-none absolute -right-8 -top-10 h-32 w-32 rounded-full bg-rx-accent/20 blur-2xl"></div>
                            <span @class([
                                'absolute right-4 top-4 inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium ring-1 ring-inset',
                                'bg-rx-accent/10 text-blue-400 ring-rx-accent/20' => $active,
                                'bg-white/5 text-zinc-400 ring-white/10' => ! $active,
                            ])>
                                <span class="h-1.5 w-1.5 rounded-full {{ $active ? 'bg-rx-accent' : 'bg-zinc-500' }}"></span>
                                {{ str($mechanic->status)->replace('_', ' ')->title() }}
                            </span>
                        </div>

                        <div class="-mt-9 flex flex-1 flex-col px-6 pb-6">
                            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-rx-accent text-2xl font-bold text-white shadow-lg shadow-rx-accent/30 ring-4 ring-rx-surface">
                                {{ str($mechanic->user->name)->substr(0, 1)->upper() }}
                            </span>

                            <p class="mt-3 truncate text-lg font-semibold text-white">{{ $mechanic->user->name }}</p>
                            <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs text-zinc-500">
                                <x-icon name="envelope" class="h-3.5 w-3.5 shrink-0" />
                                <span class="truncate">{{ $mechanic->user->email }}</span>
                            </p>

                            <div class="mt-4 flex items-center gap-3 rounded-2xl bg-white/[0.03] p-3 ring-1 ring-rx-line">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/5 text-zinc-300">
                                    <x-icon name="wrench" class="h-4 w-4" />
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[11px] uppercase tracking-wide text-zinc-500">{{ __('Specialty') }}</p>
                                    <p class="truncate text-sm font-medium text-white">{{ $mechanic->specialty ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div class="rounded-2xl bg-white/[0.03] p-3 text-center ring-1 ring-rx-line">
                                    <p class="text-2xl font-bold text-white">{{ $openJobs }}</p>
                                    <p class="text-[11px] uppercase tracking-wide text-zinc-500">{{ __('Open Jobs') }}</p>
                                </div>
                                <div class="rounded-2xl bg-white/[0.03] p-3 text-center ring-1 ring-rx-line">
                                    <p class="text-2xl font-bold text-white">{{ $doneJobs }}</p>
                                    <p class="text-[11px] uppercase tracking-wide text-zinc-500">{{ __('Completed') }}</p>
                                </div>
                            </div>

                            <p class="mt-auto pt-4 text-xs text-zinc-500">{{ __('Joined') }} {{ $mechanic->created_at->format('M j, Y') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($mechanics->hasPages())
                <div class="rounded-2xl bg-rx-surface px-4 py-3 ring-1 ring-rx-line">
                    {{ $mechanics->links() }}
                </div>
            @endif
        @endif
    </div>
</x-dashboard-layout>
