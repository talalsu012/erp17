<?php

use App\Models\Res\AbsenceDecision;
use App\Models\User;
use App\Reg\AbsenceStatus;
use App\Services\Res\AbsenceService;
use Tests\TestCase;

uses(TestCase::class);

it('computes the absence percentage rounded to two decimals', function (int $absent, int $total, float $expected) {
    expect(AbsenceService::percentage($absent, $total))->toBe($expected);
})->with([
    [0, 0, 0.0],
    [0, 20, 0.0],
    [3, 20, 15.0],
    [5, 20, 25.0],
    [1, 3, 33.33],
]);

it('classifies the absence status at the regulation thresholds', function (float $pct, AbsenceStatus $expected) {
    expect(AbsenceService::classify($pct))->toBe($expected);
})->with([
    'below warning' => [14.99, AbsenceStatus::Normal],
    'warning at 15%' => [15.0, AbsenceStatus::Warning],
    'still warning at 25%' => [25.0, AbsenceStatus::Warning],
    'deprivation above 25%' => [25.01, AbsenceStatus::DeprivationProposed],
    'full absence' => [100.0, AbsenceStatus::DeprivationProposed],
]);

it('only approves deprivation for a proposed decision', function () {
    $decision = (new AbsenceDecision)->forceFill(['status' => AbsenceStatus::Warning]);

    expect(fn () => (new AbsenceService)->approveDeprivation($decision, new User))
        ->toThrow(DomainException::class, '25%');
});

it('requires a note to lift a deprivation', function () {
    $decision = (new AbsenceDecision)->forceFill(['status' => AbsenceStatus::Deprived]);

    expect(fn () => (new AbsenceService)->lift($decision, new User, '  '))
        ->toThrow(DomainException::class, 'سبب');
});

it('refuses to lift a decision that was never proposed or approved', function () {
    $decision = (new AbsenceDecision)->forceFill(['status' => AbsenceStatus::Normal]);

    expect(fn () => (new AbsenceService)->lift($decision, new User, 'عذر'))
        ->toThrow(DomainException::class);
});

it('keeps council decisions out of automatic recalculation', function () {
    expect(AbsenceStatus::Deprived->isDecided())->toBeTrue()
        ->and(AbsenceStatus::Lifted->isDecided())->toBeTrue()
        ->and(AbsenceStatus::DeprivationProposed->isDecided())->toBeFalse();
});
