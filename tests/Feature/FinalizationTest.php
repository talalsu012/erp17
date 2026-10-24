<?php

use App\Helpers\TranscriptGenerator;
use App\Models\Res\Finalization;
use App\Models\Res\Mark;
use App\Models\Res\Recom;
use App\Models\Res\RecordMark;
use App\Models\Res\Subject;
use App\Models\Student;
use App\Models\User;
use App\Services\Res\FinalizationService;
use App\Services\Res\MarkEntryService;
use App\Services\Res\RegulationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

const COHORT = ['faculty' => 10, 'department' => 100, 'level' => 1, 'batch' => 2026];
const APPROVER_CODE = 'S3cret-77';

beforeEach(function () {
    foreach ([
        '0001_01_01_000000_create_users_table.php',
        '2026_02_08_143514_create_permission_tables.php',
        '2026_10_04_111526_create_reg_department_user_table.php',
        '2026_10_04_111527_create_reg_level_user_table.php',
    ] as $migration) {
        Artisan::call('migrate', ['--path' => "database/migrations/{$migration}", '--no-interaction' => true]);
    }

    Schema::table('users', fn (Blueprint $table) => $table->string('approval_code_hash', 64)->nullable()->unique());

    Schema::create('reg_faculties', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->string('name_ar')->nullable();
        $table->unsignedBigInteger('results_approver_id')->nullable();
        $table->softDeletes();
    });
    Schema::create('reg_departments', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->unsignedBigInteger('faculty_code')->nullable();
        $table->softDeletes();
    });
    Schema::create('reg_faculty_user', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('faculty_code');
        $table->timestamps();
    });
    Schema::create('reg_levels', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->string('name_ar')->nullable();
        $table->softDeletes();
    });
    Schema::create('reg_ltcolumns', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->unsignedBigInteger('grouped')->nullable();
        $table->string('name_ar')->nullable();
        $table->softDeletes();
    });
    Schema::create('students', function (Blueprint $table) {
        $table->unsignedBigInteger('university_no')->primary();
        $table->unsignedBigInteger('faculty_code');
        $table->unsignedBigInteger('department_code')->nullable();
        $table->unsignedBigInteger('special_code')->nullable();
        $table->unsignedBigInteger('level_code');
        $table->unsignedBigInteger('batch_code');
        $table->unsignedBigInteger('stady_code')->nullable();
        $table->softDeletes();
    });
    Schema::create('subjects', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->string('name_ar')->nullable();
        $table->string('name_en')->nullable();
        $table->unsignedBigInteger('faculty_code');
        $table->unsignedBigInteger('department_code')->nullable();
        $table->unsignedBigInteger('special_code')->nullable();
        $table->unsignedBigInteger('level_code');
        $table->unsignedTinyInteger('semester_id')->default(1);
        $table->unsignedTinyInteger('exam')->default(100);
        $table->decimal('crch', 4, 1)->default(3);
        $table->softDeletes();
    });
    Schema::create('subject_approval_statuses', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('subject_code');
        $table->unsignedBigInteger('batch_code');
        $table->string('current_stage')->default('professor');
        $table->timestamps();
        $table->softDeletes();
    });
    Schema::create('res_resons', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->string('name_ar');
        $table->string('description')->nullable();
        $table->unsignedSmallInteger('mark')->nullable();
        $table->string('grade', 20)->nullable();
        $table->decimal('point', 4, 2)->nullable();
        $table->unsignedBigInteger('recom_code')->nullable();
        $table->boolean('is_visible')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });
    Schema::create('res_ltrecoms', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->string('name_ar');
        $table->timestamps();
        $table->softDeletes();
    });
    Schema::create('res_grades', function (Blueprint $table) {
        $table->id();
        $table->string('title', 10);
        $table->unsignedTinyInteger('upper');
        $table->unsignedTinyInteger('lower');
        $table->float('point')->default(0);
        $table->unsignedBigInteger('plan')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
    Schema::create('res_marks', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('university_no');
        $table->unsignedBigInteger('subject_code');
        $table->unsignedBigInteger('faculty_code');
        $table->unsignedBigInteger('department_code')->nullable();
        $table->unsignedBigInteger('special_code')->nullable();
        $table->unsignedBigInteger('level_code');
        $table->unsignedBigInteger('batch_code');
        $table->unsignedTinyInteger('semester')->default(1);
        $table->unsignedTinyInteger('examType')->default(1);
        $table->integer('mark')->nullable();
        $table->integer('cwork')->nullable();
        $table->string('grade', 20)->nullable();
        $table->float('point')->nullable();
        $table->unsignedBigInteger('reson_code')->nullable();
        $table->boolean('is_visible')->default(true);
        $table->boolean('is_locked')->default(false);
        $table->timestamps();
        $table->softDeletes();
    });
    Schema::create('res_absence_decisions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('university_no');
        $table->unsignedBigInteger('subject_code');
        $table->unsignedBigInteger('batch_code');
        $table->unsignedTinyInteger('semester');
        $table->string('status', 30)->default('normal');
        $table->timestamps();
    });

    Schema::create('res_recoms', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('university_no');
        $table->unsignedBigInteger('faculty_code');
        $table->unsignedBigInteger('department_code')->nullable();
        $table->unsignedBigInteger('special_code')->nullable();
        $table->unsignedBigInteger('level_code');
        $table->unsignedBigInteger('batch_code');
        $table->unsignedTinyInteger('semester')->default(1);
        $table->unsignedTinyInteger('examType')->default(1);
        $table->integer('countSubject')->nullable();
        $table->float('sumMarks')->nullable();
        $table->integer('s_pass')->nullable();
        $table->integer('s_fail')->nullable();
        $table->float('crch_fail')->nullable();
        $table->integer('s_abs')->nullable();
        $table->float('crch')->nullable();
        $table->float('points')->nullable();
        $table->float('gpa')->nullable();
        $table->float('cgpa')->nullable();
        $table->unsignedBigInteger('recom_code')->nullable();
        $table->boolean('is_locked')->default(false);
        $table->timestamps();
        $table->softDeletes();
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

    Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_05_102257_create_result_finalization_tables.php', '--no-interaction' => true]);

    foreach ([['A', 100, 80, 4.0], ['B', 79, 70, 3.0], ['D', 69, 50, 2.0], ['F', 49, 0, 0.0]] as [$title, $upper, $lower, $point]) {
        DB::table('res_grades')->insert(['title' => $title, 'upper' => $upper, 'lower' => $lower, 'point' => $point, 'plan' => 20060001]);
    }
    foreach ([1005 => 'نجاح', 4001 => 'ملحق', 9999 => 'غير مكتمل'] as $code => $name) {
        DB::table('res_ltrecoms')->insert(['code' => $code, 'name_ar' => $name]);
    }
    DB::table('reg_faculties')->insert(['code' => 10, 'name_ar' => 'كلية الاختبار']);
    foreach ([5 => 'رياضيات', 6 => 'فيزياء'] as $code => $name) {
        DB::table('subjects')->insert(['code' => $code, 'name_ar' => $name, 'name_en' => "Subject {$code}", 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1]);
    }
    foreach ([1, 2] as $no) {
        DB::table('students')->insert(['university_no' => $no, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'batch_code' => 2026, 'stady_code' => 20060001]);
    }

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
    $this->superAdmin = $this->superAdmin->fresh();

    $this->approver = User::factory()->create();
    $this->service = app(FinalizationService::class);
    $this->service->setApprovalCode($this->approver, APPROVER_CODE);
    $this->approver = $this->approver->fresh();
    DB::table('reg_faculties')->where('code', 10)->update(['results_approver_id' => $this->approver->id]);
});

