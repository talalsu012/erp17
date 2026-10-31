<?php

use App\Models\Student;
use App\Models\User;
use App\Services\Reg\StudentPromotionService;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    migrateErpSchema();

    DB::table('reg_faculties')->insert(['code' => 10, 'name_ar' => 'كلية']);

    $this->criteria = ['faculty_code' => 10, 'current_level' => 1, 'current_batch_code' => 2025, 'recom_code' => [1005]];

    foreach ([1 => 1005, 2 => 2001, 3 => 1005] as $number => $recom) {
        Student::query()->forceCreate([
            'university_no' => $number, 'name_ar' => "طالب {$number}", 'faculty_code' => $number === 3 ? 11 : 10, 'department_code' => 100,
            'level_code' => 1, 'batch_code' => 2025, 'fees' => 1000, 'reg_fees' => 100, 'installment_code' => 20080001,
        ]);
        DB::table('res_recoms')->insert([
            'university_no' => $number, 'faculty_code' => 10, 'level_code' => 1, 'batch_code' => 2025, 'semester' => '2', 'recom_code' => $recom,
        ]);
    }
});

function promoter(bool $permitted = true): User
{
    $user = User::factory()->create();
    $user->faculties()->attach([10]);

    if ($permitted) {
        $user->givePermissionTo(Permission::findOrCreate(StudentPromotionService::PERMISSION, 'web'));
    }

    return $user->fresh();
}

it('promotes only the students in scope who hold the chosen recommendation and carries the balance', function () {
    DB::table('students')->where('university_no', 1)->update(['xmod' => 500]);

    $count = app(StudentPromotionService::class)->promote(promoter(), $this->criteria, 2, 2026);

    expect($count)->toBe(1)
        ->and(Student::query()->find(1))->level_code->toBe(2)->batch_code->toBe(2026)->fees8->toEqual(500)->all_fees->toEqual(1600)
        ->and(Student::query()->find(2))->level_code->toBe(1)
        ->and(Student::query()->find(3))->level_code->toBe(1);

    $log = Activity::query()->where('log_name', 'promotion')->sole();

    expect($log->properties['university_nos'])->toBe([1]);
});

it('refuses users without the permission', function () {
    expect(fn () => app(StudentPromotionService::class)->promote(promoter(false), $this->criteria, 2, 2026))->toThrow(DomainException::class)
        ->and(Student::query()->find(1)->level_code)->toBe(1);
});

it('rejects a missing new level or an unchanged position', function () {
    $service = app(StudentPromotionService::class);

    expect(fn () => $service->promote(promoter(), $this->criteria, null, 2026))->toThrow(DomainException::class)
        ->and(fn () => $service->promote(promoter(), $this->criteria, 1, 2025))->toThrow(DomainException::class)
        ->and(Student::query()->find(1)->level_code)->toBe(1);
});
