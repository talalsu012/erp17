<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Staff;

use App\Filament\Clusters\AcademicSupervision\AcademicSupervisionCluster;
use App\Filament\Clusters\AcademicSupervision\Resources\Staff\Pages\CreateStaff;
use App\Filament\Clusters\AcademicSupervision\Resources\Staff\Pages\EditStaff;
use App\Filament\Clusters\AcademicSupervision\Resources\Staff\Pages\ListStaff;
use App\Filament\Clusters\AcademicSupervision\Resources\Staff\RelationManagers\SubjectRelationManager;
use App\Filament\Clusters\AcademicSupervision\Resources\Staff\Schemas\StaffForm;
use App\Filament\Clusters\AcademicSupervision\Resources\Staff\Tables\StaffTable;
use App\Models\attendances\Staff;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = AcademicSupervisionCluster::class;

    // protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 4;

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
        return __('Attendance.staff');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Attendance.staff');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('Attendance.staff'));
    }

    public static function form(Schema $schema): Schema
    {
        return StaffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SubjectRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaff::route('/'),
            'create' => CreateStaff::route('/create'),
            'edit' => EditStaff::route('/{record}/edit'),
        ];
    }
}
