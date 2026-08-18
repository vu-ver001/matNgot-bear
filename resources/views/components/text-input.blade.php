@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-amber-200 focus:border-amber-500 focus:ring-amber-500 rounded-xl shadow-sm']) }}>
