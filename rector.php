<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Rector\ClassMethod\ScopeNamedClassMethodToScopeAttributedClassMethodRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/routes',
        __DIR__.'/database',
    ])
    ->withSkip([
        __DIR__.'/database/migrations',
        ScopeNamedClassMethodToScopeAttributedClassMethodRector::class,
        __DIR__.'/app/Services/Card/StudentCardService copy.php',
        __DIR__.'/app/Helpers/Res/Services/MarkCalculationServiceCopy.php',
    ])
    ->withPhpSets(php82: true)
    ->withSets([LaravelSetList::LARAVEL_120]);
