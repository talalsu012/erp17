<?php

namespace App\Filament\Clusters\Reg;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class RegCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'main.registration';

    public static function getNavigationLabel(): string
    {
        return ucfirst(__(static::$title));
    }

    public static function getClusterBreadcrumb(): string
    {
        return ucfirst(__(static::$title));
    }
}
