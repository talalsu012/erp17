@props(['title'])

<section aria-label="{{ $title }}" {{ $attributes->merge(['class' => 'bg-white border border-gray-200 rounded-lg p-4 sm:p-6']) }}>
    {{ $slot }}
</section>
