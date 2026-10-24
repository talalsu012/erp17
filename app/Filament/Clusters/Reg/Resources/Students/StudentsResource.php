<?php

namespace App\Filament\Clusters\Reg\Resources\Students;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\Students\Pages\CreateStudents;
use App\Filament\Clusters\Reg\Resources\Students\Pages\EditStudents;
use App\Filament\Clusters\Reg\Resources\Students\Pages\ListStudents;
use App\Filament\Clusters\Reg\Resources\Students\Pages\ListStudentsActivities;
use App\Filament\Clusters\Reg\Resources\Students\Pages\ViewStudents;
use App\Filament\Clusters\Reg\Resources\Students\RelationManagers\PaymentsRelationManager;
use App\Filament\Clusters\Reg\Resources\Students\Schemas\StudentsForm;
use App\Filament\Clusters\Reg\Resources\Students\Tables\StudentsTable;
use App\Models\Student;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentsResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // protected static ?string $cluster = RegCluster::class;

    protected static ?string $recordTitleAttribute = 'university_no';

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = auth()->user()
            ->constrainToAcademicScope(static::getModel()::query())
            ->count();

        return (string) $count;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 1000 ? 'warning' : 'primary';
    }

    public static function getLabel(): ?string
    {
        return __('reg.student');
    }

    public static function getPluralLabel(): ?string
    {
        return __('reg.students');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('reg.students'));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return StudentsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudents::route('/create'),
            'edit' => EditStudents::route('/{record}/edit'),
            'view' => ViewStudents::route('/{record}/view'),
            'activities' => ListStudentsActivities::route('/{record}/activities'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return auth()->user()->constrainToAcademicScope(parent::getEloquentQuery());
    }
}
