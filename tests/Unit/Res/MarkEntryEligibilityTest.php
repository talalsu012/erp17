<?php

use App\Models\Res\Subject;
use App\Models\Student;
use App\Services\Res\MarkEntryService;
use App\Services\Res\RegulationService;
use Tests\TestCase;

uses(TestCase::class);

const BATCH = 2024;

function student(array $overrides = []): Student
{
    return (new Student)->forceFill($overrides + [
        'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1,
        'batch_code' => BATCH, 'stady_code' => '20060001', 'special_code' => null,
    ]);
}

function subject(array $overrides = []): Subject
{
    return (new Subject)->forceFill($overrides + [
        'code' => 5, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'special_code' => null,
    ]);
}

function eligible(Student $student, Subject $subject, $special = null): void
{
    (new MarkEntryService(new RegulationService))->assertEligible($student, $subject, BATCH, $special);
}

// ───────────── 1) بيانات الطالب الأساسية ─────────────

it('accepts a student whose core data matches the subject', function () {
    eligible(student(), subject());
})->throwsNoExceptions();

it('rejects a student with missing core data', function (string $column, string $label) {
    expect(fn () => eligible(student([$column => null]), subject()))
        ->toThrow(DomainException::class, $label);
})->with([
    ['faculty_code', 'الكلية'],
    ['department_code', 'القسم'],
    ['level_code', 'الفصل'],
    ['batch_code', 'العام الدراسي'],
    ['stady_code', 'الخطة الدراسية'],
]);

it('rejects a student from another faculty, level or batch', function (array $override) {
    expect(fn () => eligible(student($override), subject()))
        ->toThrow(DomainException::class, 'لا ينتمي');
})->with([
    [['faculty_code' => 11]],
    [['level_code' => 2]],
    [['batch_code' => 2023]],
]);

it('rejects a student from another department than the subject', function () {
    expect(fn () => eligible(student(['department_code' => 101]), subject()))
        ->toThrow(DomainException::class, 'قسم الطالب');
});

// ───────────── 2 + 3) تخصص مختار ─────────────

it('accepts when the selected special matches both student and subject', function () {
    eligible(student(['special_code' => 7]), subject(['special_code' => 7]), special: 7);
})->throwsNoExceptions();

it('rejects a selected special that the student does not have', function (?int $studentSpecial) {
    expect(fn () => eligible(student(['special_code' => $studentSpecial]), subject(['special_code' => 7]), special: 7))
        ->toThrow(DomainException::class, 'ليس ضمن التخصص');
})->with([[null], [8]]);

it('rejects a selected special that the subject does not have', function (?int $subjectSpecial) {
    expect(fn () => eligible(student(['special_code' => 7]), subject(['special_code' => $subjectSpecial]), special: 7))
        ->toThrow(DomainException::class, 'المادة لا تتبع');
})->with([[null], [8]]);

// ───────────── 4) العكس: مادة لها تخصص بدون اختيار تخصص ─────────────

it('requires the student to share the subject special even when none is selected', function () {
    expect(fn () => eligible(student(['special_code' => 8]), subject(['special_code' => 7])))
        ->toThrow(DomainException::class, 'المادة خاصة بتخصص');

    expect(fn () => eligible(student(['special_code' => null]), subject(['special_code' => 7])))
        ->toThrow(DomainException::class, 'المادة خاصة بتخصص');

    eligible(student(['special_code' => 7]), subject(['special_code' => 7]));
});

it('allows a student with a special to sit a common subject when no special is selected', function () {
    eligible(student(['special_code' => 7]), subject(['special_code' => null]));
})->throwsNoExceptions();
