@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-s-4 border-emerald-400 text-start text-base font-medium text-white bg-white/10 focus:outline-none'
            : 'block w-full ps-3 pe-4 py-2 border-s-4 border-transparent text-start text-base font-medium text-gray-300 hover:text-white hover:bg-white/10 focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
