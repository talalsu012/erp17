<?php

namespace App\Filament\Clusters\AcademicSupervision;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class AcademicSupervisionCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'main.academic_supervision';

    public static function getNavigationLabel(): string
    {
        return ucfirst(__(static::$title));
    }

    public static function getClusterBreadcrumb(): string
    {
        return ucfirst(__(static::$title));
    }
}
