<?php

use App\Models\Res\Mark;
use App\Models\Res\Reson;
use App\Models\Res\Subject;
use App\Models\Student;
use App\Models\User;
use App\Services\Res\MarkEntryService;
use App\Services\Res\RegulationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['0001_01_01_000000_create_users_table.php', '2026_02_08_143514_create_permission_tables.php'] as $migration) {
        Artisan::call('migrate', ['--path' => "database/migrations/{$migration}", '--no-interaction' => true]);
    }

    Schema::create('subjects', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->unsignedBigInteger('faculty_code');
        $table->unsignedBigInteger('department_code')->nullable();
        $table->unsignedBigInteger('special_code')->nullable();
        $table->unsignedBigInteger('level_code');
        $table->unsignedTinyInteger('semester_id')->default(1);
        $table->unsignedTinyInteger('exam')->default(100);
        $table->decimal('crch', 4, 1)->default(3);
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
        $table->unsignedBigInteger('recom_code')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    foreach ([['A', 100, 80, 4.0], ['B', 79, 70, 3.0], ['D', 69, 50, 2.0], ['F', 49, 0, 0.0]] as [$title, $upper, $lower, $point]) {
        DB::table('res_grades')->insert(['title' => $title, 'upper' => $upper, 'lower' => $lower, 'point' => $point, 'plan' => 20060001]);
    }

    DB::table('subjects')->insert(['code' => 5, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1]);
    DB::table('subjects')->insert(['code' => 6, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1]);
    DB::table('subjects')->insert(['code' => 7, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'exam' => 70]);

    $this->service = new MarkEntryService(new RegulationService);
    $this->user = User::factory()->create();
    $this->user->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
    $this->user = $this->user->fresh();

    $this->student = (new Student)->forceFill([
        'university_no' => 1, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'batch_code' => 2026, 'stady_code' => 20060001,
    ]);
    $this->subject = Subject::find(5);
});

function addReson(array $attributes): Reson
{
    return Reson::create($attributes + ['name_ar' => 'سبب '.$attributes['code'], 'description' => null]);
}

function saveReason(?int $code, int $subjectCode = 5): array
{
    return test()->service->saveReason(test()->user, test()->student, Subject::find($subjectCode), 2026, 1, $code);
}

function saveScore(int $value, string $field = 'mark', int $subjectCode = 5): array
{
    return test()->service->saveScore(test()->user, test()->student, Subject::find($subjectCode), 2026, 1, $field, $value);
}

it('copies the reason mark, grade and points into the mark row', function () {
    addReson(['code' => 2003, 'mark' => 0, 'grade' => 'حرمان', 'point' => 0]);
    saveScore(60, 'mark', 7);
    saveScore(10, 'cwork', 7);

    $result = saveReason(2003, 7);

    expect($result['mark']->only(['mark', 'cwork', 'grade', 'point', 'reson_code']))
        ->toBe(['mark' => 0, 'cwork' => null, 'grade' => 'حرمان', 'point' => 0.0, 'reson_code' => 2003]);
});

it('keeps the existing score when the reason defines none', function () {
    addReson(['code' => 1001]);
    saveScore(85);

    $result = saveReason(1001);

    expect($result['mark']->only(['mark', 'grade', 'point', 'reson_code']))
        ->toBe(['mark' => 85, 'grade' => 'A', 'point' => 4.0, 'reson_code' => 1001]);
});

it('fills the missing grade and points from the grading table when only a mark is given', function () {
    addReson(['code' => 1002, 'mark' => 50]);

    $result = saveReason(1002);

    expect($result['mark']->only(['mark', 'grade', 'point']))->toBe(['mark' => 50, 'grade' => 'D', 'point' => 2.0]);
});

it('keeps an explicit grade and fills only the missing points', function () {
    addReson(['code' => 1003, 'mark' => 50, 'grade' => 'نجاح خاص']);

    $result = saveReason(1003);

    expect($result['mark']->only(['mark', 'grade', 'point']))->toBe(['mark' => 50, 'grade' => 'نجاح خاص', 'point' => 2.0]);
});

it('lets a reason decision override the regulation decision', function () {
    addReson(['code' => 2005, 'mark' => 0, 'grade' => 'فصل', 'point' => 0, 'recom_code' => 4002]);
    saveScore(90, 'mark', 6);

    $result = saveReason(2005);

    expect((string) $result['recom']->recom_code)->toBe('4002');
});

it('uses the regulation decision when the reason has none', function () {
    addReson(['code' => 2002, 'mark' => 0, 'grade' => 'رسوب', 'point' => 0]);
    saveScore(90, 'mark', 6);

    $result = saveReason(2002);

    expect((string) $result['recom']->recom_code)->not->toBe('4002')
        ->and((int) $result['recom']->s_fail)->toBe(1);
});

it('returns to the regulation decision and re-grades when the reason is cleared', function () {
    addReson(['code' => 2005, 'recom_code' => 4002, 'grade' => 'فصل', 'mark' => 0, 'point' => 0]);
    saveScore(90, 'mark', 6);
    saveReason(2005);

    $cleared = saveReason(null);

    expect($cleared['mark']->reson_code)->toBeNull()
        ->and($cleared['mark']->grade)->toBeNull()
        ->and((string) $cleared['recom']->recom_code)->not->toBe('4002');
});

