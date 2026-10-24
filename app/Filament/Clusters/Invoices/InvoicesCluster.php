<?php

namespace App\Filament\Clusters\Invoices;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class InvoicesCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'main.invoice';

    public static function getNavigationLabel(): string
    {
        return ucfirst(__(static::$title));
    }

    public static function getClusterBreadcrumb(): string
    {
        return ucfirst(__(static::$title));
    }
}
