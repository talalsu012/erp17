<?php

namespace App\Filament\Clusters\Res;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

class ResCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = 2;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    protected static ?string $title = 'main.results';

    public static function getNavigationLabel(): string
    {
        return ucfirst(__(static::$title));
    }

    public static function getClusterBreadcrumb(): string
    {
        return ucfirst(__(static::$title));
    }
}
