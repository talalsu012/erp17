<?php

use App\Models\Reg\Faculty;
use App\Models\Res\Recom;
use App\Services\Res\YearResultService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    migrateErpSchema();

    DB::table('reg_faculties')->insert(['code' => 10, 'name_ar' => 'كلية تجريبية']);
    DB::table('reg_levels')->insert([
        ['code' => 1, 'name_ar' => 'الفصل الأول', 'parent_code' => 100],
        ['code' => 2, 'name_ar' => 'الفصل الثاني', 'parent_code' => 100],
        ['code' => 100, 'name_ar' => 'السنة الأولى', 'parent_code' => null],
    ]);

    foreach ([[1001, 'ناجحة كاملة'], [1002, 'راسب بمادة'], [1003, 'ينتظر ملحقًا']] as [$code, $name]) {
        DB::table('students')->insert(['university_no' => $code, 'name_ar' => $name, 'faculty_code' => 10, 'department_code' => 100]);
    }

    foreach ([[1005, 'نجاح'], [4001, 'ملحق'], [4005, 'إزالة رسوب']] as [$code, $name]) {
        DB::table('res_ltrecoms')->insert(['code' => $code, 'name_ar' => $name, 'is_visible' => 1]);
    }

    foreach ([[5, 1], [6, 1], [7, 2], [8, 2]] as [$code, $level]) {
        DB::table('subjects')->insert(['code' => $code, 'name_ar' => "مادة {$code}", 'faculty_code' => 10, 'department_code' => 100, 'level_code' => $level, 'crch' => 3]);
    }

    DB::table('res_grades')->insert([
        ['title' => 'A', 'upper' => 100, 'lower' => 80, 'point' => 4.0, 'plan' => 20060001],
        ['title' => 'D', 'upper' => 79, 'lower' => 50, 'point' => 2.0, 'plan' => 20060001],
        ['title' => 'F', 'upper' => 49, 'lower' => 0, 'point' => 0.0, 'plan' => 20060001],
    ]);

    $mark = function (int $student, int $subject, int $level, int $mark, float $point, int $exam = 1): void {
        DB::table('res_marks')->insert([
            'university_no' => $student, 'subject_code' => $subject, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => $level,
            'batch_code' => 2026, 'semester' => $level, 'examType' => $exam, 'mark' => $mark, 'point' => $point,
        ]);
    };

    foreach ([5, 6, 7, 8] as $subject) {
        $mark(1001, $subject, $subject <= 6 ? 1 : 2, 85, 4.0);
    }

    $mark(1002, 5, 1, 40, 0.0);
    $mark(1002, 6, 1, 80, 4.0);
    $mark(1002, 7, 2, 80, 4.0);
    $mark(1002, 8, 2, 80, 4.0);
    $mark(1002, 5, 1, 55, 2.0, 2);

    $mark(1003, 5, 1, 40, 0.0);
    $mark(1003, 6, 1, 80, 4.0);
    $mark(1003, 7, 2, 80, 4.0);
    $mark(1003, 8, 2, 80, 4.0);

    $recom = function (int $student, int $level, int $exam, int $code): void {
        Recom::query()->forceCreate([
            'university_no' => $student, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => $level, 'batch_code' => 2026,
            'semester' => $exam === 2 ? 3 : $level, 'examType' => $exam, 'recom_code' => $code,
        ]);
    };

    $recom(1001, 1, 1, 1005);
    $recom(1001, 2, 1, 1005);
    $recom(1002, 1, 1, 4001);
    $recom(1002, 2, 1, 1005);
    $recom(1002, 2, 2, 4005);
    $recom(1003, 1, 1, 4001);
    $recom(1003, 2, 1, 1005);

    $this->params = ['faculty' => 10, 'department' => 100, 'level' => 2, 'batch' => 2026];
});

function yearRow(array $report, int $universityNo): array
{
    return $report['rows']->first(fn (array $row): bool => (int) $row['student']->university_no === $universityNo);
}

it('combines both semesters of the academic year into one credit weighted result', function () {
    $report = app(YearResultService::class)->build($this->params, null);

    expect($report['levels']->pluck('code')->all())->toBe([1, 2])
        ->and($report['rows'])->toHaveCount(3);

    $row = yearRow($report, 1001);

    expect($row['year']['crch'])->toBe(12.0)
        ->and($row['year']['gpa'])->toBe(4.0)
        ->and($row['semesters'][1]['crch'])->toBe(6.0)
        ->and($row['semesters'][2]['crch'])->toBe(6.0)
        ->and($row['recom_code'])->toBe('1005')
        ->and($row['passed'])->toBeTrue();
});

it('lets the year-end supplementary replace the first sitting mark and settle the decision', function () {
    $row = yearRow(app(YearResultService::class)->build($this->params, null), 1002);

    expect($row['year']['s_fail'])->toBe(0)
        ->and($row['year']['gpa'])->toBe(3.5)
        ->and($row['recom_code'])->toBe('4005')
        ->and($row['recom_name'])->toBe('إزالة رسوب')
        ->and($row['passed'])->toBeTrue();
});

it('keeps a student with an unsettled failure pending until the year-end exams', function () {
    $report = app(YearResultService::class)->build($this->params, null);
    $row = yearRow($report, 1003);

    expect($row['year']['s_fail'])->toBe(1)
        ->and($row['recom_code'])->toBe('4001')
        ->and($row['pending'])->toBeTrue()
        ->and($row['passed'])->toBeFalse()
        ->and($report['summary']['passed'])->toBe(2)
        ->and($report['summary']['pending'])->toBe(1);
});

it('gives the same year when asked from either semester level', function () {
    $fromFirst = app(YearResultService::class)->build(['level' => 1] + $this->params, null);
    $fromSecond = app(YearResultService::class)->build($this->params, null);

    expect($fromFirst['rows']->count())->toBe($fromSecond['rows']->count())
        ->and($fromFirst['levels']->pluck('code')->all())->toBe([1, 2]);
});

it('prints the final year result page for staff', function () {
    actingAsSuperAdmin();

    $this->get(route('reports.year_result', $this->params))
        ->assertOk()
        ->assertSee('النتيجة النهائية للسنة الدراسية')
        ->assertSee('ينتظر ملحقًا')
        ->assertSee('إزالة رسوب');
});

it('keeps the final year result away from guests', function () {
    $this->get(route('reports.year_result', $this->params))->assertRedirect();
});

it('knows faculty 10 exists in the test data', function () {
    expect(Faculty::query()->find(10))->not->toBeNull();
});
