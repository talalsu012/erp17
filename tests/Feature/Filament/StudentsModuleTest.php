<?php

use App\Filament\Clusters\Reg\Resources\Students\Pages\CreateStudents;
use App\Filament\Clusters\Reg\Resources\Students\Pages\EditStudents;
use App\Filament\Clusters\Reg\Resources\Students\Pages\ListStudents;
use App\Filament\Clusters\Reg\Resources\Students\Pages\ViewStudents;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Reg\Level;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    migrateErpSchema();
    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);
});

function seedStudentFieldPermissions(): void
{
    foreach (['faculty_code', 'department_code', 'special_code', 'level_code', 'stady_code', 'batch_code', 'academic_year_code', 'status_code', 'admission_code', 'gender_code', 'student_account'] as $field) {
        App\Models\Field::query()->forceCreate(['name' => 'students.'.$field]);
    }
}

function actingAsScopedAdmin(): User
{
    seedStudentFieldPermissions();
    $user = actingAsSuperAdmin();
    $user->faculties()->attach(Faculty::query()->pluck('code'));

    return $user;
}

it('rejects the create form when required fields are missing', function () {
    actingAsScopedAdmin();

    Livewire::test(CreateStudents::class)
        ->fillForm(['name_ar' => null, 'university_no' => null, 'faculty_code' => null, 'admission_code' => null])
        ->call('create')
        ->assertHasFormErrors(['name_ar' => 'required', 'university_no' => 'required', 'faculty_code' => 'required', 'admission_code' => 'required']);

    expect(Student::query()->count())->toBe(0);
});

it('loads the edit form with the stored student data', function () {
    actingAsScopedAdmin();
    $student = Student::factory()->create(['name_ar' => 'طالب للتعديل']);

    Livewire::test(EditStudents::class, ['record' => $student->getRouteKey()])
        ->assertSchemaStateSet(['name_ar' => 'طالب للتعديل', 'university_no' => $student->university_no]);
});

it('renders the student view page', function () {
    actingAsScopedAdmin();
    $student = Student::factory()->create();

    Livewire::test(ViewStudents::class, ['record' => $student->getRouteKey()])->assertSuccessful();
});

it('soft deletes and restores a student from the edit page', function () {
    actingAsScopedAdmin();
    $student = Student::factory()->create();

    Livewire::test(EditStudents::class, ['record' => $student->getRouteKey()])
        ->callAction('delete');
    expect(Student::query()->whereKey($student->getKey())->exists())->toBeFalse();

    Livewire::test(EditStudents::class, ['record' => $student->getRouteKey()])
        ->callAction('restore');
    expect(Student::query()->whereKey($student->getKey())->exists())->toBeTrue();
});

it('bulk updates the selected fields and leaves other fields untouched', function () {
    actingAsScopedAdmin();
    $students = Student::factory()->count(2)->create(['level_code' => Level::query()->first()->code]);
    $targetLevel = Level::query()->where('code', '!=', $students[0]->level_code)->value('code');
    $originalBatches = $students->pluck('batch_code', 'university_no');

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->selectTableRecords($students)
        ->callAction(TestAction::make('updateStudents')->table()->bulk(), data: [
            'academic_fields' => ['level_code'],
            'level_code' => $targetLevel,
        ])
        ->assertNotified(__('reg.updated_successfully'));

    foreach ($students as $student) {
        $fresh = $student->fresh();
        expect($fresh->level_code)->toBe($targetLevel)
            ->and($fresh->batch_code)->toBe($originalBatches[$student->university_no]);
    }
});

it('bulk update warns when no field is selected', function () {
    actingAsScopedAdmin();
    $students = Student::factory()->count(2)->create();

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->selectTableRecords($students)
        ->callAction(TestAction::make('updateStudents')->table()->bulk(), data: ['academic_fields' => []])
        ->assertNotified(__('students.bulk_select_one_field'));
});

