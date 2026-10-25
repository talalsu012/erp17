<?php

use App\Filament\Clusters\Res\Pages\DistortionTreatment;
use App\Models\Reg\Department;
use App\Models\Reg\Faculty;
use App\Models\Res\Subject;
use App\Reg\AbsStatus;
use Database\Seeders\RegLtColumns;
use Database\Seeders\RegSettingsTableSeeder;
use Illuminate\Support\Facades\Artisan;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    migrateErpSchema();

    foreach (['2026_10_25_042931_create_mark_curves_table.php', '2026_10_25_042932_create_mark_curve_items_table.php'] as $migration) {
        Artisan::call('migrate', ['--path' => "database/migrations/{$migration}", '--no-interaction' => true]);
    }

    $this->seed([RegSettingsTableSeeder::class, RegLtColumns::class]);

    $user = actingAsSuperAdmin();
    $this->faculty = Faculty::query()->first();
    $this->department = Department::query()->where('faculty_code', $this->faculty->code)->firstOrFail();
    $user->faculties()->syncWithoutDetaching([$this->faculty->code]);

    Subject::query()->forceCreate([
        'code' => 999001, 'name_ar' => 'مادة تجريبية', 'faculty_code' => $this->faculty->code, 'department_code' => $this->department->code,
        'level_code' => 1001, 'crch' => 3, 'exam' => 100,
    ]);
});

function filterOptions(Testable $component, string $name): array
{
    return $component->instance()->getSchema('filtersForm')->getComponent($name)->getOptions();
}

it('starts with the first exam preselected so the statistics are not blocked by an empty exam type', function () {
    $component = Livewire::test(DistortionTreatment::class);

    expect($component->get('filters.absent'))->toBe(AbsStatus::First->value);
});

it('fills the department and subject filters from the chosen faculty and level', function () {
    $component = Livewire::test(DistortionTreatment::class);

    expect(filterOptions($component, 'department'))->toBe([])
        ->and(filterOptions($component, 'subject'))->toBe([]);

    $component->set('filters.faculty', $this->faculty->code)->set('filters.level', 1001);

    expect(filterOptions($component, 'department'))->toHaveKey($this->department->code)
        ->and(filterOptions($component, 'subject'))->toBe([999001 => 'مادة تجريبية']);

    $component->set('filters.department', $this->department->code);

    expect(filterOptions($component, 'subject'))->toBe([999001 => 'مادة تجريبية']);
});

it('clears the dependent filters when their parent filter changes', function () {
    $component = Livewire::test(DistortionTreatment::class)
        ->set('filters.faculty', $this->faculty->code)
        ->set('filters.level', 1001)
        ->set('filters.department', $this->department->code)
        ->set('filters.subject', 999001);

    $component->set('filters.level', 1002);

    expect($component->get('filters.subject'))->toBeNull()
        ->and($component->get('filters.department'))->toBe($this->department->code);

    $component->set('filters.subject', 999001)->set('filters.department', null);

    expect($component->get('filters.subject'))->toBeNull();

    $component->set('filters.department', $this->department->code)->set('filters.subject', 999001);
    $component->set('filters.faculty', $this->faculty->code + 1);

    expect($component->get('filters.department'))->toBeNull()
        ->and($component->get('filters.subject'))->toBeNull();
});

it('shows the statistics once faculty, level, subject and batch are chosen', function () {
    $component = Livewire::test(DistortionTreatment::class)
        ->set('filters.faculty', $this->faculty->code)
        ->set('filters.level', 1001)
        ->set('filters.batch', 2026)
        ->set('filters.subject', 999001);

    expect($component->instance()->getStatistics())->not->toBeNull();
});

it('declares every filter and curve key so the browser can entangle each field', function () {
    $component = Livewire::test(DistortionTreatment::class);

    foreach (['batch', 'faculty', 'department', 'level', 'absent', 'subject'] as $key) {
        expect(array_key_exists($key, $component->instance()->filters))->toBeTrue("filters.{$key}");
    }

    foreach (['method', 'range', 'delta', 'targetMean', 'targetSd', 'note'] as $key) {
        expect(array_key_exists($key, $component->instance()->curve))->toBeTrue("curve.{$key}");
    }
});
