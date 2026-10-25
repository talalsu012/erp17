<?php

use App\Services\Reg\StudentFeeCalculator;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function feeInput(array $overrides = []): array
{
    return $overrides + [
        'fees' => 4000, 'reg_fees' => 1000, 'fees1' => 0, 'fees2' => 0, 'fees3' => 0, 'fees6' => 0, 'fees7' => 0, 'fees9' => 0,
        'installment_code' => 20080004, 'discount_percent' => 0, 'pay1' => 0, 'pay2' => 0, 'pay3' => 0, 'pay4' => 0,
    ];
}

it('splits the fees into four installments with the registration fees in the first one', function () {
    $result = (new StudentFeeCalculator)->calculate(feeInput(), 0);

    expect($result)->toMatchArray([
        'all_fees' => 5000.0, 'xmod' => 5000.0, 'pay' => 0.0, 'fees10' => 0.0,
        'pay1' => 2000.0, 'pay2' => 1000.0, 'pay3' => 1000.0, 'pay4' => 1000.0,
        'xstatus' => StudentFeeCalculator::STATUS_UNPAID,
    ]);
});

it('deducts the payments from the installments one after another', function () {
    $partial = (new StudentFeeCalculator)->calculate(feeInput(), 2500);

    expect($partial)->toMatchArray([
        'pay' => 2500.0, 'xmod' => 2500.0, 'pay1' => 0.0, 'pay2' => 500.0, 'pay3' => 1000.0, 'pay4' => 1000.0,
        'xstatus' => StudentFeeCalculator::STATUS_PARTIAL,
    ]);

    $settled = (new StudentFeeCalculator)->calculate(feeInput(), 6000);

    expect($settled)->toMatchArray([
        'xmod' => 0.0, 'pay1' => 0.0, 'pay2' => 0.0, 'pay3' => 0.0, 'pay4' => 0.0,
        'xstatus' => StudentFeeCalculator::STATUS_PAID,
    ]);
});

it('applies the discount taken from the last three digits of the discount code', function () {
    $result = (new StudentFeeCalculator)->calculate(feeInput(['installment_code' => 20080002, 'discount_percent' => 30010025]), 0);

    expect($result)->toMatchArray([
        'all_fees' => 4000.0, 'fees10' => 1000.0, 'pay1' => 2500.0, 'pay2' => 1500.0, 'pay3' => 0.0, 'pay4' => 0.0,
    ]);
});

it('keeps the cents of uneven installments in the last one', function () {
    $result = (new StudentFeeCalculator)->calculate(feeInput(['fees' => 1000, 'reg_fees' => 0, 'installment_code' => 3]), 0);

    expect($result)->toMatchArray(['pay1' => 333.33, 'pay2' => 333.33, 'pay3' => 333.34, 'pay4' => 0.0]);
});

it('charges everything at once without an installment plan', function () {
    $none = (new StudentFeeCalculator)->calculate(feeInput(['installment_code' => null]), 1200);

    expect($none)->toMatchArray(['all_fees' => 5000.0, 'pay1' => 3800.0, 'pay2' => 0.0, 'xstatus' => StudentFeeCalculator::STATUS_PARTIAL]);
});

it('uses the manual installments and the manual first installment amount for plan 7', function () {
    $result = (new StudentFeeCalculator)->calculate(feeInput([
        'installment_code' => 7, 'fees9' => 55, 'pay1' => 100, 'pay2' => 200, 'pay3' => 300, 'pay4' => 400,
    ]), 150);

    expect($result)->toMatchArray(['pay1' => 55.0, 'pay2' => 150.0, 'pay3' => 300.0, 'pay4' => 400.0]);
});

it('marks a student with no fees as unpaid', function () {
    $result = (new StudentFeeCalculator)->calculate(feeInput(['fees' => 0, 'reg_fees' => 0]), 0);

    expect($result)->toMatchArray(['all_fees' => 0.0, 'xmod' => 0.0, 'xstatus' => StudentFeeCalculator::STATUS_UNPAID]);
});
