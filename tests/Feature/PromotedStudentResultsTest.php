<?php

use App\Models\Student;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    migrateErpSchema();

    DB::table('reg_faculties')->insert(['code' => 10, 'name_ar' => 'كلية']);
    DB::table('reg_levels')->insert([['code' => 10, 'name_ar' => 'العاشر'], ['code' => 11, 'name_ar' => 'الحادي عشر']]);

    foreach ([1 => 10, 2 => 11, 3 => 11] as $number => $level) {
        Student::query()->forceCreate([
            'university_no' => $number, 'name_ar' => "طالب {$number}", 'faculty_code' => 10, 'department_code' => 100,
            'level_code' => $level, 'batch_code' => $number === 1 ? 2026 : 2027,
        ]);
    }

    foreach ([1, 2] as $number) {
        DB::table('res_recoms')->insert([
            'university_no' => $number, 'faculty_code' => 10, 'level_code' => 10, 'batch_code' => 2026, 'semester' => '2', 'examType' => '1', 'recom_code' => 1005,
        ]);
    }
});

function printedStudents(): array
{
    $data = base64_encode(json_encode(['faculty' => 10, 'level' => 10, 'batch' => 2026, 'semester' => 2, 'absent' => 1]));

    return test()->get(route('student.results', ['dataForm' => $data]))->assertOk()->viewData('students')->pluck('university_no')->all();
}

it('still prints the result of a level for students already promoted out of it', function () {
    actingAsSuperAdmin();

    expect(printedStudents())->toBe([1, 2]);
});

it('keeps the result sheet roster after the students were promoted to the next level', function () {
    $user = actingAsSuperAdmin();

    $sheet = app(App\Services\Res\ResultSheetService::class)->build(
        ['faculty' => 10, 'level' => 10, 'batch' => 2026, 'absent' => 1, 'fields' => []],
        $user,
    );

    expect($sheet['students']->pluck('university_no')->sort()->values()->all())->toBe([1, 2]);
});
