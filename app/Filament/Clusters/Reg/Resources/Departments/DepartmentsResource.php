<?php

namespace App\Filament\Clusters\Reg\Resources\Departments;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\Departments\Pages\CreateDepartments;
use App\Filament\Clusters\Reg\Resources\Departments\Pages\EditDepartments;
use App\Filament\Clusters\Reg\Resources\Departments\Pages\ListDepartments;
use App\Filament\Clusters\Reg\Resources\Departments\Schemas\DepartmentsForm;
use App\Filament\Clusters\Reg\Resources\Departments\Tables\DepartmentsTable;
use App\Models\Reg\Department;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepartmentsResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 3;

    public static function getPluralLabel(): ?string
    {
        return __('reg.departments');
    }

    public static function getLabel(): ?string
    {
        return __('reg.department');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('reg.departments'));
    }

    public static function getNavigationGroup(): string
    {
        return __('main.registration');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    protected static ?string $cluster = RegCluster::class;

    // protected static ?string $recordTitleAttribute = 'name_ar';

    public static function form(Schema $schema): Schema
    {
        return DepartmentsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepartmentsTable::configure($table);
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
            'index' => ListDepartments::route('/'),
            'create' => CreateDepartments::route('/create'),
            'edit' => EditDepartments::route('/{record}/edit'),
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