function seedCompleteCohort(bool $approveSubjects = true): void
{
    $row = fn (int $no, int $subject, int $exam, ?int $mark, ?string $grade) => Mark::create([
        'university_no' => $no, 'subject_code' => $subject, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1,
        'batch_code' => 2026, 'semester' => 1, 'examType' => $exam, 'mark' => $mark, 'grade' => $grade, 'point' => 2.0,
    ]);

    $row(1, 5, 1, 70, 'B');
    $row(1, 6, 1, 40, 'F');
    $row(1, 6, 2, 50, '*D');
    $row(2, 5, 1, 85, 'A');
    $row(2, 6, 1, 80, 'A');

    $recom = fn (int $no, int $exam, int $code) => Recom::create([
        'university_no' => $no, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'batch_code' => 2026,
        'semester' => 1, 'examType' => $exam, 'countSubject' => 2, 'gpa' => 3.0, 'recom_code' => $code,
    ]);

    $recom(1, 1, 4001);
    $recom(1, 2, 1005);
    $recom(2, 1, 1005);

    if ($approveSubjects) {
        foreach ([5, 6] as $subject) {
            DB::table('subject_approval_statuses')->insert(['subject_code' => $subject, 'batch_code' => 2026, 'current_stage' => 'approved']);
        }
    }
}

