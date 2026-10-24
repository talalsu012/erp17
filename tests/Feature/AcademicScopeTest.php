<?php

use App\Filament\Clusters\Res\Resources\Marks\MarkResource;
use App\Filament\Clusters\Res\Resources\Recoms\RecomResource;
use App\Models\Reg\Department;
use App\Models\Reg\Level;
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
    foreach ([
        '0001_01_01_000000_create_users_table.php',
        '2026_02_08_143514_create_permission_tables.php',
        '2026_10_04_111526_create_reg_department_user_table.php',
        '2026_10_04_111527_create_reg_level_user_table.php',
    ] as $migration) {
        Artisan::call('migrate', ['--path' => "database/migrations/{$migration}", '--no-interaction' => true]);
    }

    Schema::create('reg_faculties', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->string('name_ar')->nullable();
        $table->softDeletes();
    });
    Schema::create('reg_departments', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->unsignedBigInteger('faculty_code');
        $table->string('name_ar')->nullable();
        $table->softDeletes();
    });
    Schema::create('reg_levels', function (Blueprint $table) {
        $table->unsignedBigInteger('code')->primary();
        $table->string('name_ar')->nullable();
        $table->softDeletes();
    });
    Schema::create('reg_faculty_user', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('faculty_code');
        $table->timestamps();
    });

    foreach ([10, 11] as $faculty) {
        DB::table('reg_faculties')->insert(['code' => $faculty, 'name_ar' => "faculty {$faculty}"]);
    }
    foreach ([[100, 10], [101, 10], [110, 11]] as [$department, $faculty]) {
        DB::table('reg_departments')->insert(['code' => $department, 'faculty_code' => $faculty, 'name_ar' => "dept {$department}"]);
    }
    foreach ([1, 2, 3] as $level) {
        DB::table('reg_levels')->insert(['code' => $level, 'name_ar' => "level {$level}"]);
    }
});

function academicUser(array $faculties = [], array $departments = [], array $levels = []): User
{
    $user = User::factory()->create();

    $user->faculties()->attach($faculties);
    $user->departments()->attach($departments);
    $user->levels()->attach($levels);

    return $user->fresh();
}

it('leaves departments and levels unrestricted when none are assigned', function () {
    $user = academicUser(faculties: [10]);

    expect($user->allowedDepartmentCodes())->toBeNull()
        ->and($user->allowedLevelCodes())->toBeNull()
        ->and($user->canAccessAcademic(10, 101, 3))->toBeTrue();
});

it('restricts access to the assigned departments and levels', function () {
    $user = academicUser(faculties: [10], departments: [100], levels: [1, 2]);

    expect($user->canAccessAcademic(10, 100, 1))->toBeTrue()
        ->and($user->canAccessAcademic(10, 101, 1))->toBeFalse()
        ->and($user->canAccessAcademic(10, 100, 3))->toBeFalse();
});

it('treats a blank department or level as unspecified rather than denied', function () {
    $user = academicUser(faculties: [10], departments: [100], levels: [1]);

    expect($user->canAccessAcademic(10, null, null))->toBeTrue();
});

it('keeps the faculty strict: no assignment means no access', function () {
    $user = academicUser(faculties: [10]);

    expect($user->canAccessAcademic(10))->toBeTrue()
        ->and($user->canAccessAcademic(11))->toBeFalse()
        ->and($user->canAccessAcademic(null))->toBeFalse()
        ->and(academicUser()->canAccessAcademic(10))->toBeFalse();
});

it('lets a super admin see everything', function () {
    $user = academicUser(faculties: [10], departments: [100], levels: [1]);
    $user->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
    $user = $user->fresh();

    expect($user->allowedFacultyCodes())->toBeNull()
        ->and($user->allowedDepartmentCodes())->toBeNull()
        ->and($user->allowedLevelCodes())->toBeNull()
        ->and($user->canAccessAcademic(11, 110, 3))->toBeTrue();
});

it('constrains a query to the user scope', function () {
    $user = academicUser(faculties: [10], departments: [100, 101], levels: [2]);

    $query = $user->constrainToAcademicScope(Student::query());

    expect($query->toSql())->toContain('"faculty_code" in', '"department_code" in', '"level_code" in')
        ->and($query->getBindings())->toBe([10, 100, 101, 2]);
});

it('can keep unassigned rows such as general subjects visible', function () {
    $user = academicUser(faculties: [10], departments: [100]);

    $sql = $user->constrainToAcademicScope(Student::query(), includeUnassigned: ['department'])->toSql();

    expect($sql)->toContain('"department_code" in')->toContain('"department_code" is null');
});