it('bulk update sets the faculty from the chosen department', function () {
    actingAsScopedAdmin();
    $department = Department::query()->whereNotNull('faculty_code')->first();
    $students = Student::factory()->count(2)->create();

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->selectTableRecords($students)
        ->callAction(TestAction::make('updateStudents')->table()->bulk(), data: [
            'academic_fields' => ['department_code', 'faculty_code'],
            'faculty_code' => $department->faculty_code,
            'department_code' => $department->code,
        ]);

    foreach ($students as $student) {
        expect($student->fresh()->department_code)->toBe($department->code)
            ->and($student->fresh()->faculty_code)->toBe($department->faculty_code);
    }
});

it('bulk update clears department and special when only the faculty changes', function () {
    actingAsScopedAdmin();
    $targetFaculty = Faculty::query()->first()->code;
    $students = Student::factory()->count(2)->create();

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->selectTableRecords($students)
        ->callAction(TestAction::make('updateStudents')->table()->bulk(), data: [
            'academic_fields' => ['faculty_code'],
            'faculty_code' => $targetFaculty,
        ]);

    foreach ($students as $student) {
        expect($student->fresh()->faculty_code)->toBe($targetFaculty)
            ->and($student->fresh()->department_code)->toBeNull()
            ->and($student->fresh()->special_code)->toBeNull();
    }
});

it('maps every student policy ability to its permission', function (string $ability, string $permission) {
    $user = User::factory()->create();
    $student = Student::factory()->make();
    Permission::findOrCreate($permission, 'web');

    $subject = in_array($ability, ['viewAny', 'create', 'forceDeleteAny', 'restoreAny', 'reorder'], true) ? Student::class : $student;

    expect(Gate::forUser($user)->allows($ability, $subject))->toBeFalse();

    $user->givePermissionTo($permission);

    expect(Gate::forUser($user->fresh())->allows($ability, $subject))->toBeTrue();
})->with([
    ['viewAny', 'ViewAny:Student'],
    ['view', 'View:Student'],
    ['create', 'Create:Student'],
    ['update', 'Update:Student'],
    ['delete', 'Delete:Student'],
    ['restore', 'Restore:Student'],
    ['forceDelete', 'ForceDelete:Student'],
    ['forceDeleteAny', 'ForceDeleteAny:Student'],
    ['restoreAny', 'RestoreAny:Student'],
    ['replicate', 'Replicate:Student'],
    ['reorder', 'Reorder:Student'],
]);

it('shows the reset account and bulk update actions only to users holding their dedicated permissions', function () {
    $user = actingAsScopedAdmin();
    $user->removeRole(config('filament-shield.super_admin.name'));
    foreach (['ViewAny:Student', 'View:Student'] as $permission) {
        Permission::findOrCreate($permission, 'web');
        $user->givePermissionTo($permission);
    }
    $student = Student::factory()->create();

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->assertActionHidden(TestAction::make('resetAccount')->table($student))
        ->assertActionHidden(TestAction::make('updateStudents')->table()->bulk());

    foreach (['ResetAccount:Student', 'BulkUpdate:Student'] as $permission) {
        Permission::findOrCreate($permission, 'web');
        $user->givePermissionTo($permission);
    }

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->assertActionVisible(TestAction::make('resetAccount')->table($student))
        ->assertActionVisible(TestAction::make('updateStudents')->table()->bulk());
});

it('grants the new student permissions to every role and user that could list students', function () {
    $viewAny = Permission::findOrCreate('ViewAny:Student', 'web');
    $lister = Spatie\Permission\Models\Role::findOrCreate('lister', 'web');
    $other = Spatie\Permission\Models\Role::findOrCreate('other', 'web');
    $lister->givePermissionTo($viewAny);
    $directUser = User::factory()->create();
    $directUser->givePermissionTo($viewAny);

    Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => 'database/migrations/2026_10_21_170000_grant_student_custom_permissions.php', '--no-interaction' => true]);
    app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    expect($lister->fresh()->hasPermissionTo('ResetAccount:Student'))->toBeTrue()
        ->and($lister->fresh()->hasPermissionTo('BulkUpdate:Student'))->toBeTrue()
        ->and($other->fresh()->hasPermissionTo('ResetAccount:Student'))->toBeFalse()
        ->and($directUser->fresh()->hasPermissionTo('BulkUpdate:Student'))->toBeTrue();
});

