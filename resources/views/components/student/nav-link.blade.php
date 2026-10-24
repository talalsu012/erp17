@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center self-center px-3 py-1.5 rounded-md bg-white/10 text-sm font-medium text-white focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center self-center px-3 py-1.5 rounded-md text-sm font-medium text-gray-300 hover:bg-white/10 hover:text-white focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
