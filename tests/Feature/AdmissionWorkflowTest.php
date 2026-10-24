<?php

use App\Models\Reg\AdmissionWorkflow;
use App\Models\Reg\Payment;
use App\Models\Student;
use App\Models\User;
use App\Reg\AdmissionStage;
use App\Services\AdmissionWorkflowService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('students', function (Blueprint $table) {
        $table->unsignedBigInteger('university_no')->primary();
        $table->string('name_ar')->nullable();
        $table->string('name_en')->nullable();
        $table->string('email')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('admission_workflows', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('university_no')->unique();
        $table->string('stage')->default('awaiting_doctor');
        $table->unsignedBigInteger('registered_by')->nullable();
        $table->string('medical_fitness')->nullable();
        $table->text('medical_notes')->nullable();
        $table->unsignedBigInteger('medical_by')->nullable();
        $table->timestamp('medical_at')->nullable();
        $table->string('committee_decision')->nullable();
        $table->text('committee_notes')->nullable();
        $table->text('rejection_reason')->nullable();
        $table->unsignedBigInteger('committee_by')->nullable();
        $table->timestamp('committee_at')->nullable();
        $table->decimal('assigned_fees', 12, 2)->nullable();
        $table->string('currency')->nullable();
        $table->unsignedBigInteger('fees_by')->nullable();
        $table->timestamp('fees_at')->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->timestamp('account_created_at')->nullable();
        $table->text('dean_notes')->nullable();
        $table->unsignedBigInteger('dean_by')->nullable();
        $table->timestamp('dean_at')->nullable();
        $table->timestamps();
    });
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('university_no');
        $table->decimal('amount', 12, 2)->default(0);
        $table->string('currency')->nullable();
        $table->string('bank')->nullable();
        $table->string('branch')->nullable();
        $table->string('voucher_no')->nullable();
        $table->date('bankdate')->nullable();
        $table->unsignedBigInteger('batch_code')->nullable();
        $table->unsignedBigInteger('level_code')->nullable();
        $table->string('sys_type')->nullable();
        $table->string('transaction_id')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('users_std', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('university_no')->nullable();
        $table->string('name')->nullable();
        $table->string('name_en')->nullable();
        $table->string('email')->unique();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });
    Schema::create('activity_log', function (Blueprint $table) {
        $table->id();
        $table->string('log_name')->nullable();
        $table->text('description');
        $table->nullableMorphs('subject', 'subject');
        $table->string('event')->nullable();
        $table->nullableMorphs('causer', 'causer');
        $table->json('properties')->nullable();
        $table->uuid('batch_uuid')->nullable();
        $table->timestamps();
    });

    DB::table('students')->insert(['university_no' => 100, 'name_ar' => 'طالب', 'name_en' => 'Student', 'email' => null]);
    Payment::observe(App\Observers\PaymentAdmissionObserver::class);
});

function actor(array $roles, int $id = 1): User
{
    $user = Mockery::mock(User::class)->makePartial();
    $user->id = $id;
    $user->shouldReceive('hasAnyRole')->andReturnUsing(fn (array $wanted): bool => array_intersect($wanted, $roles) !== []);

    return $user;
}

function pay(float $amount): Payment
{
    return Payment::query()->create(['university_no' => 100, 'amount' => $amount, 'currency' => 'SDG', 'bank' => 'B', 'voucher_no' => uniqid()]);
}

