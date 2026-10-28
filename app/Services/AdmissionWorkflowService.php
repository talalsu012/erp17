<?php

namespace App\Services;

use App\Models\Reg\AdmissionWorkflow;
use App\Models\Reg\Payment;
use App\Models\Student;
use App\Models\StudentAccount;
use App\Models\User;
use App\Reg\AdmissionStage;
use App\Reg\Currency;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * مخطط قبول الطالب بعد المعاينة: المسجل ← الطبيب ← لجنة الكلية ← المسجل (الرسوم) ← البنك ← النظام ← العميد.
 * كل انتقال يتحقق من دور المنفّذ ومن المرحلة الحالية، فلا يعدّل دور إلا حقوله.
 */
class AdmissionWorkflowService
{
    public const ROLE_REGISTRAR = 'Registrar';

    public const ROLE_DOCTOR = 'medical_doctor';

    public const ROLE_COMMITTEE = 'faculty_committee';

    public const ROLE_DEAN = 'dean';

    public const ROLE_SUPER_ADMIN = 'super_admin';

    /** حقول رسوم الطالب القابلة للتحديد من خطوة الرسوم */
    public const STUDENT_FEE_FIELDS = [
        'reg_fees', 'fees', 'fees1', 'fees2', 'fees3', 'fees5', 'fees6', 'fees7', 'fees8',
        'discount_percent', 'currency', 'installment_code', 'fees9',
    ];

    /** الأقساط المحسوبة: تُعرض في نموذج الرسوم ولا تُحفظ منه */
    public const STUDENT_INSTALLMENT_FIELDS = ['pay1', 'pay2', 'pay3', 'pay4'];

    /** @return list<string> الأدوار المشاركة في المخطط (لإظهار الصفحة) */
    public static function participatingRoles(): array
    {
        return [self::ROLE_REGISTRAR, self::ROLE_DOCTOR, self::ROLE_COMMITTEE, self::ROLE_DEAN, self::ROLE_SUPER_ADMIN];
    }

    /** المسجل يضيف الطالب إلى صفحة المعاينة */
    public function register(User $user, Student $student): AdmissionWorkflow
    {
        $this->assertRole($user, self::ROLE_REGISTRAR);

        if (AdmissionWorkflow::query()->where('university_no', $student->university_no)->exists()) {
            throw new DomainException('الطالب مضاف مسبقاً في صفحة المعاينة.');
        }

        return AdmissionWorkflow::query()->create([
            'university_no' => $student->university_no,
            'stage' => AdmissionStage::AwaitingDoctor,
            'registered_by' => $user->getKey(),
        ]);
    }

    /** الطبيب يدخل قرار اللياقة الطبية */
    public function recordMedical(User $user, AdmissionWorkflow $workflow, string $fitness, ?string $notes = null): AdmissionWorkflow
    {
        $this->assertRole($user, self::ROLE_DOCTOR);
        $this->assertStage($workflow, AdmissionStage::AwaitingDoctor);

        if (! in_array($fitness, ['fit', 'unfit'], true)) {
            throw new DomainException('قرار الطبيب يجب أن يكون لائق أو غير لائق.');
        }

        return $this->save($workflow, [
            'medical_fitness' => $fitness,
            'medical_notes' => $notes,
            'medical_by' => $user->getKey(),
            'medical_at' => now(),
            'stage' => AdmissionStage::AwaitingCommittee,
        ]);
    }

    /** لجنة الكلية توصي بالقبول أو الرفض (الرفض يغلق المعاملة ويلزمه سبب) */
    public function recordCommittee(User $user, AdmissionWorkflow $workflow, bool $accept, ?string $notes = null, ?string $rejectionReason = null): AdmissionWorkflow
    {
        $this->assertRole($user, self::ROLE_COMMITTEE);
        $this->assertStage($workflow, AdmissionStage::AwaitingCommittee);

        if (! $accept && blank($rejectionReason)) {
            throw new DomainException('يلزم تدوين سبب الرفض.');
        }

        return $this->save($workflow, [
            'committee_decision' => $accept ? 'accepted' : 'rejected',
            'committee_notes' => $notes,
            'rejection_reason' => $accept ? null : $rejectionReason,
            'committee_by' => $user->getKey(),
            'committee_at' => now(),
            'stage' => $accept ? AdmissionStage::AwaitingFees : AdmissionStage::Rejected,
        ]);
    }

