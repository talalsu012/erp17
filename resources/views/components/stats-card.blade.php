@props(['count' => 0, 'title' => 'إجمالي الطلاب'])

<div {{ $attributes->merge(['class' => 'filament-stats-card']) }}>
    <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-300 hover:shadow-md">
        <!-- عناصر زخرفية -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-primary-100/50 to-primary-50/50 dark:from-primary-900/20 dark:to-primary-800/20 rounded-full -mr-16 -mt-16"></div>
        
        <div class="relative">
            
            
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($count) }}
                </span>
            
            </div>
            
            @if($attributes->has('trend'))
            <div class="mt-3 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span class="text-xs text-success-600 dark:text-success-400">{{ $trend }}</span>
            </div>
            @endif
        </div>
    </div>
</div>