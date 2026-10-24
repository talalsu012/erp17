@php
    $analyticsData = app(\App\Services\Analytics\DashboardAnalyticsService::class)
        ->pageData(auth()->user(), request()->only(['batch', 'faculty', 'department', 'level', 'exam']));
@endphp
@include('analytics._content', $analyticsData + ['standalone' => false])
