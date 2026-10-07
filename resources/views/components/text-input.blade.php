@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-lg shadow-sm']) }}>
