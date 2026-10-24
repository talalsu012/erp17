<?php

namespace App\Providers;

use App\Models\Reg\Payment;
use App\Observers\PaymentAdmissionObserver;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en']); // also accepts a closure
        });

        FilamentColor::register([

            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => Color::Amber,
            'success' => Color::Green,
            'warning' => Color::Amber,
            'secondary' => Color::Indigo,
            'Talal' => [
                50 => 'oklch(0.969 0.015 12.422)',
            ],
        ]);

        Payment::observe(PaymentAdmissionObserver::class);

        RateLimiter::for('student-register', fn (Request $request): array => [
            Limit::perHour((int) config('student.rate_limits.register_per_ip_per_hour'))->by('ip:'.$request->ip()),
            Limit::perHour((int) config('student.rate_limits.register_per_university_no_per_hour'))
                ->by('uni:'.$request->input('university_no')),
        ]);

        RateLimiter::for('student-verify', fn (Request $request): array|Limit => config('student.rate_limits.verify_throttle_enabled')
            ? [
                Limit::perHour((int) config('student.rate_limits.verify_per_ip_per_hour'))->by('ip:'.$request->ip()),
                Limit::perHour((int) config('student.rate_limits.verify_per_university_no_per_hour'))
                    ->by('verify-uni:'.$request->input('university_no')),
            ]
            : Limit::none());

        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }
    }
}