it('scopes department and level lists for the authenticated user', function () {
    $user = academicUser(faculties: [10], departments: [100], levels: [2, 3]);
    auth()->setUser($user);

    expect(Department::forAuthUser()->pluck('code')->all())->toBe([100])
        ->and(Level::forAuthUser()->pluck('code')->sort()->values()->all())->toBe([2, 3]);
});

it('limits departments to the allowed faculties even without department assignments', function () {
    auth()->setUser(academicUser(faculties: [11]));

    expect(Department::forAuthUser()->pluck('code')->all())->toBe([110]);
});

it('returns nothing from the scopes when nobody is signed in', function () {
    auth()->logout();

    expect(Department::forAuthUser()->count())->toBe(0)
        ->and(Level::forAuthUser()->count())->toBe(0);
});

it('blocks saving marks for a student outside the user scope', function () {
    $service = new MarkEntryService(new RegulationService);
    $user = academicUser(faculties: [10], departments: [100], levels: [1]);

    $inside = (new Student)->forceFill(['faculty_code' => 10, 'department_code' => 100, 'level_code' => 1]);
    $otherDepartment = (new Student)->forceFill(['faculty_code' => 10, 'department_code' => 101, 'level_code' => 1]);
    $otherLevel = (new Student)->forceFill(['faculty_code' => 10, 'department_code' => 100, 'level_code' => 2]);

    $service->assertCanAccessStudent($user, $inside);

    expect(fn () => $service->assertCanAccessStudent($user, $otherDepartment))->toThrow(DomainException::class)
        ->and(fn () => $service->assertCanAccessStudent($user, $otherLevel))->toThrow(DomainException::class);
});

function createResultTables(): void
{
    Schema::create('students', function (Blueprint $table) {
        $table->unsignedBigInteger('university_no')->primary();
        $table->unsignedBigInteger('department_code')->nullable();
        $table->softDeletes();
    });

    foreach (['res_marks', 'res_recoms'] as $name) {
        Schema::create($name, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('university_no');
            $table->unsignedBigInteger('faculty_code');
            $table->unsignedBigInteger('department_code')->nullable();
            $table->unsignedBigInteger('level_code');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    DB::table('students')->insert([
        ['university_no' => 1, 'department_code' => 100],
        ['university_no' => 2, 'department_code' => 101],
    ]);
}

it('limits the marks resource to the user scope, keeping general-subject marks of own students', function () {
    createResultTables();

    DB::table('res_marks')->insert([
        ['id' => 1, 'university_no' => 1, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1],
        ['id' => 2, 'university_no' => 2, 'faculty_code' => 10, 'department_code' => 101, 'level_code' => 1],
        ['id' => 3, 'university_no' => 1, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 2],
        ['id' => 4, 'university_no' => 1, 'faculty_code' => 10, 'department_code' => null, 'level_code' => 1],
        ['id' => 5, 'university_no' => 2, 'faculty_code' => 10, 'department_code' => null, 'level_code' => 1],
        ['id' => 6, 'university_no' => 1, 'faculty_code' => 11, 'department_code' => 100, 'level_code' => 1],
    ]);

    auth()->setUser(academicUser(faculties: [10], departments: [100], levels: [1]));

    expect(MarkResource::getEloquentQuery()->pluck('id')->sort()->values()->all())->toBe([1, 4])
        ->and(MarkResource::getNavigationBadge())->toBe('2');
});

it('limits the recommendations resource to the user scope', function () {
    createResultTables();

    DB::table('res_recoms')->insert([
        ['id' => 1, 'university_no' => 1, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1],
        ['id' => 2, 'university_no' => 2, 'faculty_code' => 10, 'department_code' => 101, 'level_code' => 1],
        ['id' => 3, 'university_no' => 1, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 3],
    ]);

    auth()->setUser(academicUser(faculties: [10], departments: [100], levels: [1]));

    expect(RecomResource::getEloquentQuery()->pluck('id')->all())->toBe([1])
        ->and(RecomResource::getNavigationBadge())->toBe('1');
});

it('shows a faculty-only user every mark in the faculty', function () {
    createResultTables();

    DB::table('res_marks')->insert([
        ['id' => 1, 'university_no' => 1, 'faculty_code' => 10, 'department_code' => 100, 'level_code' => 1],
        ['id' => 2, 'university_no' => 2, 'faculty_code' => 10, 'department_code' => null, 'level_code' => 2],
        ['id' => 3, 'university_no' => 2, 'faculty_code' => 11, 'department_code' => 110, 'level_code' => 1],
    ]);

    auth()->setUser(academicUser(faculties: [10]));

    expect(MarkResource::getEloquentQuery()->pluck('id')->sort()->values()->all())->toBe([1, 2]);
});
