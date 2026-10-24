<?php

use App\Filament\Clusters\Reg\Resources\Levels\Pages\CreateLevel;
use App\Filament\Clusters\Reg\Resources\Levels\Pages\EditLevel;
use App\Filament\Clusters\Reg\Resources\Levels\Pages\ListLevels;
use App\Models\Reg\Level;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    migrateErpSchema();
});

it('lists levels for a super admin', function () {
    actingAsSuperAdmin();
    $levels = collect([
        Level::create(['code' => 1, 'name_ar' => 'المستوى الأول', 'name_en' => 'Level One']),
        Level::create(['code' => 2, 'name_ar' => 'المستوى الثاني', 'name_en' => 'Level Two']),
    ]);

    Livewire::test(ListLevels::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($levels);
});

it('creates a level with valid data', function () {
    actingAsSuperAdmin();

    Livewire::test(CreateLevel::class)
        ->fillForm(['code' => 5, 'name_ar' => 'مستوى جديد', 'name_en' => 'New Level'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Level::query()->where('code', 5)->value('name_en'))->toBe('New Level');
});

it('rejects a level with missing or duplicate data', function () {
    actingAsSuperAdmin();
    Level::create(['code' => 1, 'name_ar' => 'المستوى الأول', 'name_en' => 'Level One']);

    Livewire::test(CreateLevel::class)
        ->fillForm(['code' => null, 'name_en' => null])
        ->call('create')
        ->assertHasFormErrors(['code' => 'required', 'name_ar' => 'required', 'name_en' => 'required']);

    Livewire::test(CreateLevel::class)
        ->fillForm(['code' => 9, 'name_ar' => 'المستوى الأول', 'name_en' => 'Level One'])
        ->call('create')
        ->assertHasFormErrors(['name_ar' => 'unique', 'name_en' => 'unique']);
});

it('edits a level', function () {
    actingAsSuperAdmin();
    $level = Level::create(['code' => 1, 'name_ar' => 'المستوى الأول', 'name_en' => 'Level One']);

    Livewire::test(EditLevel::class, ['record' => $level->getRouteKey()])
        ->fillForm(['name_en' => 'First Level'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($level->fresh()->name_en)->toBe('First Level');
});

it('forbids users without permissions from the levels pages', function () {
    config(['app.env' => 'local']);
    $this->actingAs(User::factory()->create());

    $this->get('/admin/reg/levels')->assertForbidden();
    $this->get('/admin/reg/levels/create')->assertForbidden();
});
