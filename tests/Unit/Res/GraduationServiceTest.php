<?php

use App\Services\Res\GraduationService;
use Tests\TestCase;

uses(TestCase::class);

it('computes the gpa as weighted points over hours', function () {
    expect(GraduationService::gpa(0, 0))->toBe(0.0)
        ->and(GraduationService::gpa(66.0, 22))->toBe(3.0)
        ->and(GraduationService::gpa(10, 3))->toBe(3.33);
});

it('requires a cumulative gpa of at least 2.00 to graduate', function (float $gpa, bool $expected) {
    expect(GraduationService::meetsGraduation($gpa))->toBe($expected);
})->with([[1.99, false], [2.0, true], [4.0, true]]);

it('classifies the degree', function (float $gpa, ?string $general, ?string $honours) {
    expect(GraduationService::classify($gpa))->toBe($general)
        ->and(GraduationService::classify($gpa, true))->toBe($honours);
})->with([
    [1.99, null, null],
    [2.0, 'مقبول', 'مرتبة الشرف الثالثة'],
    [2.49, 'مقبول', 'مرتبة الشرف الثالثة'],
    [2.5, 'جيد', 'مرتبة الشرف الثانية (القسم الثاني)'],
    [3.0, 'جيد جداً', 'مرتبة الشرف الثانية (القسم الأول)'],
    [3.49, 'جيد جداً', 'مرتبة الشرف الثانية (القسم الأول)'],
    [3.5, 'ممتاز', 'مرتبة الشرف الأولى'],
]);