it('removes the values a reason applied when it is cleared or replaced by one that applies none', function () {
    addReson(['code' => 2003, 'mark' => 0, 'grade' => 'حرمان', 'point' => 0]);
    addReson(['code' => 1001]);

    saveReason(2003);
    $replaced = saveReason(1001);

    expect($replaced['mark']->only(['mark', 'grade', 'point', 'reson_code']))
        ->toBe(['mark' => null, 'grade' => null, 'point' => null, 'reson_code' => 1001]);

    saveReason(2003);
    $cleared = saveReason(null);

    expect($cleared['mark']->only(['mark', 'grade', 'point', 'reson_code']))
        ->toBe(['mark' => null, 'grade' => null, 'point' => null, 'reson_code' => null]);
});

it('drops the reason and its decision once a score is entered afterwards', function () {
    addReson(['code' => 2005, 'recom_code' => 4002, 'grade' => 'فصل', 'mark' => 0, 'point' => 0]);
    saveReason(2005);

    $result = saveScore(75);

    expect($result['mark']->reson_code)->toBeNull()
        ->and($result['mark']->grade)->toBe('B')
        ->and((string) $result['recom']->recom_code)->not->toBe('4002');
});

it('lets the most recently saved reason decision win when several exist', function () {
    addReson(['code' => 2005, 'recom_code' => 4002, 'grade' => 'فصل', 'mark' => 0, 'point' => 0]);
    addReson(['code' => 2004, 'recom_code' => 4004, 'grade' => 'غش', 'mark' => 0, 'point' => 0]);

    saveReason(2005, 5);
    Mark::query()->where('subject_code', 5)->update(['updated_at' => now()->subHour()]);

    $result = saveReason(2004, 6);

    expect((string) $result['recom']->recom_code)->toBe('4004');
});

it('rejects an unknown reason code', function () {
    expect(fn () => saveReason(9999))->toThrow(DomainException::class, 'السبب المختار غير موجود');
});

it('reports whether a reason applies a score', function () {
    expect((new Reson)->forceFill([])->appliesScore())->toBeFalse()
        ->and((new Reson)->forceFill(['mark' => 0])->appliesScore())->toBeTrue()
        ->and((new Reson)->forceFill(['grade' => 'x'])->appliesScore())->toBeTrue()
        ->and((new Reson)->forceFill(['point' => 0])->appliesScore())->toBeTrue()
        ->and((new Reson)->forceFill(['grade' => ''])->appliesScore())->toBeFalse();
});

it('stamps the mark and recom with the student faculty, department and special', function () {
    $student = (new Student)->forceFill([
        'university_no' => 2, 'faculty_code' => 10, 'department_code' => 100, 'special_code' => 7,
        'level_code' => 1, 'batch_code' => 2026, 'stady_code' => 20060001,
    ]);

    $result = test()->service->saveScore(test()->user, $student, Subject::find(5), 2026, 1, 'mark', 80);

    expect($result['mark']->only(['faculty_code', 'department_code', 'special_code']))->toBe(['faculty_code' => 10, 'department_code' => 100, 'special_code' => 7])
        ->and($result['recom']->special_code)->toBe(7);
});

it('corrects a stale special on an existing mark row when saved again', function () {
    $student = (new Student)->forceFill([
        'university_no' => 2, 'faculty_code' => 10, 'department_code' => 100, 'special_code' => 7,
        'level_code' => 1, 'batch_code' => 2026, 'stady_code' => 20060001,
    ]);
    Mark::create(['university_no' => 2, 'subject_code' => 5, 'faculty_code' => 10, 'department_code' => 100, 'special_code' => null, 'level_code' => 1, 'batch_code' => 2026, 'semester' => 1, 'examType' => 1, 'mark' => 50]);

    $result = test()->service->saveScore(test()->user, $student, Subject::find(5), 2026, 1, 'mark', 80);

    expect($result['mark']->special_code)->toBe(7);
});

it('lists only the students of the subject special, and of no special for a general subject when strict', function () {
    $special = (new Subject)->forceFill(['department_code' => 100, 'special_code' => 7]);
    $general = (new Subject)->forceFill(['department_code' => 100, 'special_code' => null]);

    expect($special->constrainStudents(Student::query())->toRawSql())
        ->toContain('"department_code" = 100')->toContain('"special_code" = 7')
        ->and($general->constrainStudents(Student::query())->toRawSql())->not->toContain('special_code');

    config(['regulations.entry.strict_special' => true]);

    expect($general->constrainStudents(Student::query())->toRawSql())->toContain('"special_code" is null');
});

it('refuses a common subject for a student with a special when strict', function () {
    $student = (new Student)->forceFill([
        'university_no' => 2, 'faculty_code' => 10, 'department_code' => 100, 'special_code' => 7,
        'level_code' => 1, 'batch_code' => 2026, 'stady_code' => 20060001,
    ]);

    test()->service->assertEligible($student, Subject::find(5), 2026);

    config(['regulations.entry.strict_special' => true]);

    expect(fn () => test()->service->assertEligible($student, Subject::find(5), 2026))
        ->toThrow(DomainException::class, 'مادة عامة');
});
