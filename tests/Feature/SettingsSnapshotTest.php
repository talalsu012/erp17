<?php

use Database\Seeders\SettingsSeeder;

function settingsSnapshot(string $table): array
{
    return json_decode((string) file_get_contents(database_path("seeders/data/{$table}.json")), true, flags: JSON_THROW_ON_ERROR);
}

it('has a non-empty snapshot for every settings table', function (string $table) {
    expect(settingsSnapshot($table))->not->toBeEmpty();
})->with(fn () => array_map(
    fn (string $path): string => basename($path, '.json'),
    glob(__DIR__.'/../../database/seeders/data/*.json')
));

it('seeds every snapshot file', function () {
    $tables = (new ReflectionClassConstant(SettingsSeeder::class, 'TABLES'))->getValue();
    $files = array_map(fn (string $path): string => basename($path, '.json'), glob(database_path('seeders/data/*.json')));

    expect($tables)->toEqualCanonicalizing($files);
});

it('keeps department and faculty references consistent in the snapshot', function () {
    $faculties = array_column(settingsSnapshot('reg_faculties'), 'code');
    $departments = settingsSnapshot('reg_departments');

    foreach ($departments as $department) {
        expect($faculties)->toContain($department['faculty_code']);
    }
});

it('does not export users into the faculties snapshot', function () {
    foreach (settingsSnapshot('reg_faculties') as $faculty) {
        expect($faculty['results_approver_id'])->toBeNull();
    }
});
