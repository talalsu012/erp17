<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

/**
 * الصفحة الرئيسية للنظام: لوحة المؤشرات الأكاديمية والمالية داخل قالب Filament
 * فتبقى القوائم الجانبية والعلوية ظاهرة. نسخة بملء الشاشة على المسار /analytics.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'لوحة المؤشرات';

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('analytics.page'),
        ]);
    }
}
