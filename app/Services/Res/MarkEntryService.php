<?php

namespace App\Services\Res;

use App\Models\Res\Grade;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\Reson;
use App\Models\Res\Subject;
use App\Models\Res\SubjectApprovalStatus;
use App\Models\Student;
use App\Models\User;
use App\Reg\MarkApprovalStage;
use App\Reg\ShowAbsentCode;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * إدخال الدرجات والأسباب ومراحل الاعتماد.
 *
 * كل الأخطاء المتوقعة (صلاحية/قيمة غير صالحة) تُرمى كـ DomainException برسالة عربية جاهزة للعرض.
 */
class MarkEntryService
{
    /** @deprecated القيمة الفعلية: regulations.supplementary.max_mark (RegulationProfile::supplementaryMaxMark()) */
    public const SUPPLEMENTARY_MAX = 50;

    public function __construct(private readonly RegulationService $regulation) {}

    public function currentStage(int|string|null $subject, int|string|null $batch): MarkApprovalStage
    {
        if (blank($subject) || blank($batch)) {
            return MarkApprovalStage::Professor;
        }

        return SubjectApprovalStatus::where('subject_code', $subject)
            ->where('batch_code', $batch)
            ->first()?->current_stage ?? MarkApprovalStage::Professor;
    }

    public function isSuperAdmin(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function canViewFaculty(User $user, mixed $faculty): bool
    {
        return $user->canAccessAcademic($faculty);
    }

    public function canAccess(User $user, mixed $faculty, mixed $department = null, mixed $level = null): bool
    {
        return $user->canAccessAcademic($faculty, $department, $level);
    }

    public function assertCanAccessStudent(User $user, Student $student): void
    {
        if (! $user->canAccessAcademic($student->faculty_code, $student->department_code, $student->level_code)) {
            throw new DomainException('الطالب خارج نطاق صلاحياتك (الكلية أو القسم أو الفصل).');
        }
    }

    public function canEdit(User $user, Subject $subject, int|string $batch): bool
    {
        try {
            $this->assertCanEdit($user, $subject, $batch);

            return true;
        } catch (DomainException) {
            return false;
        }
    }

    /**
     * الصلاحية تُفحص في الخادم عند كل كتابة (لا نعتمد على إخفاء الأزرار في الواجهة).
     */
    public function assertCanEdit(User $user, Subject $subject, int|string $batch): void
    {
        if ($this->isSuperAdmin($user)) {
            return;
        }

        if (! $user->canAccessAcademic($subject->faculty_code, $subject->department_code, $subject->level_code)) {
            throw new DomainException('لا تملك صلاحية على كلية هذه المادة أو قسمها أو مستواها.');
        }

        $stage = $this->currentStage($subject->code, $batch);

        if (! $user->hasRole($stage->value)) {
            throw new DomainException("المادة حالياً في مرحلة ({$stage->getLabel()}) ولا تملك صلاحية التعديل فيها.");
        }
    }

    /**
     * أهلية الطالب للمادة (تُفحص لكل المستخدمين بمن فيهم المدير الأعلى):
     *  1) بيانات الطالب الأساسية مكتملة، وتطابق كلية/مستوى/دفعة/قسم المادة.
     *  2) عند اختيار تخصص: الطالب والمادة كلاهما في هذا التخصص.
     *  3) بدون اختيار تخصص: إن كانت المادة لتخصص معيّن فيجب أن يكون الطالب فيه.
     *  4) regulations.entry.strict_special: المادة العامة (بلا تخصص) لا تُدخل لطالب مسجّل في تخصص.
     */
    public function assertEligible(Student $student, Subject $subject, int|string $batch, int|string|null $special = null): void
    {
        $missing = collect([
            'faculty_code' => 'الكلية',
            'department_code' => 'القسم',
            'level_code' => 'الفصل',
            'batch_code' => 'العام الدراسي',
            'stady_code' => 'الخطة الدراسية',
        ])->filter(fn ($label, $column) => blank($student->{$column}))->values();

        if ($missing->isNotEmpty()) {
            throw new DomainException('بيانات الطالب ناقصة: '.$missing->implode('، ').'.');
        }

        if ((string) $student->faculty_code !== (string) $subject->faculty_code
            || (string) $student->level_code !== (string) $subject->level_code
            || (string) $student->batch_code !== (string) $batch) {
            throw new DomainException('الطالب لا ينتمي إلى كلية/فصل/عام دراسي هذه المادة.');
        }

        if (filled($subject->department_code) && (string) $student->department_code !== (string) $subject->department_code) {
            throw new DomainException('قسم الطالب لا يطابق قسم المادة.');
        }

        if (filled($special)) {
            if ((string) $student->special_code !== (string) $special) {
                throw new DomainException('الطالب ليس ضمن التخصص المختار.');
            }

            if ((string) $subject->special_code !== (string) $special) {
                throw new DomainException('المادة لا تتبع التخصص المختار.');
            }
        } elseif (filled($subject->special_code) && (string) $student->special_code !== (string) $subject->special_code) {
            throw new DomainException('المادة خاصة بتخصص لا ينتمي إليه الطالب.');
        } elseif (blank($subject->special_code) && filled($student->special_code) && config('regulations.entry.strict_special')) {
            throw new DomainException('هذه مادة عامة (بلا تخصص) والطالب مسجّل في تخصص، فلا تُدخل له درجتها.');
        }
    }

    public function advanceStage(User $user, Subject $subject, int|string $batch): MarkApprovalStage
    {
        $this->assertCanEdit($user, $subject, $batch);

        $next = match ($this->currentStage($subject->code, $batch)) {
            MarkApprovalStage::Professor => MarkApprovalStage::HeadDepartment,
            MarkApprovalStage::HeadDepartment => MarkApprovalStage::Dean,
            MarkApprovalStage::Dean => MarkApprovalStage::ScientificAffairs,
            MarkApprovalStage::ScientificAffairs => MarkApprovalStage::Approved,
            default => throw new DomainException('لا يمكن ترحيل المادة من مرحلتها الحالية.'),
        };

        SubjectApprovalStatus::withTrashed()->updateOrCreate(
            ['subject_code' => $subject->code, 'batch_code' => $batch],
            ['current_stage' => $next, 'deleted_at' => null],
        );

        return $next;
    }

    /**
     * يرجع استعلاماً فارغاً إن نقصت الفلاتر أو كانت الكلية خارج صلاحيات المستخدم،
     * لأن الفلاتر قادمة من المتصفح ولا يُوثق بها.
     *
     * @param  array{batch?: mixed, faculty?: mixed, department?: mixed, special?: mixed, level?: mixed, semester?: mixed, absent?: mixed, subject?: mixed}  $f
     */
    public function studentsQuery(User $user, array $f): Builder
    {
        $incomplete = collect(['batch', 'subject', 'level', 'faculty', 'absent', 'semester'])
            ->contains(fn ($key) => blank($f[$key] ?? null));

        if ($incomplete || ! $user->canAccessAcademic($f['faculty'], $f['department'] ?? null, $f['level'])) {
            return Student::query()->whereRaw('0 = 1');
        }

        $subject = Subject::query()->find($f['subject']);

        if (! $subject || (string) $subject->faculty_code !== (string) $f['faculty'] || (string) $subject->level_code !== (string) $f['level']) {
            return Student::query()->whereRaw('0 = 1');
        }

        return $subject->constrainStudents($user->constrainToAcademicScope(Student::query()))
            ->with(['marks' => fn ($q) => $q->where([
                ['subject_code', $f['subject']],
                ['batch_code', $f['batch']],
                ['level_code', $f['level']],
                ['examType', $f['absent']],
            ])])
            ->where('faculty_code', $f['faculty'])
            ->where('level_code', $f['level'])
            ->where('batch_code', $f['batch'])
            ->when($f['department'] ?? null, fn ($q, $v) => $q->where('department_code', $v))
            ->when($f['special'] ?? null, fn ($q, $v) => $q->where('special_code', $v))
            ->when((int) $f['absent'] === 2, fn (Builder $q) => $q
                ->whereHas('recom', fn ($r) => $r->whereIn('recom_code', ShowAbsentCode::getRemedialValues()))
                ->whereHas('marks', fn ($m) => $m
                    ->where('subject_code', $f['subject'])
                    ->where('batch_code', $f['batch'])
                    ->where('level_code', $f['level'])
                    ->where('examType', 1)
                    ->whereIn('grade', ShowAbsentCode::getMarkValues())));
    }

    /**
     * يحفظ درجة الامتحان أو أعمال السنة (null/فارغ = مسح الحقل)،
     * ثم يحسب التقدير والنقاط ويحدّث توصية الطالب.
     *
     * @param  'mark'|'cwork'  $field
     * @param  int|string|null  $special  التخصص المختار في شاشة الإدخال (إن وُجد)
     * @return array{mark: Mark, recom: Recom}
     */
    public function saveScore(
        User $user,
        Student $student,
        Subject $subject,
        int|string $batch,
        int|string $examType,
        string $field,
        mixed $value,
        int|string|null $special = null,
    ): array {
        $this->assertCanEdit($user, $subject, $batch);
        $this->assertCanAccessStudent($user, $student);
        $this->assertEligible($student, $subject, $batch, $special);

        $examType = (int) $examType;

        if ($field === 'mark' && filled($value)) {
            $this->assertNotDeprived($student, $subject, $batch);
        }

        $value = $this->normalizeScore($value, $field, $subject, $examType);

        return DB::transaction(function () use ($user, $student, $subject, $batch, $examType, $field, $value) {
            $mark = $this->lockRow($user, $student, $subject, $batch, $examType);
            $before = $this->snapshotIfFinalized($mark);

            $previousReason = (int) $mark->reson_code;

            $mark->{$field} = $value;
            $mark->reson_code = null;

            [$mark->grade, $mark->point] = $this->gradeFor($mark, $student, $examType, $previousReason);
            $mark->save();

            $recom = $this->refreshStatistics($student, $subject, $batch, $examType);
            $this->recordAmendmentIfFinalized($user, $mark, $before);

            return ['mark' => $mark, 'recom' => $recom];
        });
    }

    /**
     * يحفظ سبب عدم وجود درجة (null = إلغاء السبب).
     *
     * @return array{mark: Mark, recom: Recom}
     */
    public function saveReason(
        User $user,
        Student $student,
        Subject $subject,
        int|string $batch,
        int|string $examType,
        int|string|null $reasonCode,
        int|string|null $special = null,
    ): array {
        $this->assertCanEdit($user, $subject, $batch);
        $this->assertCanAccessStudent($user, $student);
        $this->assertEligible($student, $subject, $batch, $special);

        $reason = filled($reasonCode) ? Reson::find((int) $reasonCode) : null;

        if (filled($reasonCode) && ! $reason) {
            throw new DomainException('السبب المختار غير موجود.');
        }

        return DB::transaction(function () use ($user, $student, $subject, $batch, $examType, $reason) {
            $mark = $this->lockRow($user, $student, $subject, $batch, (int) $examType);
            $before = $this->snapshotIfFinalized($mark);

            $previous = filled($mark->reson_code) ? Reson::find($mark->reson_code) : null;

            if ($previous?->appliesScore()) {
                $mark->mark = $mark->cwork = $mark->grade = $mark->point = null;
            }

            $mark->reson_code = $reason?->code;

            if ($reason?->appliesScore()) {
                $this->applyReasonScore($mark, $reason, $student, (int) $examType);
            } elseif (! $reason) {
                [$mark->grade, $mark->point] = $this->gradeFor($mark, $student, (int) $examType, 0);
            }

            $mark->save();

            $recom = $this->refreshStatistics($student, $subject, $batch, (int) $examType);
            $this->recordAmendmentIfFinalized($user, $mark, $before);

            return ['mark' => $mark, 'recom' => $recom];
        });
    }

    /** الطالب المحروم بالغياب بقرار مجلس الكلية لا تُدخل له درجة الامتحان النهائي. */
    private function assertNotDeprived(Student $student, Subject $subject, int|string $batch): void
    {
        if (app(AbsenceService::class)->isBarredFromFinalExam($student->university_no, $subject->code, $batch, $subject->semester_id)) {
            throw new DomainException('الطالب محروم من الجلوس لامتحان هذا المقرر بسبب الغياب (قرار مجلس الكلية)، ولا تُدخل له درجة امتحان.');
        }
    }

    /** القيمة الصحيحة: رقم ≥ 0 ضمن حدود المادة. العمود integer فنقرّب صراحةً بدل التقريب الصامت من قاعدة البيانات. */
    private function normalizeScore(mixed $value, string $field, Subject $subject, int $examType): ?int
    {
        if (blank($value)) {
            return null;
        }

        if (! is_numeric($value)) {
            throw new DomainException('الدرجة المدخلة غير صحيحة، يجب أن تكون رقماً.');
        }

        $score = (int) round((float) $value);

        if ($score < 0) {
            throw new DomainException('لا يمكن إدخال درجة أقل من الصفر.');
        }

        $examMax = (int) ($subject->exam ?: 100);

        if ($field === 'cwork') {
            if ($examMax >= 100) {
                throw new DomainException('هذه المادة ليس لها أعمال سنة (نسبة الامتحان 100%).');
            }
            if ($score > 100 - $examMax) {
                throw new DomainException('درجة أعمال السنة تتجاوز الحد الأقصى ('.(100 - $examMax).').');
            }

            return $score;
        }

        if ($score > $examMax) {
            throw new DomainException("درجة الامتحان تتجاوز الحد الأقصى ({$examMax}).");
        }

        return $examType === 2 ? min($score, RegulationProfile::current()->supplementaryMaxMark()) : $score;
    }

    /**
     * ينسخ درجة السبب وتقديره ونقاطه إلى صف الدرجة (وتُلغى أعمال السنة).
     * إن حدّد السبب درجة دون تقدير أو نقاط، يُكمَل الناقص من جدول التقديرات.
     */
    private function applyReasonScore(Mark $mark, Reson $reason, Student $student, int $examType): void
    {
        $mark->mark = $reason->mark;
        $mark->cwork = null;
        $mark->grade = filled($reason->grade) ? $reason->grade : null;
        $mark->point = $reason->point;

        if ($mark->mark !== null && ($mark->grade === null || $mark->point === null)) {
            [$grade, $point] = $this->gradeFor($mark, $student, $examType, 0);

            $mark->grade ??= $grade;
            $mark->point ??= $point;
        }
    }

    /**
     * قيم صف مقفل (معتمد) قبل تعديل المدير الأعلى له، أو null إن لم يكن مقفلاً.
     *
     * @return array<string, mixed>|null
     */
    private function snapshotIfFinalized(Mark $mark): ?array
    {
        return $mark->exists && $mark->is_locked
            ? $mark->only(['mark', 'cwork', 'grade', 'point', 'reson_code'])
            : null;
    }

    /**
     * @param  array<string, mixed>|null  $before
     */
    private function recordAmendmentIfFinalized(User $user, Mark $mark, ?array $before): void
    {
        if ($before !== null) {
            app(FinalizationService::class)->recordAmendment($user, $mark, $before, request()->ip());
        }
    }

    /**
     * يجلب سجل الدرجة بقفل (أو ينشئه) بالمفتاح الفريد نفسه للجدول، ويستعيده إن كان محذوفاً.
     * كلية الصف وقسمه وتخصصه من الطالب دائماً (الطالب هو الأصل ثم المقرر) فتتطابق مع توصيته.
     */
    private function lockRow(User $user, Student $student, Subject $subject, int|string $batch, int $examType): Mark
    {
        $key = [
            'university_no' => $student->university_no,
            'subject_code' => $subject->code,
            'level_code' => $subject->level_code,
            'batch_code' => $batch,
            'semester' => $subject->semester_id,
            'examType' => $examType,
        ];

        $mark = Mark::withTrashed()->where($key)->lockForUpdate()->first() ?? new Mark($key);

        if ($mark->exists && $mark->is_locked && ! $user->isSuperAdmin()) {
            throw new DomainException('هذه النتيجة معتمدة ومقفلة نهائياً، ولا يعدّلها إلا المدير الأعلى.');
        }

        $mark->faculty_code = $student->faculty_code;
        $mark->department_code = $student->department_code;
        $mark->special_code = $student->special_code;

        if ($mark->trashed()) {
            $mark->restore();
        }

        return $mark;
    }

    /** @return array{0: ?string, 1: ?float} [التقدير، النقاط] */
    public function gradeFor(Mark $mark, Student $student, int $examType, int $previousReason): array
    {
        if ($mark->mark === null && $mark->cwork === null) {
            return [null, null];
        }

        $total = (int) $mark->mark + (int) $mark->cwork;
        $profile = RegulationProfile::current();
        $plan = (string) ($student->stady_code ?: $profile->defaultPlan());
        $grade = Grade::forMark($total, $plan);

        if (! $grade) {
            throw new DomainException("لا يوجد تقدير مُعرَّف للمجموع ({$total}) في لائحة الطالب.");
        }

        if ($examType === 2 && ! $profile->isAbsentReason($previousReason)) {
            $lowest = Grade::lowestPassing($plan);

            if ((int) $mark->mark >= ($lowest?->lower ?? $profile->passMark())) {
                return ['*'.($lowest?->title ?? 'D'), $lowest !== null ? (float) $lowest->point : $profile->supplementaryPassPoint()];
            }
        }

        return [$grade->title, (float) $grade->point];
    }

    private function refreshStatistics(Student $student, Subject $subject, int|string $batch, int $examType): Recom
    {
        return $this->regulation->refresh(
            $student,
            $subject->faculty_code,
            $subject->level_code,
            $batch,
            $subject->semester_id,
            $examType,
        );
    }
}
