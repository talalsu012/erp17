<?php

use App\Reg\RecomCode;
use App\Services\Res\AbsenceService;
use App\Services\Res\GraduationService;
use App\Services\Res\RegulationService;
use Tests\TestCase;

uses(TestCase::class);

/**
 * @param  array<string, mixed>  $stats
 */
function decideWith(int $exam, array $stats, bool $finalYear = false): RecomCode
{
    return RegulationService::decide($exam, $stats + ['crch' => 20, 'crch_fail' => 0, 's_fail' => 0, 's_abs' => 0], true, fn () => false, fn () => false, $finalYear);
}

it('moves the supplementary repeat thresholds from the regulation profile', function () {
    $stats = ['crch_fail' => 13, 's_fail' => 4];

    expect(decideWith(2, $stats))->toBe(RecomCode::RepeatSome);

    config(['regulations.supplementary.repeat_all_above_pct' => 60]);

    expect(decideWith(2, $stats))->toBe(RecomCode::RepeatAll);

    config(['regulations.supplementary.repeat_some_above_pct' => 70, 'regulations.supplementary.repeat_all_above_pct' => 80]);

    expect(decideWith(2, $stats))->toBe(RecomCode::Pass);
});

it('allows removing the failure of more than one subject when the regulation says so', function () {
    $stats = ['crch_fail' => 2, 's_fail' => 2];

    expect(decideWith(2, $stats))->toBe(RecomCode::Pass);

    config(['regulations.supplementary.remove_fail_max_subjects' => 2]);

    expect(decideWith(2, $stats))->toBe(RecomCode::RemoveFail);
});

it('moves the first sitting repeat and dismissal fractions', function () {
    $stats = ['crch_fail' => 11, 's_fail' => 3];

    expect(decideWith(1, $stats))->toBe(RecomCode::RepeatSome);

    config(['regulations.decisions.dismiss_above' => [1, 2]]);

    expect(decideWith(1, $stats))->toBe(RecomCode::AcademicDismissal)
        ->and(decideWith(1, $stats, finalYear: true))->toBe(RecomCode::RepeatSome);
});

it('moves the graduation minimum and classification bands', function () {
    expect(GraduationService::meetsGraduation(1.9))->toBeFalse()
        ->and(GraduationService::classify(3.6))->toBe('ممتاز');

    config(['regulations.graduation.min_gpa' => 1.5, 'regulations.graduation.bands' => [
        ['min' => 3.8, 'general' => 'امتياز', 'honours' => 'شرف'],
    ]]);

    expect(GraduationService::meetsGraduation(1.9))->toBeTrue()
        ->and(GraduationService::classify(3.6))->toBe('مقبول')
        ->and(GraduationService::classify(3.9))->toBe('امتياز')
        ->and(GraduationService::classify(1.2))->toBeNull();
});

it('moves the absence warning and deprivation percentages', function () {
    expect(AbsenceService::classify(20)->name)->toBe('Warning');

    config(['regulations.absence.warning_pct' => 30, 'regulations.absence.deprivation_pct' => 40]);

    expect(AbsenceService::classify(20)->name)->toBe('Normal')
        ->and(AbsenceService::classify(41)->name)->toBe('DeprivationProposed');
});

it('treats the configured reason codes as excluded or excused absence', function () {
    config(['regulations.reasons.absent_codes' => [7777], 'regulations.reasons.excluded' => 8888]);

    expect(RegulationService::isAbsentReason(7777))->toBeTrue()
        ->and(RegulationService::isAbsentReason(3001))->toBeFalse();
});
