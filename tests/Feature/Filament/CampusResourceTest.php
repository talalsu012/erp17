<?php

use App\Filament\Clusters\Reg\Resources\Campuses\Pages\CreateCampus;
use App\Filament\Clusters\Reg\Resources\Campuses\Pages\EditCampus;
use App\Filament\Clusters\Reg\Resources\Campuses\Pages\ListCampuses;
use App\Models\Reg\Campus;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

beforeEach(function () {
    migrateErpSchema();
});

it('lists campuses', function () {
    actingAsSuperAdmin();
    $campuses = collect([
        Campus::create(['code' => 1, 'name_ar' => 'الحرم الأول', 'name_en' => 'Main']),
        Campus::create(['code' => 2, 'name_ar' => 'الحرم الثاني', 'name_en' => 'North']),
    ]);

    Livewire::test(ListCampuses::class)->assertCanSeeTableRecords($campuses);
});

it('creates a campus with valid data and rejects missing fields', function () {
    actingAsSuperAdmin();

    Livewire::test(CreateCampus::class)
        ->fillForm(['code' => 9, 'name_ar' => 'حرم', 'name_en' => 'Campus'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Campus::query()->where('code', 9)->exists())->toBeTrue();

    Livewire::test(CreateCampus::class)
        ->fillForm(['code' => null, 'name_ar' => null, 'name_en' => null])
        ->call('create')
        ->assertHasFormErrors(['code' => 'required', 'name_en' => 'required']);
});

it('edits and deletes a campus', function () {
    actingAsSuperAdmin();
    $campus = Campus::create(['code' => 1, 'name_ar' => 'الحرم الأول', 'name_en' => 'Main']);

    Livewire::test(EditCampus::class, ['record' => $campus->getRouteKey()])
        ->fillForm(['name_en' => 'Main Campus'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($campus->fresh()->name_en)->toBe('Main Campus');

    Livewire::test(EditCampus::class, ['record' => $campus->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(Campus::query()->count())->toBe(0)
        ->and(Campus::withTrashed()->count())->toBe(1);
});
