<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

it('adds and removes the student listing indexes idempotently', function () {
    migrateErpSchema();
    $migration = '2026_10_21_162107_add_listing_indexes_to_students_table.php';

    foreach (range(1, 2) as $_) {
        Artisan::call('migrate', ['--path' => 'database/migrations/'.$migration, '--no-interaction' => true]);
    }

    expect(Schema::hasIndex('students', 'students_faculty_level_batch_index'))->toBeTrue()
        ->and(Schema::hasIndex('students', 'students_created_at_index'))->toBeTrue();

    Artisan::call('migrate:rollback', ['--path' => 'database/migrations/'.$migration, '--no-interaction' => true]);

    expect(Schema::hasIndex('students', 'students_faculty_level_batch_index'))->toBeFalse()
        ->and(Schema::hasIndex('students', 'students_created_at_index'))->toBeFalse();
});
