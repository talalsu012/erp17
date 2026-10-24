<?php

namespace App\Filament\Clusters\Cert;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class CertCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'main.certificate';

    public static function getNavigationLabel(): string
    {
        return ucfirst(__(static::$title));
    }

    public static function getClusterBreadcrumb(): string
    {
        return ucfirst(__(static::$title));
    }
}
