<?php

namespace App\Filament\Clusters\Reg\Resources\Specials;

use App\Filament\Clusters\Reg\RegCluster;
use App\Filament\Clusters\Reg\Resources\Specials\Pages\CreateSpecials;
use App\Filament\Clusters\Reg\Resources\Specials\Pages\EditSpecials;
use App\Filament\Clusters\Reg\Resources\Specials\Pages\ListSpecials;
use App\Filament\Clusters\Reg\Resources\Specials\Schemas\SpecialsForm;
use App\Filament\Clusters\Reg\Resources\Specials\Tables\SpecialsTable;
use App\Models\Reg\Special;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpecialsResource extends Resource
{
    protected static ?string $model = Special::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = RegCluster::class;

    // protected static ?string $recordTitleAttribute = 'Special';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->where('is_visible', '>=', '0');
    }

    public static function getNavigationGroup(): string
    {
        return __('main.registration');
    }

    public static function getLabel(): ?string
    {
        return __('reg.special');
    }

    public static function getPluralLabel(): ?string
    {
        return __('reg.specials');
    }

    public static function getNavigationLabel(): string
    {
        return ucfirst(__('reg.specials'));
    }

    public static function form(Schema $schema): Schema
    {
        return SpecialsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpecialsTable::configure($table);
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
            'index' => ListSpecials::route('/'),
            'create' => CreateSpecials::route('/create'),
            'edit' => EditSpecials::route('/{record}/edit'),
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