function finalizeCohort(): Finalization
{
    return test()->service->finalize(test()->approver, COHORT, APPROVER_CODE, '10.0.0.1');
}

// ───────────── المعتمد والرمز ─────────────

it('stores only a hash of the approval code and keeps it unique per person', function () {
    $other = User::factory()->create();

    expect($this->approver->approval_code_hash)->toBe(FinalizationService::codeHash(APPROVER_CODE))->not->toContain(APPROVER_CODE)
        ->and(fn () => $this->service->setApprovalCode($other, APPROVER_CODE))->toThrow(DomainException::class, 'مستخدم من شخص آخر')
        ->and(fn () => $this->service->setApprovalCode($other, '123'))->toThrow(DomainException::class, 'ألا يقل');

    $this->service->setApprovalCode($this->approver, APPROVER_CODE);
});

it('lets only the faculty designated approver approve', function () {
    $other = User::factory()->create();
    $this->service->setApprovalCode($other, 'Another-code-1');

    expect($this->service->canApprove($this->approver, 10))->toBeTrue()
        ->and($this->service->canApprove($other->fresh(), 10))->toBeFalse()
        ->and($this->service->canApprove($this->approver, 11))->toBeFalse()
        ->and($this->service->approverFor(10)->is($this->approver))->toBeTrue();
});

it('rejects a wrong code and locks the approver out after repeated attempts', function () {
    seedCompleteCohort();

    foreach (range(1, FinalizationService::MAX_ATTEMPTS) as $attempt) {
        expect(fn () => $this->service->finalize($this->approver, COHORT, 'wrong-code'))->toThrow(DomainException::class, 'غير صحيح');
    }

    expect(fn () => $this->service->finalize($this->approver, COHORT, APPROVER_CODE))->toThrow(DomainException::class, 'تجاوز عدد المحاولات')
        ->and(Finalization::count())->toBe(0);
});

// ───────────── موانع الاعتماد ─────────────

it('blocks approval while subjects have not reached the approved stage', function () {
    seedCompleteCohort(approveSubjects: false);

    expect($this->service->blockers(COHORT))->toContain('2 مادة لم تصل مرحلة (معتمد نهائياً).')
        ->and(fn () => finalizeCohort())->toThrow(DomainException::class, 'لا يمكن الاعتماد');
});

it('blocks approval for incomplete students and for a missing supplementary round', function () {
    seedCompleteCohort();
    Recom::query()->where('university_no', 2)->update(['recom_code' => 9999]);
    Recom::query()->where('university_no', 1)->where('examType', 2)->delete();

    $blockers = implode(' | ', $this->service->blockers(COHORT));

    expect($blockers)->toContain('1 طالب بدرجات ناقصة')->toContain('1 طالب لم تُدخل نتيجة ملحقه');
});

