<?php

use App\Models\Reg\AdmissionWorkflow;
use App\Models\Student;
use App\Models\User;
use App\Reg\AdmissionStage;
use App\Reg\Currency;
use App\Services\AdmissionWorkflowService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    migrateErpSchema();

    $this->student = Student::query()->forceCreate([
        'university_no' => 1, 'name_ar' => 'طالب', 'faculty_code' => 10, 'department_code' => 100,
        'fees' => 100, 'reg_fees' => 10, 'installment_code' => 20080004, 'discount_percent' => 0,
    ]);
    $this->workflow = AdmissionWorkflow::query()->create(['university_no' => 1, 'stage' => AdmissionStage::AwaitingFees]);
});

function registrar(array $roles = ['Registrar']): User
{
    $user = Mockery::mock(User::class)->makePartial();
    $user->id = 1;
    $user->shouldReceive('hasAnyRole')->andReturnUsing(fn (array $wanted): bool => array_intersect($wanted, $roles) !== []);

    return $user;
}

it('saves the full student fees and assigns the student total as the workflow fees', function () {
    app(AdmissionWorkflowService::class)->assignStudentFees(registrar(), $this->workflow, [
        'reg_fees' => 50, 'fees' => 1000, 'fees1' => 0, 'fees2' => 0, 'fees3' => 0, 'fees5' => 0,
        'fees6' => 25, 'fees7' => 0, 'fees8' => 0, 'discount_percent' => 0,
        'currency' => 'SDG', 'installment_code' => 20080004, 'not_a_fee_field' => 'ignored',
    ]);

    expect($this->student->fresh())->fees->toEqual(1000)->reg_fees->toEqual(50)->fees6->toEqual(25)
        ->and($this->workflow->fresh())
        ->stage->toBe(AdmissionStage::AwaitingPayment)
        ->assigned_fees->toEqual(1075);
});

it('does not touch the student when the caller is not a registrar', function () {
    expect(fn () => app(AdmissionWorkflowService::class)->assignStudentFees(registrar(['dean']), $this->workflow, ['fees' => 9999]))
        ->toThrow(DomainException::class);

    expect($this->student->fresh()->fees)->toEqual(100)
        ->and(DB::table('admission_workflows')->value('stage'))->toBe(AdmissionStage::AwaitingFees->value);
});

it('accepts the currency as the enum the form submits', function () {
    app(AdmissionWorkflowService::class)->assignStudentFees(registrar(), $this->workflow, [
        'fees' => 500, 'currency' => Currency::USD,
    ]);

    expect($this->workflow->fresh()->currency)->toBe('USD');
});
