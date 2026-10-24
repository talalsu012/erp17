<?php

namespace App\Filament\Clusters\Reg\Resources\Faculties;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\Faculties\Pages\CreateFaculties;
use App\Filament\Clusters\Reg\Resources\Faculties\Pages\EditFaculties;
use App\Filament\Clusters\Reg\Resources\Faculties\Pages\ListFaculties;
use App\Filament\Clusters\Reg\Resources\Faculties\Schemas\FacultiesForm;
use App\Filament\Clusters\Reg\Resources\Faculties\Tables\FacultiesTable;
use App\Models\Reg\Faculty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FacultiesResource extends Resource
{
    protected static ?string $model = Faculty::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 2;

    public static function getPluralLabel(): ?string
    {
        return __('reg.faculties');
    }

    public static function getLabel(): ?string
    {
        return __('reg.faculty');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('reg.faculties'));
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

    // protected static ?string $recordTitleAttribute = 'Reg/Faculty';

    public static function form(Schema $schema): Schema
    {
        return FacultiesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FacultiesTable::configure($table);
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
            'index' => ListFaculties::route('/'),
            'create' => CreateFaculties::route('/create'),
            'edit' => EditFaculties::route('/{record}/edit'),
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
