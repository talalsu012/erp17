<?php

use App\Filament\Clusters\Invoices\Pages\BulkStudentFees;
use App\Models\Student;
use App\Models\User;
use App\Services\Reg\BulkStudentFeeService;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    migrateErpSchema();

    foreach ([10, 11] as $faculty) {
        DB::table('reg_faculties')->insert(['code' => $faculty, 'name_ar' => "كلية {$faculty}"]);
    }

    $this->filters = ['faculty_code' => 10, 'department_code' => 100, 'level_code' => 1, 'batch_code' => 2025];

    foreach ([1, 2] as $number) {
        Student::query()->forceCreate([
            'university_no' => $number, 'name_ar' => "طالب {$number}", 'faculty_code' => 10, 'department_code' => 100,
            'level_code' => 1, 'batch_code' => 2025, 'fees' => 1000, 'reg_fees' => 100, 'installment_code' => 20080002,
        ]);
    }

    $this->outsider = Student::query()->forceCreate([
        'university_no' => 3, 'name_ar' => 'خارج الفلاتر', 'faculty_code' => 10, 'department_code' => 100,
        'level_code' => 2, 'batch_code' => 2025, 'fees' => 1000, 'reg_fees' => 100, 'installment_code' => 20080002,
    ]);

    $this->changes = ['fee_type' => 'fees', 'amount' => 4000, 'installment_code' => 20080004];
});

function feeUser(bool $permitted = true, array $faculties = [10]): User
{
    $user = User::factory()->create();
    $user->faculties()->attach($faculties);

    if ($permitted) {
        $user->givePermissionTo(Permission::findOrCreate(BulkStudentFeeService::PERMISSION, 'web'));
    }

    return $user->fresh();
}

it('previews the matched students count and the recalculated installments without saving', function () {
    $preview = app(BulkStudentFeeService::class)->preview(feeUser(), $this->filters, $this->changes);

    expect($preview['count'])->toBe(2)
        ->and($preview['samples'])->toHaveCount(2)
        ->and($preview['samples'][0]['after'])->toMatchArray(['all_fees' => 4100.0, 'pay1' => 1100.0, 'pay2' => 1000.0, 'pay3' => 1000.0, 'pay4' => 1000.0])
        ->and(Student::query()->find(1)->fees)->toEqual(1000);
});

it('applies the fee to every matching student through the fee service and leaves the others untouched', function () {
    $changed = app(BulkStudentFeeService::class)->apply(feeUser(), $this->filters, $this->changes);

    expect($changed)->toBe(2)
        ->and(Student::query()->find(1))->fees->toEqual(4000)->all_fees->toEqual(4100)->xmod->toEqual(4100)->pay1->toEqual(1100)->pay4->toEqual(1000)
        ->and(Student::query()->find(2)->all_fees)->toEqual(4100)
        ->and($this->outsider->fresh())->fees->toEqual(1000)->all_fees->toEqual(0);
});

it('logs who ran the bulk update and what changed', function () {
    $user = feeUser();

    app(BulkStudentFeeService::class)->apply($user, $this->filters, $this->changes);

    $log = Activity::query()->where('log_name', 'bulk_fees')->sole();

    expect($log->causer_id)->toBe($user->id)
        ->and($log->properties['matched'])->toBe(2)
        ->and($log->properties['students']['1']['before']['fees'])->toEqual(1000)
        ->and($log->properties['students']['1']['after']['fees'])->toEqual(4000);
});

it('refuses users without the explicit permission', function () {
    $user = feeUser(permitted: false);

    expect(fn () => app(BulkStudentFeeService::class)->apply($user, $this->filters, $this->changes))->toThrow(DomainException::class)
        ->and(fn () => app(BulkStudentFeeService::class)->preview($user, $this->filters, $this->changes))->toThrow(DomainException::class)
        ->and(Student::query()->find(1)->fees)->toEqual(1000);
});

it('restricts the students to the academic scope of the user', function () {
    $user = feeUser(faculties: [11]);

    expect(app(BulkStudentFeeService::class)->preview($user, $this->filters, $this->changes)['count'])->toBe(0)
        ->and(app(BulkStudentFeeService::class)->apply($user, $this->filters, $this->changes))->toBe(0)
        ->and(Student::query()->find(1)->fees)->toEqual(1000);
});

it('rejects an unknown fee type, a negative amount and missing filters', function () {
    $service = app(BulkStudentFeeService::class);
    $user = feeUser();

    expect(fn () => $service->apply($user, $this->filters, ['fee_type' => 'all_fees', 'amount' => 1]))->toThrow(DomainException::class)
        ->and(fn () => $service->apply($user, $this->filters, ['fee_type' => 'fees', 'amount' => -5]))->toThrow(DomainException::class)
        ->and(fn () => $service->apply($user, ['faculty_code' => 10], $this->changes))->toThrow(DomainException::class);
});

it('rolls back every student when one update fails', function () {
    Student::updating(function (Student $student) {
        if ($student->university_no === 2) {
            throw new RuntimeException('fail');
        }
    });

    expect(fn () => app(BulkStudentFeeService::class)->apply(feeUser(), $this->filters, $this->changes))->toThrow(RuntimeException::class)
        ->and(Student::query()->find(1)->fees)->toEqual(1000)
        ->and(Activity::query()->where('log_name', 'bulk_fees')->count())->toBe(0);
});

it('previews and applies from the page for a permitted user', function () {
    actingAsSuperAdmin();

    DB::table('reg_departments')->insert(['code' => 100, 'faculty_code' => 10, 'name_ar' => 'قسم']);
    DB::table('reg_levels')->insert(['code' => 1, 'name_ar' => 'فصل']);
    DB::table('reg_ltcolumns')->insert([
        ['code' => 2025, 'name_ar' => 'عام', 'name_en' => 'year', 'grouped' => 2003],
        ['code' => 20080004, 'name_ar' => 'أربعة أقساط', 'name_en' => 'four', 'grouped' => 2005],
    ]);

    Livewire::test(BulkStudentFees::class)
        ->fillForm($this->filters + $this->changes)
        ->call('previewFees')
        ->assertSet('preview.count', 2)
        ->call('applyFees');

    expect(Student::query()->find(1)->all_fees)->toEqual(4100);
});

it('forbids the page to users without the permission', function () {
    $this->actingAs(feeUser(permitted: false));
    Filament\Facades\Filament::setCurrentPanel('admin');

    expect(BulkStudentFees::canAccess())->toBeFalse();
});
