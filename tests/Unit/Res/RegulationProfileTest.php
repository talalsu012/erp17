<?php

use App\Reg\AcademicSystem;
use App\Services\Res\RegulationProfile;
use Tests\TestCase;

uses(TestCase::class);

it('ships the Red Sea University regulation as the default profile', function () {
    $profile = RegulationProfile::current();

    expect($profile->academicSystem())->toBe(AcademicSystem::Semester)
        ->and($profile->defaultPlan())->toBe('20060001')
        ->and($profile->passMark())->toBe(50)
        ->and($profile->failFraction('repeat_above'))->toBe([1, 3])
        ->and($profile->failFraction('dismiss_above'))->toBe([2, 3])
        ->and($profile->finalYearLevels())->toBe(2)
        ->and($profile->excludedReason())->toBe(21)
        ->and($profile->absentReasons())->toBe([3001, 18])
        ->and($profile->supplementaryMaxMark())->toBe(50)
        ->and($profile->supplementaryPassPoint())->toBe(2.0)
        ->and($profile->repeatAllAbovePct())->toBe(70)
        ->and($profile->repeatSomeAbovePct())->toBe(50)
        ->and($profile->removeFailMaxSubjects())->toBe(1)
        ->and($profile->repeatHistoryCodes())->toBe([2001, 2002])
        ->and($profile->externalHistoryCodes())->toBe([4005])
        ->and($profile->minGpa())->toBe(2.0)
        ->and($profile->absenceWarningPct())->toBe(15)
        ->and($profile->absenceDeprivationPct())->toBe(25)
        ->and($profile->countExcusedAsAbsent())->toBeTrue()
        ->and(array_column($profile->graduationBands(), 'min'))->toBe([3.5, 3.0, 2.5]);
});

it('applies faculty overrides, merging maps and replacing lists', function () {
    config(['regulations.faculties' => [
        '10' => [
            'decisions' => ['final_year_levels' => 1, 'repeat_history_codes' => [2001]],
            'absence' => ['warning_pct' => 20],
        ],
    ]]);

    $overridden = RegulationProfile::forFaculty(10);

    expect($overridden->finalYearLevels())->toBe(1)
        ->and($overridden->repeatHistoryCodes())->toBe([2001])
        ->and($overridden->failFraction('dismiss_above'))->toBe([2, 3])
        ->and($overridden->absenceWarningPct())->toBe(20)
        ->and($overridden->absenceDeprivationPct())->toBe(25)
        ->and(RegulationProfile::forFaculty(99)->finalYearLevels())->toBe(2)
        ->and(RegulationProfile::current()->absenceWarningPct())->toBe(15);
});

it('falls back to the semester system for an unknown academic system value', function () {
    config(['regulations.academic_system' => 'trimester']);

    expect(RegulationProfile::current()->academicSystem())->toBe(AcademicSystem::Semester);

    config(['regulations.academic_system' => 'semester_annual']);

    expect(RegulationProfile::current()->academicSystem())->toBe(AcademicSystem::SemesterAnnual)
        ->and(AcademicSystem::SemesterAnnual->supplementsAtYearEnd())->toBeTrue()
        ->and(AcademicSystem::Semester->supplementsAtYearEnd())->toBeFalse();
});