it('blocks approval when the cohort has no results', function () {
    expect($this->service->blockers(COHORT))->toBe(['لا توجد نتائج لهذه المجموعة.']);
});

// ───────────── الاعتماد والسجل ─────────────

it('approves: signed snapshot, effective marks only, names stored, rows locked', function () {
    seedCompleteCohort();

    $finalization = finalizeCohort();

    expect($finalization->version)->toBe(1)->and($finalization->is_active)->toBeTrue()
        ->and($finalization->kind)->toBe('approval')->and($finalization->user_id)->toBe($this->approver->id)
        ->and($finalization->ip)->toBe('10.0.0.1')->and($finalization->signature)->toHaveLength(64)
        ->and($finalization->students_count)->toBe(2)->and($finalization->marks_count)->toBe(4);

    $physics = RecordMark::query()->where('university_no', 1)->where('subject_code', 6)->get();

    expect($physics)->toHaveCount(1)
        ->and($physics->first()->only(['source_exam_type', 'mark', 'grade', 'subject_name_ar', 'subject_name_en']))
        ->toBe(['source_exam_type' => 2, 'mark' => 50, 'grade' => '*D', 'subject_name_ar' => 'فيزياء', 'subject_name_en' => 'Subject 6'])
        ->and($finalization->recoms()->where('university_no', 1)->where('examType', 2)->first()->recom_name)->toBe('نجاح')
        ->and(Mark::query()->where('is_locked', false)->count())->toBe(0)
        ->and(Recom::query()->where('is_locked', false)->count())->toBe(0)
        ->and($this->service->isIntact($finalization))->toBeTrue();
});

it('refuses a second approval of the same cohort', function () {
    seedCompleteCohort();
    finalizeCohort();

    expect(fn () => finalizeCohort())->toThrow(DomainException::class, 'اعتماد هذه المجموعة مسبقاً');
});

it('detects tampering with the approved record', function () {
    seedCompleteCohort();
    $finalization = finalizeCohort();

    RecordMark::query()->where('university_no', 2)->where('subject_code', 5)->update(['mark' => 100]);

    expect($this->service->isIntact($finalization->fresh()))->toBeFalse();
});

// ───────────── منع التعديل ─────────────

it('prevents anyone but the super admin from changing an approved mark', function () {
    seedCompleteCohort();
    finalizeCohort();
    $mark = Mark::query()->where('university_no', 2)->where('subject_code', 5)->first();

    auth()->setUser($this->approver);
    expect(fn () => $mark->update(['mark' => 99]))->toThrow(DomainException::class, 'معتمدة ومقفلة')
        ->and(fn () => $mark->delete())->toThrow(DomainException::class, 'معتمدة ومقفلة');

    auth()->logout();
    expect(fn () => $mark->update(['mark' => 99]))->toThrow(DomainException::class);

    auth()->setUser($this->superAdmin);
    $mark->update(['mark' => 99]);

    expect($mark->fresh()->mark)->toBe(99);
});

it('rejects a normal entry on an approved mark through the entry service', function () {
    seedCompleteCohort();
    finalizeCohort();

    $professor = User::factory()->create();
    $professor->assignRole(Role::create(['name' => 'approved', 'guard_name' => 'web']));
    $professor->faculties()->attach(10);

    $student = (new Student)->forceFill(['university_no' => 2, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'batch_code' => 2026, 'stady_code' => 20060001]);

    expect(fn () => app(MarkEntryService::class)->saveScore($professor->fresh(), $student, Subject::find(5), 2026, 1, 'mark', 60))
        ->toThrow(DomainException::class, 'معتمدة ومقفلة');
});

