<?php

use App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Pages\CreateRooms;
use App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Pages\EditRooms;
use App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Pages\ListRooms;
use App\Models\attendances\Room;
use Livewire\Livewire;

beforeEach(function () {
    migrateErpSchema();
});

it('lists rooms', function () {
    actingAsSuperAdmin();
    $rooms = collect([
        Room::create(['name' => 'قاعة 1', 'capacity' => 30, 'type' => 'theoretical']),
        Room::create(['name' => 'معمل 1', 'capacity' => 20, 'type' => 'practical']),
    ]);

    Livewire::test(ListRooms::class)->assertCanSeeTableRecords($rooms);
});

it('creates a room and validates required fields and minimum capacity', function () {
    actingAsSuperAdmin();

    Livewire::test(CreateRooms::class)
        ->fillForm(['name' => 'قاعة 5', 'capacity' => 40, 'type' => 'theoretical'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Room::query()->where('name', 'قاعة 5')->value('capacity'))->toBe(40);

    Livewire::test(CreateRooms::class)
        ->fillForm(['name' => null, 'capacity' => 0, 'type' => null])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'capacity' => 'min', 'type' => 'required']);
});

it('edits a room', function () {
    actingAsSuperAdmin();
    $room = Room::create(['name' => 'قاعة 1', 'capacity' => 30, 'type' => 'theoretical']);

    Livewire::test(EditRooms::class, ['record' => $room->getRouteKey()])
        ->fillForm(['capacity' => 55])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($room->fresh()->capacity)->toBe(55);
});
