<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border border-slate-300 rounded-lg font-semibold text-sm text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
