<?php

use App\Models\Res\Recom;
use App\Models\Student;
use App\Reg\RecomCode;
use App\Services\Res\AcademicYearService;
use App\Services\Res\RegulationProfile;
use App\Services\Res\RegulationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('reg_levels', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->string('name_ar')->nullable();
        $table->unsignedBigInteger('parent_code')->nullable();
        $table->softDeletes();
    });

    Schema::create('subjects', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->unsignedBigInteger('faculty_code');
        $table->unsignedBigInteger('department_code')->nullable();
        $table->unsignedBigInteger('special_code')->nullable();
        $table->unsignedBigInteger('level_code');
        $table->unsignedTinyInteger('semester_id')->default(1);
        $table->decimal('crch', 4, 1)->default(3);
        $table->softDeletes();
    });

    Schema::create('res_marks', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('university_no');
        $table->unsignedBigInteger('subject_code');
        $table->unsignedBigInteger('faculty_code');
        $table->unsignedBigInteger('level_code');
        $table->unsignedBigInteger('batch_code');
        $table->unsignedTinyInteger('semester')->default(1);
        $table->unsignedTinyInteger('examType')->default(1);
        $table->integer('mark')->nullable();
        $table->float('point')->nullable();
        $table->unsignedBigInteger('reson_code')->nullable();
        $table->timestamps();
        $table->softDeletes();
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

    foreach ([['A', 100, 80, 4.0], ['D', 79, 50, 2.0], ['F', 49, 0, 0.0]] as [$title, $upper, $lower, $point]) {
        DB::table('res_grades')->insert(['title' => $title, 'upper' => $upper, 'lower' => $lower, 'point' => $point, 'plan' => 20060001]);
    }

    DB::table('reg_levels')->insert([
        ['code' => 1, 'name_ar' => 'first semester', 'parent_code' => 100],
        ['code' => 2, 'name_ar' => 'second semester', 'parent_code' => 100],
        ['code' => 3, 'name_ar' => 'standalone semester', 'parent_code' => null],
        ['code' => 100, 'name_ar' => 'first year', 'parent_code' => null],
    ]);

    foreach ([[5, 1, 1], [6, 1, 1], [7, 2, 2], [8, 2, 2]] as [$code, $level, $semester]) {
        DB::table('subjects')->insert(['code' => $code, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => $level, 'semester_id' => $semester]);
    }

    foreach ([[5, 1, 1, 40, 0.0], [6, 1, 1, 80, 4.0], [7, 2, 2, 80, 4.0], [8, 2, 2, 80, 4.0]] as [$subject, $level, $semester, $mark, $point]) {
        DB::table('res_marks')->insert([
            'university_no' => 1, 'subject_code' => $subject, 'faculty_code' => 10, 'level_code' => $level,
            'batch_code' => 2026, 'semester' => $semester, 'examType' => 1, 'mark' => $mark, 'point' => $point,
        ]);
    }

    $this->student = (new Student)->forceFill([
        'university_no' => 1, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'batch_code' => 2026, 'stady_code' => 20060001,
    ]);
});

it('groups the levels of one academic year by their parent level', function () {
    $years = new AcademicYearService;

    expect($years->levelsOfYear(1))->toEqualCanonicalizing([1, 2])
        ->and($years->levelsOfYear(2))->toEqualCanonicalizing([1, 2])
        ->and($years->levelsOfYear(3))->toBe([3]);
});

it('keeps the semester system exactly as before: each level is settled on its own', function () {
    $recom = (new RegulationService)->refresh($this->student, 10, 1, 2026, 1, 2);

    expect($recom->semester->value)->toBe(2)
        ->and($recom->crch)->toBe(6.0)
        ->and($recom->recom_code)->toBe((string) RecomCode::RemoveFail->value);
});

it('settles supplementary exams at the end of the year over both semesters in the semester-annual system', function () {
    config(['regulations.academic_system' => 'semester_annual']);

    $first = (new RegulationService)->refresh($this->student, 10, 1, 2026, 1, 1);

    expect($first->semester->value)->toBe(1)
        ->and($first->crch)->toBe(6.0)
        ->and($first->recom_code)->toBe((string) RecomCode::Supplementary->value);

    $yearEnd = (new RegulationService)->refresh($this->student, 10, 2, 2026, 2, 2);

    expect($yearEnd->semester->value)->toBe(3)
        ->and($yearEnd->examType->value)->toBe(2)
        ->and($yearEnd->crch)->toBe(12.0)
        ->and($yearEnd->crch_fail)->toBe(3.0)
        ->and($yearEnd->recom_code)->toBe((string) RecomCode::RemoveFail->value)
        ->and(Recom::query()->where('university_no', 1)->count())->toBe(2);
});

it('keeps the whole year in one period in the annual system', function () {
    config(['regulations.academic_system' => 'annual']);

    expect(RegulationProfile::current()->recomSemester(1, 1))->toBe(3)
        ->and(RegulationProfile::current()->recomSemester(1, 2))->toBe(3)
        ->and(RegulationProfile::current()->defaultSemester())->toBe(3)
        ->and(RegulationProfile::current()->subjectSemesters())->toBe([3]);
});

it('offers both semesters for subjects only in the semester-annual system', function () {
    expect(RegulationProfile::current()->subjectSemesters())->toBe([1])
        ->and(RegulationProfile::current()->recomSemester(1, 2))->toBe(2)
        ->and(RegulationProfile::current()->recomSemester(2, 1))->toBe(2);

    config(['regulations.academic_system' => 'semester_annual']);

    expect(RegulationProfile::current()->subjectSemesters())->toBe([1, 2])
        ->and(RegulationProfile::current()->recomSemester(1, 1))->toBe(1)
        ->and(RegulationProfile::current()->recomSemester(1, 2))->toBe(3)
        ->and(RegulationProfile::current()->combinesYearForSupplements())->toBeTrue();
});
