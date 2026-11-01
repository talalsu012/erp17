<?php

use App\Filament\Clusters\Res\Pages\DashboardResult;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    migrateErpSchema();

    DB::table('subjects')->insert([
        ['code' => 1, 'name_ar' => 'أ', 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1],
        ['code' => 2, 'name_ar' => 'ب', 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1],
        ['code' => 3, 'name_ar' => 'ج', 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 2],
    ]);

    $this->page = new DashboardResult;
    $this->page->faculty = 10;
    $this->page->department = 100;
    $this->page->level = 1;
});

it('selects every subject of the level when none is chosen', function () {
    expect($this->page->effectiveSubjects())->toBe(['1', '2']);
});

it('keeps the chosen subjects when some are selected', function () {
    $this->page->subjects = ['2'];

    expect($this->page->effectiveSubjects())->toBe(['2']);
});
