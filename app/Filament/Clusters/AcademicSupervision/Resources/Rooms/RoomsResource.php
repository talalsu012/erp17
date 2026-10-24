<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Rooms;

use App\Filament\Clusters\AcademicSupervision\AcademicSupervisionCluster;
use App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Pages\CreateRooms;
use App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Pages\EditRooms;
use App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Pages\ListRooms;
use App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Schemas\RoomsForm;
use App\Filament\Clusters\AcademicSupervision\Resources\Rooms\Tables\RoomsTable;
use App\Models\attendances\Room;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RoomsResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = AcademicSupervisionCluster::class;

    // protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string
    {
        return __('Attendance.Attendance');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getLabel(): ?string
    {
        return __('Attendance.room');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Attendance.room');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('Attendance.room'));
    }

    public static function form(Schema $schema): Schema
    {
        return RoomsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRooms::route('/'),
            'create' => CreateRooms::route('/create'),
            'edit' => EditRooms::route('/{record}/edit'),
        ];
    }
}