it('currently writes NULL for a bulk-updated field the user has no field permission for (pinned behaviour)', function () {
    $user = actingAsScopedAdmin();
    $user->removeRole(config('filament-shield.super_admin.name'));
    Spatie\Permission\Models\Role::findOrCreate('registrar', 'web');
    $user->assignRole('registrar');
    foreach (['ViewAny:Student', 'View:Student', 'Update:Student', 'BulkUpdate:Student'] as $permission) {
        Permission::findOrCreate($permission, 'web');
        $user->givePermissionTo($permission);
    }
    $user->faculties()->attach(Faculty::query()->pluck('code'));
    $students = Student::factory()->count(2)->create();
    $targetLevel = Level::query()->first()->code;

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->selectTableRecords($students)
        ->callAction(TestAction::make('updateStudents')->table()->bulk(), data: [
            'academic_fields' => ['level_code'],
            'level_code' => $targetLevel,
        ]);

    foreach ($students as $student) {
        expect($student->fresh()->level_code)->toBeNull();
    }
});

it('filters students by faculty through the faculty and department filter', function () {
    actingAsScopedAdmin();
    $faculties = Faculty::query()->limit(2)->pluck('code');
    $inFaculty = Student::factory()->create(['faculty_code' => $faculties[0]]);
    $outFaculty = Student::factory()->create(['faculty_code' => $faculties[1]]);

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->filterTable('faculty_and_department', ['faculty_code' => [$faculties[0]]])
        ->assertCanSeeTableRecords([$inFaculty])
        ->assertCanNotSeeTableRecords([$outFaculty]);
});

it('loads the student table with a query count that stays bounded as the number of rows grows', function () {
    actingAsScopedAdmin();

    $countQueries = function (): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::test(ListStudents::class)->loadTable();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $queries;
    };

    Student::factory()->count(3)->create();
    $few = $countQueries();

    Student::factory()->count(12)->create();
    $many = $countQueries();

    expect($many - $few)->toBeLessThan(5);
});

it('keeps the student translation files in sync between Arabic and English', function () {
    $arabic = require base_path('lang/ar/students.php');
    $english = require base_path('lang/en/students.php');

    expect(array_keys($english))->toBe(array_keys($arabic));
});

it('fills the English name from the Arabic name only when it is empty', function () {
    $this->mock(App\Support\EnglishNameTranslator::class)
        ->shouldReceive('translate')->with('محمد أحمد')->once()->andReturn('Mohamed Ahmed');

    $fill = app(App\Actions\Students\FillEnglishName::class);

    expect($fill->execute(['name_ar' => 'محمد أحمد', 'name_en' => null])['name_en'])->toBe('Mohamed Ahmed')
        ->and($fill->execute(['name_ar' => 'محمد أحمد', 'name_en' => 'Custom Name'])['name_en'])->toBe('Custom Name')
        ->and($fill->execute(['name_ar' => null, 'name_en' => null])['name_en'])->toBeNull();
});

it('translates an empty English name in the create and edit page hooks', function () {
    $this->mock(App\Support\EnglishNameTranslator::class)
        ->shouldReceive('translate')->with('طالب للترجمة')->twice()->andReturn('Student For Translation');

    foreach ([CreateStudents::class => 'mutateFormDataBeforeCreate', EditStudents::class => 'mutateFormDataBeforeSave'] as $page => $hook) {
        $result = (new ReflectionMethod($page, $hook))->invoke(new $page, ['name_ar' => 'طالب للترجمة', 'name_en' => '']);

        expect($result['name_en'])->toBe('Student For Translation');
    }
});

it('falls back to transliteration when the translation service fails', function () {
    $translator = new App\Support\EnglishNameTranslator;

    expect($translator->translate('   '))->toBe('');
});
