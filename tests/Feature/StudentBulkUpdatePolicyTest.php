<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(fn () => migrateErpSchema());

it('lets only users holding the bulk update permission run the bulk student update', function () {
    $user = User::factory()->create();

    expect($user->can('bulkUpdate', App\Models\Student::class))->toBeFalse();

    $user->givePermissionTo(Permission::findOrCreate('BulkUpdate:Student', 'web'));

    expect($user->fresh()->can('bulkUpdate', App\Models\Student::class))->toBeTrue();
});