it('keeps the approved recommendation when a refresh runs for a non super admin', function () {
    seedCompleteCohort();
    finalizeCohort();
    $before = Recom::query()->where('university_no', 2)->first();

    auth()->setUser($this->approver);
    $student = (new Student)->forceFill(['university_no' => 2, 'department_code' => 100, 'special_code' => null]);
    $returned = (new RegulationService)->refresh($student, 10, 1, 2026, 1, 1);

    expect($returned->is($before))->toBeTrue()->and((int) $returned->gpa)->toBe(3);
});

// ───────────── تعديل المدير الأعلى ─────────────

it('lets the super admin amend an approved mark with an audit entry and a new numbered version', function () {
    seedCompleteCohort();
    $v1 = finalizeCohort();

    auth()->setUser($this->superAdmin);
    $student = (new Student)->forceFill(['university_no' => 2, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'batch_code' => 2026, 'stady_code' => 20060001]);

    app(MarkEntryService::class)->saveScore($this->superAdmin, $student, Subject::find(5), 2026, 1, 'mark', 60);

    $v2 = Finalization::query()->active()->first();

    expect($v1->fresh()->is_active)->toBeFalse()
        ->and($v2->version)->toBe(2)->and($v2->kind)->toBe('amendment')->and($v2->user_id)->toBe($this->superAdmin->id)
        ->and($v2->reason)->toContain('2')->toContain('5')
        ->and($v2->marks()->where('university_no', 2)->where('subject_code', 5)->value('mark'))->toBe(60)
        ->and($v2->marks()->where('university_no', 1)->where('subject_code', 6)->value('grade'))->toBe('*D')
        ->and($v2->marks()->count())->toBe(4)
        ->and($v1->marks()->where('university_no', 2)->where('subject_code', 5)->value('mark'))->toBe(85)
        ->and($this->service->isIntact($v2))->toBeTrue()->and($this->service->isIntact($v1->fresh()))->toBeTrue()
        ->and(Mark::query()->where('is_locked', false)->count())->toBe(0);

    $audit = Activity::query()->where('log_name', 'results')->first();

    expect($audit)->not->toBeNull()->and($audit->causer_id)->toBe($this->superAdmin->id)
        ->and($audit->properties['before']['mark'])->toBe(85)->and($audit->properties['after']['mark'])->toBe(60);
});

// ───────────── الشهادات ─────────────

it('builds certificates from the approved record and never from later live edits', function () {
    seedCompleteCohort();
    finalizeCohort();

    auth()->setUser($this->superAdmin);
    DB::table('res_marks')->where('university_no', 2)->where('subject_code', 5)->update(['mark' => 1, 'grade' => 'F']);

    $marks = TranscriptGenerator::marksFor(2);

    expect($marks)->toHaveCount(2)->and($marks->first())->toBeInstanceOf(RecordMark::class)
        ->and($marks->firstWhere('subject_code', 5)->mark)->toBe(85);
});

it('reads the newest approved version for certificates after an amendment', function () {
    seedCompleteCohort();
    finalizeCohort();

    auth()->setUser($this->superAdmin);
    $student = (new Student)->forceFill(['university_no' => 2, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'batch_code' => 2026, 'stady_code' => 20060001]);
    app(MarkEntryService::class)->saveScore($this->superAdmin, $student, Subject::find(5), 2026, 1, 'mark', 60);

    expect(TranscriptGenerator::marksFor(2)->firstWhere('subject_code', 5)->mark)->toBe(60);
});

it('falls back to live marks for unapproved students unless record-only mode is on', function () {
    seedCompleteCohort();

    config(['results.certificates_from_record_only' => false]);
    $live = TranscriptGenerator::marksFor(2);

    expect($live)->toHaveCount(2)->and($live->first())->toBeInstanceOf(Mark::class);

    config(['results.certificates_from_record_only' => true]);

    expect(TranscriptGenerator::marksFor(2))->toHaveCount(0);
});
