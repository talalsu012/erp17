<?php

namespace App\Filament\Clusters\AcademicSupervision\Resources\Lectures;

use App\Filament\Clusters\AcademicSupervision\AcademicSupervisionCluster;
use App\Filament\Clusters\AcademicSupervision\Resources\Lectures\Pages\CreateLecture;
use App\Filament\Clusters\AcademicSupervision\Resources\Lectures\Pages\EditLecture;
use App\Filament\Clusters\AcademicSupervision\Resources\Lectures\Pages\ListLectures;
use App\Filament\Clusters\AcademicSupervision\Resources\Lectures\Schemas\LectureForm;
use App\Filament\Clusters\AcademicSupervision\Resources\Lectures\Tables\LecturesTable;
use App\Models\attendances\Lecture;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LectureResource extends Resource
{
    protected static ?string $model = Lecture::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = AcademicSupervisionCluster::class;

    // protected static ?string $recordTitleAttribute = 'Lecture';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Attendance.Lecture';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->orderBy('created_at', 'DESC');
    }

    public static function getNavigationGroup(): string
    {
        return __('Attendance.Attendance');
    }

    public static function getLabel(): ?string
    {
        return __(static::$title);
    }

    public static function getPluralLabel(): ?string
    {
        return __(static::$title);
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__(static::$title));
    }

    public static function form(Schema $schema): Schema
    {
        return LectureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LecturesTable::configure($table);
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
            'index' => ListLectures::route('/'),
            'create' => CreateLecture::route('/create'),
            'edit' => EditLecture::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
