<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Groupings;

use App\Filament\Clusters\AcademicSupervision\AcademicSupervisionCluster;
use App\Filament\Clusters\AcademicSupervision\Resources\Groupings\Pages\CreateGrouping;
use App\Filament\Clusters\AcademicSupervision\Resources\Groupings\Pages\EditGrouping;
use App\Filament\Clusters\AcademicSupervision\Resources\Groupings\Pages\ListGroupings;
use App\Filament\Clusters\AcademicSupervision\Resources\Groupings\Schemas\GroupingForm;
use App\Filament\Clusters\AcademicSupervision\Resources\Groupings\Tables\GroupingsTable;
use App\Models\attendances\Grouping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GroupingResource extends Resource
{
    protected static ?string $model = Grouping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = AcademicSupervisionCluster::class;

    protected static ?string $table = 'att_grouping';

    protected static ?int $navigationSort = 2;

    // protected static ?string $cluster = attendances::class;
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
        return __('Attendance.grouping');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Attendance.grouping');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('Attendance.grouping'));
    }

    public static function form(Schema $schema): Schema
    {
        return GroupingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGroupings::route('/'),
            'create' => CreateGrouping::route('/create'),
            'edit' => EditGrouping::route('/{record}/edit'),
        ];
    }
}
