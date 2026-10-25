<?php

use App\Filament\Resources\Users\UserResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Facades\Filament;

it('shows the roles menu in Arabic under the same group as the users', function () {
    $plugin = Filament::getPanel('admin')->getPlugin(FilamentShieldPlugin::make()->getId());

    expect($plugin->getNavigationLabel())->toBe('الأدوار')
        ->and($plugin->getNavigationGroup())->toBe('الصلاحيات')
        ->and(UserResource::getNavigationGroup())->toBe($plugin->getNavigationGroup());
});