    /** المسجل يحدد رسوم الطالب، فتظهر معلّقة للدفع (Pending) */
    public function assignFees(User $user, AdmissionWorkflow $workflow, float $amount, string $currency = 'SDG'): AdmissionWorkflow
    {
        $this->assertRole($user, self::ROLE_REGISTRAR);
        $this->assertStage($workflow, AdmissionStage::AwaitingFees);

        if ($amount <= 0) {
            throw new DomainException('قيمة الرسوم يجب أن تكون أكبر من صفر.');
        }

        return $this->save($workflow, [
            'assigned_fees' => $amount,
            'currency' => $currency,
            'fees_by' => $user->getKey(),
            'fees_at' => now(),
            'stage' => AdmissionStage::AwaitingPayment,
        ]);
    }

    /**
     * المسجل يحدد رسوم الطالب كاملة (كما في صفحة الطالب): تُحفظ على الطالب فيُعاد حسابه،
     * ثم يُعتمد إجمالي رسومه (all_fees) كرسوم المعاملة.
     *
     * @param  array<string, mixed>  $fees
     */
    public function assignStudentFees(User $user, AdmissionWorkflow $workflow, array $fees): AdmissionWorkflow
    {
        $this->assertRole($user, self::ROLE_REGISTRAR);
        $this->assertStage($workflow, AdmissionStage::AwaitingFees);

        return DB::transaction(function () use ($user, $workflow, $fees) {
            $fees = Arr::only($fees, self::STUDENT_FEE_FIELDS);
            $currency = ($fees['currency'] ?? Currency::SDG) instanceof Currency ? $fees['currency']->value : (string) ($fees['currency'] ?? 'SDG');
            $fees['currency'] = $currency;

            $student = Student::query()->findOrFail($workflow->university_no);
            $student->update($fees);

            return $this->assignFees($user, $workflow, (float) $student->fresh()->all_fees, $currency);
        });
    }

    /**
     * يُستدعى عند تسجيل دفعة: إن بلغ مجموع مدفوعات الطالب الرسوم المحددة تُسجَّل «تم الدفع»
     * ويُنشأ حساب الطالب تلقائياً وتنتقل المعاملة إلى العميد.
     */
    public function onPayment(Payment $payment): ?AdmissionWorkflow
    {
        $workflow = AdmissionWorkflow::query()
            ->where('university_no', $payment->university_no)
            ->where('stage', AdmissionStage::AwaitingPayment->value)
            ->first();

        if (! $workflow) {
            return null;
        }

        $paid = (float) Payment::query()->where('university_no', $payment->university_no)->sum('amount');

        if ($paid < (float) $workflow->assigned_fees) {
            return null;
        }

        return DB::transaction(function () use ($workflow) {
            $this->createStudentAccount($workflow->student);

            return $this->save($workflow, [
                'paid_at' => now(),
                'account_created_at' => now(),
                'stage' => AdmissionStage::AwaitingDean,
            ]);
        });
    }

    /** العميد يعتمد القبول النهائي */
    public function approve(User $user, AdmissionWorkflow $workflow, ?string $notes = null): AdmissionWorkflow
    {
        $this->assertRole($user, self::ROLE_DEAN);
        $this->assertStage($workflow, AdmissionStage::AwaitingDean);

        return $this->save($workflow, [
            'dean_notes' => $notes,
            'dean_by' => $user->getKey(),
            'dean_at' => now(),
            'stage' => AdmissionStage::Approved,
        ]);
    }

    /**
     * حساب الطالب في users_std (كلمة المرور عشوائية وتُعاد بالاستعادة).
     * البريد المسجّل للطالب، وإلا بريد افتراضي من الرقم الجامعي.
     */
    private function createStudentAccount(Student $student): StudentAccount
    {
        return StudentAccount::query()->firstOrCreate(
            ['university_no' => $student->university_no],
            [
                'name' => $student->name_ar,
                'name_en' => $student->name_en,
                'email' => $student->email ?: "{$student->university_no}@students.local",
                'password' => Hash::make(Str::random(16)),
            ],
        );
    }

    /** @param  array<string, mixed>  $attributes */
    private function save(AdmissionWorkflow $workflow, array $attributes): AdmissionWorkflow
    {
        $workflow->forceFill($attributes)->save();

        return $workflow;
    }

    private function assertStage(AdmissionWorkflow $workflow, AdmissionStage $expected): void
    {
        if ($workflow->stage !== $expected) {
            throw new DomainException("هذه الخطوة غير متاحة: المعاملة الآن «{$workflow->stage->getLabel()}».");
        }
    }

    private function assertRole(User $user, string $role): void
    {
        if (! $user->hasAnyRole([$role, self::ROLE_SUPER_ADMIN])) {
            throw new DomainException('ليست لديك صلاحية هذه الخطوة في مخطط القبول.');
        }
    }
}
