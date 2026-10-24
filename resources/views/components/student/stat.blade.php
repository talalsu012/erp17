@props(['label', 'value', 'tone' => 'gray'])

@php
    $border = [
        'blue' => 'border-blue-500',
        'green' => 'border-emerald-500',
        'red' => 'border-red-500',
        'gray' => 'border-gray-300 dark:border-gray-600',
    ][$tone] ?? 'border-gray-300';
@endphp

<div class="rounded-md bg-gray-50 dark:bg-gray-900/40 border-r-4 {{ $border }} px-4 py-3">
    <dt class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $label }}</dt>
    <dd class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ $value }}</dd>
</div>
