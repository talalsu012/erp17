<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsDashboardRequest;
use App\Services\Analytics\DashboardAnalyticsService;
use Illuminate\Contracts\View\View;

/**
 * نسخة بملء الشاشة من لوحة المؤشرات (الصفحة الرئيسية داخل النظام هي صفحة Dashboard).
 */
class AnalyticsDashboardController extends Controller
{
    public function __invoke(AnalyticsDashboardRequest $request, DashboardAnalyticsService $analytics): View
    {
        return view('analytics.dashboard', $analytics->pageData($request->user(), $request->validated()));
    }
}
