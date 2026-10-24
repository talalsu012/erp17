<?php

namespace App\Filament\Clusters\Reg\Resources\Campuses;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\Campuses\Pages\CreateCampus;
use App\Filament\Clusters\Reg\Resources\Campuses\Pages\EditCampus;
use App\Filament\Clusters\Reg\Resources\Campuses\Pages\ListCampuses;
use App\Filament\Clusters\Reg\Resources\Campuses\Schemas\CampusForm;
use App\Filament\Clusters\Reg\Resources\Campuses\Tables\CampusesTable;
use App\Models\Reg\Campus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CampusResource extends Resource
{
    protected static ?string $model = Campus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 1;

    public static function getPluralLabel(): ?string
    {

        return __('reg.campuses');
    }

    public static function getLabel(): ?string
    {
        return __('reg.campus');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('reg.campuses'));
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

    public static function form(Schema $schema): Schema
    {
        return CampusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CampusesTable::configure($table);
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
            'index' => ListCampuses::route('/'),
            'create' => CreateCampus::route('/create'),
            'edit' => EditCampus::route('/{record}/edit'),
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
