<div class="flex h-full flex-col">
    <div class="flex h-20 shrink-0 items-center px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rx-accent shadow-lg shadow-rx-accent/30">
                <x-application-logo class="h-5 w-auto fill-current text-white" />
            </span>
            <span class="text-lg font-bold italic tracking-tight text-white">AutoCare <span class="text-blue-400">Pro</span></span>
        </a>
    </div>

    <nav class="flex-1 space-y-1.5 overflow-y-auto px-3 py-2">
        @foreach ($links as $link)
            @php $active = request()->routeIs($link['match']); @endphp
            <a
                href="{{ route($link['route']) }}"
                @class([
                    'group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition',
                    'bg-rx-accent text-white shadow-lg shadow-rx-accent/25' => $active,
                    'text-zinc-400 hover:bg-white/5 hover:text-white' => ! $active,
                ])
            >
                <x-icon :name="$link['icon']" class="h-5 w-5 shrink-0" />
                <span class="flex-1">{{ $link['label'] }}</span>
                @if (! empty($link['badge']))
                    <span @class([
                        'flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[11px] font-semibold',
                        'bg-white text-rx-accent' => $active,
                        'bg-rx-accent text-white' => ! $active,
                    ])>{{ $link['badge'] }}</span>
                @endif
            </a>
        @endforeach

        <div class="!my-5 mx-4 border-t border-white/5"></div>

        <a href="{{ route($quickLink['route']) }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-zinc-400 transition hover:bg-white/5 hover:text-white">
            <x-icon :name="$quickLink['icon']" class="h-5 w-5 shrink-0" />
            {{ $quickLink['label'] }}
        </a>
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-zinc-400 transition hover:bg-white/5 hover:text-white">
            <x-icon name="user-circle" class="h-5 w-5 shrink-0" />
            {{ __('Settings') }}
        </a>
    </nav>

    <div class="shrink-0 p-3">
        <div class="flex items-center gap-2 rounded-2xl bg-white/[0.03] p-2 ring-1 ring-rx-line">
            <a href="{{ route('profile.edit') }}" class="flex min-w-0 flex-1 items-center gap-3 rounded-xl px-1 py-1">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-rx-accent text-sm font-semibold text-white">
                    {{ str($navUser->name)->substr(0, 1)->upper() }}
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-medium text-white">{{ $navUser->name }}</span>
                    <span class="block truncate text-xs text-zinc-500">{{ $navUser->email }}</span>
                </span>
            </a>
            <x-theme-toggle class="text-zinc-500 hover:bg-white/5 hover:text-white" />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="{{ __('Log out') }}" class="flex h-9 w-9 items-center justify-center rounded-xl text-zinc-500 transition hover:bg-white/5 hover:text-white">
                    <x-icon name="logout" class="h-5 w-5" />
                </button>
            </form>
        </div>
    </div>
</div>