it('runs the whole admission flow and creates the account only after full payment', function () {
    $service = new AdmissionWorkflowService;
    $workflow = $service->register(actor(['Registrar']), Student::find(100));
    expect($workflow->stage)->toBe(AdmissionStage::AwaitingDoctor);

    $service->recordMedical(actor(['medical_doctor']), $workflow, 'fit', 'سليم');
    expect($workflow->fresh()->stage)->toBe(AdmissionStage::AwaitingCommittee);

    $service->recordCommittee(actor(['faculty_committee']), $workflow->fresh(), true);
    expect($workflow->fresh()->stage)->toBe(AdmissionStage::AwaitingFees);

    $service->assignFees(actor(['Registrar']), $workflow->fresh(), 1000);
    expect($workflow->fresh()->stage)->toBe(AdmissionStage::AwaitingPayment);

    pay(400);
    expect($workflow->fresh()->stage)->toBe(AdmissionStage::AwaitingPayment)
        ->and(DB::table('users_std')->count())->toBe(0);

    pay(600);
    $fresh = $workflow->fresh();
    expect($fresh->stage)->toBe(AdmissionStage::AwaitingDean)
        ->and($fresh->account_created_at)->not->toBeNull()
        ->and(DB::table('users_std')->where('university_no', 100)->value('email'))->toBe('100@students.local');

    $service->approve(actor(['dean']), $fresh);
    expect($workflow->fresh()->stage)->toBe(AdmissionStage::Approved);
});

it('closes the transaction when the committee rejects, and requires a reason', function () {
    $service = new AdmissionWorkflowService;
    $workflow = $service->register(actor(['Registrar']), Student::find(100));
    $service->recordMedical(actor(['medical_doctor']), $workflow, 'unfit');

    expect(fn () => $service->recordCommittee(actor(['faculty_committee']), $workflow->fresh(), false))
        ->toThrow(DomainException::class, 'سبب الرفض');

    $service->recordCommittee(actor(['faculty_committee']), $workflow->fresh(), false, null, 'غير لائق طبياً');

    expect($workflow->fresh()->stage)->toBe(AdmissionStage::Rejected)
        ->and(fn () => $service->assignFees(actor(['Registrar']), $workflow->fresh(), 500))->toThrow(DomainException::class);
});

it('lets each role act only on its own step', function (array $roles, string $step) {
    $service = new AdmissionWorkflowService;
    $workflow = AdmissionWorkflow::query()->create(['university_no' => 100, 'stage' => match ($step) {
        'medical' => AdmissionStage::AwaitingDoctor,
        'committee' => AdmissionStage::AwaitingCommittee,
        'fees' => AdmissionStage::AwaitingFees,
        'approve' => AdmissionStage::AwaitingDean,
    }]);

    $attempt = match ($step) {
        'medical' => fn () => $service->recordMedical(actor($roles), $workflow, 'fit'),
        'committee' => fn () => $service->recordCommittee(actor($roles), $workflow, true),
        'fees' => fn () => $service->assignFees(actor($roles), $workflow, 100),
        'approve' => fn () => $service->approve(actor($roles), $workflow),
    };

    expect($attempt)->toThrow(DomainException::class, 'صلاحية');
})->with([
    'registrar cannot decide medically' => [['Registrar'], 'medical'],
    'doctor cannot recommend' => [['medical_doctor'], 'committee'],
    'committee cannot set fees' => [['faculty_committee'], 'fees'],
    'registrar cannot approve' => [['Registrar'], 'approve'],
]);

it('rejects a step in the wrong stage even for the right role', function () {
    $service = new AdmissionWorkflowService;
    $workflow = $service->register(actor(['Registrar']), Student::find(100));

    expect(fn () => $service->approve(actor(['dean']), $workflow))->toThrow(DomainException::class, 'غير متاحة');
});

it('does not register the same student twice', function () {
    $service = new AdmissionWorkflowService;
    $service->register(actor(['Registrar']), Student::find(100));

    expect(fn () => $service->register(actor(['Registrar']), Student::find(100)))->toThrow(DomainException::class, 'مسبقاً');
});

it('lets the super admin act on any step', function () {
    $service = new AdmissionWorkflowService;
    $workflow = $service->register(actor(['super_admin']), Student::find(100));

    $service->recordMedical(actor(['super_admin']), $workflow, 'fit');

    expect($workflow->fresh()->stage)->toBe(AdmissionStage::AwaitingCommittee);
});
