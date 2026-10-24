<?php

use App\Models\Res\Mark;
use App\Models\Res\Subject;
use App\Reg\RecomCode;
use App\Services\Res\RegulationService;
use Tests\TestCase;

uses(TestCase::class);

/** إحصائيات جاهزة: crch و crch_fail يحددان نسبة الرسوب */
function stats(float $crch = 20, float $crchFail = 0, int $fail = 0, int $abs = 0): array
{
    return ['crch' => $crch, 'crch_fail' => $crchFail, 's_fail' => $fail, 's_abs' => $abs];
}

function decide(int $exam, array $stats, bool $repeated = false, bool $external = false, bool $complete = true, bool $finalYear = false): RecomCode
{
    return RegulationService::decide($exam, $stats, $complete, fn () => $repeated, fn () => $external, $finalYear);
}

function markRow(?int $mark, float $point, float $crch, ?int $reason = null): Mark
{
    return (new Mark)
        ->forceFill(['mark' => $mark, 'point' => $point, 'reson_code' => $reason, 'subject_code' => uniqid()])
        ->setRelation('subject', (new Subject)->forceFill(['crch' => $crch]));
}

// ───────────── الامتحان الأساسي (examType = 1) ─────────────

it('returns incomplete when marks are missing, regardless of exam type', function (int $exam) {
    expect(decide($exam, stats(), complete: false))->toBe(RecomCode::Incomplete);
})->with([1, 2]);

it('passes a student with no failures or absences', function () {
    expect(decide(1, stats()))->toBe(RecomCode::Pass);
});

it('dismisses when failed credit hours exceed two thirds', function () {
    expect(decide(1, stats(21, 15, fail: 3)))->toBe(RecomCode::AcademicDismissal);
    expect(decide(1, stats(21, 14, fail: 3)))->not->toBe(RecomCode::AcademicDismissal);
});

it('repeats above one third up to two thirds, and dismisses if already repeated', function () {
    expect(decide(1, stats(21, 8, fail: 2)))->toBe(RecomCode::RepeatSome);
    expect(decide(1, stats(21, 14, fail: 2)))->toBe(RecomCode::RepeatSome);
    expect(decide(1, stats(21, 8, fail: 2), repeated: true))->toBe(RecomCode::AcademicDismissal);
});

it('gives supplementary / substitute up to one third', function () {
    expect(decide(1, stats(12, 4, fail: 1)))->toBe(RecomCode::Supplementary);
    expect(decide(1, stats(20, 3, fail: 1)))->toBe(RecomCode::Supplementary);
    expect(decide(1, stats(20, 0, abs: 1)))->toBe(RecomCode::Substitute);
    expect(decide(1, stats(20, 3, fail: 1, abs: 1)))->toBe(RecomCode::SubstituteAndSupplementary);
});

it('never dismisses by failure in the final year, it repeats above two thirds only', function () {
    expect(decide(1, stats(21, 20, fail: 4), finalYear: true))->toBe(RecomCode::RepeatSome);
    expect(decide(1, stats(21, 10, fail: 2), finalYear: true))->toBe(RecomCode::Supplementary);
    expect(decide(1, stats(21, 10, fail: 2), repeated: true, finalYear: true))->toBe(RecomCode::Supplementary);
});

// ───────────── الملاحق (examType = 2) ─────────────

it('repeats all courses above 70% unless already repeated', function () {
    expect(decide(2, stats(20, 16, fail: 4)))->toBe(RecomCode::RepeatAll);
    expect(decide(2, stats(20, 16, fail: 4), repeated: true))->toBe(RecomCode::AcademicDismissal);
});

it('repeats some courses between 50% and 70%', function () {
    expect(decide(2, stats(20, 12, fail: 3)))->toBe(RecomCode::RepeatSome);
});

it('removes a single failure once, then falls through to pass', function () {
    expect(decide(2, stats(20, 3, fail: 1)))->toBe(RecomCode::RemoveFail);
    expect(decide(2, stats(20, 3, fail: 1), external: true))->toBe(RecomCode::Pass);
});

it('passes a supplementary student with no failures', function () {
    expect(decide(2, stats()))->toBe(RecomCode::Pass);
});

it('returns incomplete for an unknown exam type', function () {
    expect(decide(9, stats()))->toBe(RecomCode::Incomplete);
});

it('does not query history unless a rule needs it', function () {
    $boom = fn () => throw new RuntimeException('history queried');

    // نجاح كامل وفصل بنسبة عالية: لا حاجة للتاريخ
    expect(RegulationService::decide(1, stats(), true, $boom, $boom))->toBe(RecomCode::Pass);
    expect(RegulationService::decide(1, stats(20, 16, fail: 4), true, $boom, $boom))->toBe(RecomCode::AcademicDismissal);
});

// ───────────── الإحصائيات ─────────────

it('computes credit hours, points, gpa and counts', function () {
    $service = new RegulationService;

    $stats = $service->statistics(collect([
        markRow(70, 3.0, 3),                              // ناجح
        markRow(40, 0.0, 2),                              // راسب
        markRow(null, 0.0, 1, RegulationService::REASON_ABSENT), // غائب بعذر
        markRow(90, 4.0, 5, RegulationService::REASON_EXCLUDED), // مستبعد: يخرج من كل شيء
    ]), subjectCount: 4);

    expect($stats)->toMatchArray([
        'countSubject' => 3,   // 4 مواد - 1 مستبعدة
        's_pass' => 1,
        's_fail' => 1,
        's_abs' => 1,
        'crch' => 6.0,         // 3 + 2 + 1
        'crch_fail' => 2.0,
        'points' => 9.0,       // 3.0*3
        'gpa' => 1.5,          // 9 / 6
        'sumMarks' => 110.0,   // 70 + 40
    ]);
});

it('counts the legacy excused-absence reason 18 like 3001', function () {
    $stats = (new RegulationService)->statistics(collect([
        markRow(70, 3.0, 3),
        markRow(null, 0.0, 2, 18),
        markRow(null, 0.0, 1, RegulationService::REASON_ABSENT),
    ]), subjectCount: 3);

    expect($stats['s_abs'])->toBe(2)->and($stats['s_fail'])->toBe(0);
    expect(RegulationService::isAbsentReason(18))->toBeTrue()
        ->and(RegulationService::isAbsentReason('3001'))->toBeTrue()
        ->and(RegulationService::isAbsentReason(22))->toBeFalse()
        ->and(RegulationService::isAbsentReason(null))->toBeFalse();
});

it('uses the plan pass mark so a diploma student passes at 40', function () {
    $marks = collect([markRow(45, 2.0, 3), markRow(30, 0.0, 2)]);

    $default = (new RegulationService)->statistics($marks, 2);
    $diploma = (new RegulationService)->statistics($marks, 2, 40);

    expect($default['s_pass'])->toBe(0)->and($default['s_fail'])->toBe(2)
        ->and($diploma['s_pass'])->toBe(1)->and($diploma['s_fail'])->toBe(1);
});

it('returns zero gpa when there are no credit hours', function () {
    expect((new RegulationService)->statistics(collect(), 0)['gpa'])->toBe(0);
});
