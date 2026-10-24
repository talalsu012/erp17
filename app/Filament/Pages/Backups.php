<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Contracts\Support\Htmlable;
use ShuvroRoy\FilamentSpatieLaravelBackup\Pages\Backups as BaseBackups;

class Backups extends BaseBackups
{
    use HasPageShield;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    public function getHeading(): string|Htmlable
    {
        return 'النسخ الاحتياطية';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'الصلاحيات';
    }
}
