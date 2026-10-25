<?php

use App\Models\Res\Grade;
use App\Models\Res\Mark;
use App\Models\Res\Subject;
use App\Models\User;
use App\Services\Res\MarkCurveService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

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
    Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_25_042931_create_mark_curves_table.php', '--no-interaction' => true]);
    Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_25_042932_create_mark_curve_items_table.php', '--no-interaction' => true]);

    foreach ([['A', 100, 80, 4.0], ['B', 79, 70, 3.0], ['D', 69, 50, 2.0], ['F', 49, 0, 0.0]] as [$title, $upper, $lower, $point]) {
        DB::table('res_grades')->insert(['title' => $title, 'upper' => $upper, 'lower' => $lower, 'point' => $point, 'plan' => 20060001]);
    }
    DB::table('reg_faculties')->insert(['code' => 10, 'name_ar' => 'كلية الاختبار']);
    DB::table('subjects')->insert(['code' => 5, 'name_ar' => 'رياضيات', 'name_en' => 'Math', 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1]);

    foreach ([30, 45, 48, 60, 90] as $index => $mark) {
        $no = $index + 1;
        DB::table('students')->insert(['university_no' => $no, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'batch_code' => 2026, 'stady_code' => 20060001]);
        Mark::create([
            'university_no' => $no, 'subject_code' => 5, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1,
            'batch_code' => 2026, 'semester' => 1, 'examType' => 1, 'mark' => $mark,
            'grade' => Grade::forMark($mark, '20060001')->title, 'point' => Grade::forMark($mark, '20060001')->point,
        ]);
    }

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
    $this->admin = $this->admin->fresh();
    $this->subject = Subject::query()->find(5);
    $this->service = app(MarkCurveService::class);
});

function marksByStudent(): array
{
    return Mark::query()->orderBy('university_no')->pluck('mark', 'university_no')->all();
}

it('shows the grade distribution with mean, deviation and failures', function () {
    $totals = $this->service->marksFor($this->subject, 2026, 1)->map(fn ($m) => (int) $m->mark);
    $distribution = $this->service->distribution($totals, '20060001');

    expect($distribution['count'])->toBe(5)
        ->and($distribution['failCount'])->toBe(3)
        ->and($distribution['mean'])->toBe(54.6)
        ->and($distribution['histogram'][45])->toBe(1)
        ->and(collect($distribution['grades'])->pluck('count', 'title')->all())->toBe(['F' => 3, 'D' => 1, 'B' => 0, 'A' => 1]);
});

it('adds points to a mark range only, updates grades and keeps the previous values', function () {
    $curve = $this->service->apply($this->admin, $this->subject, 2026, 1, 'range', ['from' => 45, 'to' => 49, 'delta' => 5]);

    expect(marksByStudent())->toBe([1 => 30, 2 => 50, 3 => 53, 4 => 60, 5 => 90])
        ->and($curve->affected_count)->toBe(2)
        ->and(Mark::query()->where('university_no', 2)->value('grade'))->toBe('D')
        ->and($curve->items()->where('university_no', 2)->first()->old_mark)->toBe(45)
        ->and($curve->items()->where('university_no', 2)->first()->old_grade)->toBe('F');
});

it('clamps adjusted marks to the allowed range', function () {
    $this->service->apply($this->admin, $this->subject, 2026, 1, 'range', ['from' => 80, 'to' => 100, 'delta' => 30]);

    expect(marksByStudent()[5])->toBe(100);
});

it('reshapes the distribution to a target mean and deviation', function () {
    $this->service->apply($this->admin, $this->subject, 2026, 1, 'stddev', ['targetMean' => 65, 'targetSd' => 10]);

    $after = $this->service->distribution(collect(marksByStudent()));

    expect($after['mean'])->toBeGreaterThan(64)->toBeLessThan(66)
        ->and($after['sd'])->toBeGreaterThan(8)->toBeLessThan(12);
});

it('reverts a curve and restores marks and grades exactly', function () {
    $curve = $this->service->apply($this->admin, $this->subject, 2026, 1, 'range', ['from' => 0, 'to' => 49, 'delta' => 10]);

    $this->service->revert($this->admin, $curve);

    expect(marksByStudent())->toBe([1 => 30, 2 => 45, 3 => 48, 4 => 60, 5 => 90])
        ->and(Mark::query()->where('university_no', 2)->value('grade'))->toBe('F')
        ->and($curve->fresh()->isReverted())->toBeTrue()
        ->and(fn () => $this->service->revert($this->admin, $curve->fresh()))->toThrow(DomainException::class, 'مسبقاً');
});

it('refuses to revert when a mark was edited after the curve', function () {
    $curve = $this->service->apply($this->admin, $this->subject, 2026, 1, 'range', ['from' => 0, 'to' => 49, 'delta' => 10]);
    Mark::query()->where('university_no', 1)->update(['mark' => 77]);

    expect(fn () => $this->service->revert($this->admin, $curve))->toThrow(DomainException::class, 'عُدّلت درجة')
        ->and(marksByStudent()[2])->toBe(55);
});

it('refuses to curve locked marks and invalid parameters', function () {
    expect(fn () => $this->service->apply($this->admin, $this->subject, 2026, 1, 'range', ['from' => 50, 'to' => 40, 'delta' => 5]))
        ->toThrow(DomainException::class, 'أكبر من نهايته')
        ->and(fn () => $this->service->apply($this->admin, $this->subject, 2026, 1, 'range', ['from' => 0, 'to' => 100, 'delta' => 0]))
        ->toThrow(DomainException::class, 'الإضافة أو النقص');

    Mark::query()->where('university_no', 1)->update(['is_locked' => true]);

    expect(fn () => $this->service->apply($this->admin, $this->subject, 2026, 1, 'range', ['from' => 0, 'to' => 49, 'delta' => 5]))
        ->toThrow(DomainException::class, 'مقفلة');
    expect(marksByStudent())->toBe([1 => 30, 2 => 45, 3 => 48, 4 => 60, 5 => 90]);
});
