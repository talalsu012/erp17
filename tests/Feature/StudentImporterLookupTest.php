<?php

use App\Filament\Imports\Reg\StudentImporter;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    migrateErpSchema();

    DB::table('reg_faculties')->insert([['code' => 101, 'name_ar' => 'كلية الحاسوب', 'name_en' => 'Computer'], ['code' => 102, 'name_ar' => 'كلية الطب', 'name_en' => 'Medicine']]);
    DB::table('reg_levels')->insert([['code' => 1, 'name_ar' => 'الفصل الأول', 'name_en' => 'First'], ['code' => 2, 'name_ar' => 'الفصل الثاني', 'name_en' => 'Second']]);
    DB::table('reg_departments')->insert([
        ['code' => 10101, 'faculty_code' => 101, 'name_ar' => 'علوم', 'name_en' => 'Science'],
        ['code' => 10201, 'faculty_code' => 102, 'name_ar' => 'علوم', 'name_en' => 'Science'],
    ]);
    DB::table('reg_ltcolumns')->insert([['code' => 10010001, 'name_ar' => 'ذكر', 'name_en' => 'male', 'grouped' => 1001]]);
});

function importerCall(string $method, mixed ...$arguments): mixed
{
    return (new ReflectionMethod(StudentImporter::class, $method))->invoke(null, ...$arguments);
}

it('passes a code through and converts a name to its code', function () {
    expect(importerCall('resolveByName', '101', 'reg_faculties'))->toBe(101)
        ->and(importerCall('resolveByName', '١٠١', 'reg_faculties'))->toBe(101)
        ->and(importerCall('resolveByName', 'كلية الطب', 'reg_faculties'))->toBe(102)
        ->and(importerCall('resolveByName', 'first', 'reg_levels'))->toBe(1)
        ->and(importerCall('resolveByName', 'الفصل الثاني', 'reg_levels'))->toBe(2);
});

it('leaves an unknown or ambiguous name as text so validation reports it', function () {
    expect(importerCall('resolveByName', 'كلية غير موجودة', 'reg_faculties'))->toBe('كلية غير موجودة')
        ->and(importerCall('resolveByName', 'علوم', 'reg_departments'))->toBe('علوم');
});

it('resolves a department name inside the row faculty only', function () {
    expect(importerCall('resolveByName', 'علوم', 'reg_departments', ['faculty_code' => 102]))->toBe(10201)
        ->and(importerCall('resolveByName', 'Science', 'reg_departments', ['faculty_code' => 101]))->toBe(10101);
});

it('resolves list values by code or name within the group', function () {
    expect(importerCall('resolveLtColumn', 'ذكر', 1001))->toBe(10010001)
        ->and(importerCall('resolveLtColumn', 'MALE', 1001))->toBe(10010001)
        ->and(importerCall('resolveLtColumn', '10010001', 1001))->toBe(10010001)
        ->and(importerCall('resolveLtColumn', 'غير معروف', 1001))->toBe('غير معروف');
});

it('shows five example rows for every column', function () {
    foreach (StudentImporter::getColumns() as $column) {
        expect($column->getExamples())->toHaveCount(5, $column->getName());
    }
});
