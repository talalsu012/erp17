<?php

use App\Filament\Clusters\Reg\Resources\Students\Pages\ListStudents;
use App\Models\Reg\Faculty;
use App\Models\Student;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    migrateErpSchema();
    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);
});

it('lists students of the faculties assigned to the user', function () {
    $user = actingAsSuperAdmin();
    $user->faculties()->attach(Faculty::query()->pluck('code'));
    $students = Student::factory()->count(3)->create();

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->assertSuccessful()
        ->assertCanSeeTableRecords($students);
});

it('currently hides every student from a super admin with no faculty assignment (pinned behaviour)', function () {
    actingAsSuperAdmin();
    $students = Student::factory()->count(2)->create();

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->assertSuccessful()
        ->assertCanNotSeeTableRecords($students);
});

it('searches students by name', function () {
    $user = actingAsSuperAdmin();
    $user->faculties()->attach(Faculty::query()->pluck('code'));
    $target = Student::factory()->create(['name_ar' => 'محمد أحمد علي']);
    $other = Student::factory()->create(['name_ar' => 'سارة عمر حسن']);

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->searchTable('محمد أحمد')
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);
});

it('deletes the student account through the resetAccount action', function () {
    $user = actingAsSuperAdmin();
    $user->faculties()->attach(Faculty::query()->pluck('code'));
    $student = Student::factory()->create();
    DB::table('users_std')->insert([
        'university_no' => $student->university_no,
        'email' => 'student@example.test',
        'password' => 'x',
    ]);
    $student->update(['student_account' => Student::CLEARED_ACCOUNT_CODE]);

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->callAction(TestAction::make('resetAccount')->table($student))
        ->assertNotified(__('students.reset_account_done'));

    expect(DB::table('users_std')->where('university_no', $student->university_no)->exists())->toBeFalse()
        ->and($student->fresh()->student_account)->toBe(Student::CLEARED_ACCOUNT_CODE);
});

it('warns when the student has no account to delete', function () {
    $user = actingAsSuperAdmin();
    $user->faculties()->attach(Faculty::query()->pluck('code'));
    $student = Student::factory()->create();

    Livewire::test(ListStudents::class)
        ->loadTable()
        ->callAction(TestAction::make('resetAccount')->table($student))
        ->assertNotified(__('students.account_not_found'));
});
