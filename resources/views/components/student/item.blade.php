@props(['label', 'value' => null, 'ltr' => false])

<div class="rounded-md border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 px-4 py-3">
    <dt class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $label }}</dt>
    <dd @if ($ltr) dir="ltr" @endif class="font-semibold text-sm text-gray-900 dark:text-gray-100 break-words {{ $ltr ? 'text-right' : '' }}">
        {{ filled($value) ? $value : '-' }}
    </dd>
</div>
