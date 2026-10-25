<?php

use App\Models\Reg\OpenReg;
use App\Models\Reg\Payment;
use App\Models\Student;
use App\Reg\RegAccess;
use App\Services\Reg\RegistrationAccessService;
use App\Services\Reg\StudentFeeCalculator;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    migrateErpSchema();

    DB::table('reg_faculties')->insert(['code' => 10, 'name_ar' => 'كلية تجريبية']);
    DB::table('reg_open_reg')->insert(['faculty_code' => 10, 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'created_at' => now(), 'updated_at' => now()]);

    $this->student = Student::query()->forceCreate([
        'university_no' => 1, 'name_ar' => 'طالب', 'faculty_code' => 10, 'department_code' => 100,
        'fees' => 4000, 'reg_fees' => 1000, 'installment_code' => 20080004, 'discount_percent' => 0,
    ]);
});

function pay(float $amount, string $date = '2026-03-01'): Payment
{
    return Payment::query()->create([
        'university_no' => 1, 'amount' => $amount, 'currency' => 'SDG', 'bankdate' => $date, 'bank' => 'BOK', 'voucher_no' => uniqid('v'),
    ]);
}

it('recalculates the student fees when a bank payment is recorded', function () {
    pay(2500);

    expect($this->student->fresh())
        ->pay->toBe(2500.0)
        ->xmod->toBe(2500.0)
        ->pay1->toBe(0.0)
        ->pay2->toBe(500.0)
        ->xstatus->toBe(StudentFeeCalculator::STATUS_PARTIAL);
});

it('settles the student when the payments cover all the fees', function () {
    pay(3000);
    pay(2000, '2026-04-01');

    expect($this->student->fresh())
        ->xmod->toBe(0.0)
        ->xstatus->toBe(StudentFeeCalculator::STATUS_PAID);
});

it('ignores payments outside the open-regs period', function () {
    pay(2500, '2025-12-31');
    pay(100, '2027-01-01');

    expect($this->student->fresh())->pay->toBe(0.0)->xstatus->toBe(StudentFeeCalculator::STATUS_UNPAID);
});

it('recalculates when a payment is changed, deleted or restored', function () {
    $payment = pay(2500);

    $payment->update(['amount' => 1000]);
    expect($this->student->fresh()->pay)->toBe(1000.0);

    $payment->delete();
    expect($this->student->fresh())->pay->toBe(0.0)->xstatus->toBe(StudentFeeCalculator::STATUS_UNPAID);

    $payment->restore();
    expect($this->student->fresh()->pay)->toBe(1000.0);
});

it('recomputes the fee columns on every student update like the old trigger did', function () {
    DB::table('students')->where('university_no', 1)->update(['xmod' => 999, 'all_fees' => 1]);

    $student = Student::query()->find(1);
    $student->update(['name_ar' => 'اسم جديد']);

    expect($student->fresh())->all_fees->toBe(5000.0)->xmod->toBe(5000.0)->pay1->toBe(2000.0);
});

it('moves a payment between two students when its student number changes', function () {
    Student::query()->forceCreate(['university_no' => 2, 'name_ar' => 'آخر', 'faculty_code' => 10, 'fees' => 4000, 'reg_fees' => 1000, 'installment_code' => 4]);

    $payment = pay(2500);
    $payment->update(['university_no' => 2]);

    expect($this->student->fresh()->pay)->toBe(0.0)
        ->and(Student::query()->find(2)->pay)->toBe(2500.0);
});

it('recalculates the covered students when an open-regs period changes', function () {
    pay(2500, '2026-03-01');
    expect($this->student->fresh()->pay)->toBe(2500.0);

    OpenReg::query()->where('faculty_code', 10)->first()->update(['end_date' => '2026-02-01']);

    expect($this->student->fresh())->pay->toBe(0.0)->xstatus->toBe(StudentFeeCalculator::STATUS_UNPAID);
});

it('counts no payments for a faculty without an open-regs period', function () {
    DB::table('reg_open_reg')->delete();

    pay(2500);

    expect($this->student->fresh()->pay)->toBe(0.0);
});

it('only applies an open-regs row to the matching department', function () {
    DB::table('reg_open_reg')->delete();
    DB::table('reg_open_reg')->insert(['faculty_code' => 10, 'department_code' => 999, 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'created_at' => now(), 'updated_at' => now()]);

    pay(2500);

    expect($this->student->fresh()->pay)->toBe(0.0);
});

it('reports whether registration is open from the window and lets a per-student setting override it', function () {
    $service = app(RegistrationAccessService::class);
    $student = $this->student->fresh();

    expect($service->isOpen($student, now()->setDate(2026, 6, 1)))->toBeTrue()
        ->and($service->isOpen($student, now()->setDate(2027, 6, 1)))->toBeFalse();

    $service->grant($student, RegAccess::Closed);
    expect($service->isOpen($student->fresh(), now()->setDate(2026, 6, 1)))->toBeFalse();

    $service->grant($student, RegAccess::Open);
    expect($service->isOpen($student->fresh(), now()->setDate(2027, 6, 1)))->toBeTrue()
        ->and($student->fresh()->reg_access_at)->not->toBeNull();

    $service->grant($student, null);
    expect($student->fresh())->reg_access->toBeNull()->reg_access_at->toBeNull();
});

it('counts payments of a student opened individually from the grant date', function () {
    DB::table('reg_open_reg')->delete();
    app(RegistrationAccessService::class)->grant($this->student, RegAccess::Open);

    pay(2500, now()->toDateString());

    expect($this->student->fresh()->pay)->toBe(2500.0);
});
