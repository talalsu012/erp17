<?php

use App\Filament\Clusters\Reg\Resources\Students\Pages\ListStudents;
use App\Models\Student;
use App\Reg\RegAccess;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    migrateErpSchema();
    DB::table('reg_faculties')->insert(['code' => 10, 'name_ar' => 'كلية تجريبية']);

    $this->student = Student::query()->forceCreate([
        'university_no' => 1, 'name_ar' => 'طالب', 'faculty_code' => 10, 'fees' => 4000, 'installment_code' => 4,
    ]);
});

it('blocks, opens and resets registration from the row actions', function () {
    actingAsSuperAdmin();

    $table = Livewire::test(ListStudents::class);

    $table->callTableAction('blockRegistration', $this->student)->assertHasNoTableActionErrors();
    expect($this->student->fresh()->reg_access)->toBe(RegAccess::Closed);

    $table->callTableAction('openRegistration', $this->student);
    expect($this->student->fresh())->reg_access->toBe(RegAccess::Open)->reg_access_at->not->toBeNull();

    $table->callTableAction('resetRegistration', $this->student);
    expect($this->student->fresh()->reg_access)->toBeNull();
});

it('blocks registration for the selected students in bulk', function () {
    actingAsSuperAdmin();

    Livewire::test(ListStudents::class)
        ->callTableBulkAction('blockRegistrationBulk', [$this->student])
        ->assertHasNoTableBulkActionErrors();

    expect($this->student->fresh()->reg_access)->toBe(RegAccess::Closed);
});
